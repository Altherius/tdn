<?php

namespace App\Controller;

use App\Entity\FootballMatch;
use App\Entity\Tournament;
use App\Elo\EloCalculator;
use App\Form\FootballMatchEditType;
use App\Form\FootballMatchType;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FootballMatchController extends AbstractController
{
    #[Route('/match/create', name: 'match_create')]
    #[IsGranted('ROLE_USER')]
    public function create(Request $request, EntityManagerInterface $manager): Response
    {
        $eloCalculator = new EloCalculator();



        $match = new FootballMatch();
        if ($id = $request->query->has('tournament')) {
            $tournament = $manager->getRepository(Tournament::class)->find($id);
            $match->setTournament($tournament);
        }

        $form = $this->createForm(FootballMatchType::class, $match);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            if ($match->getReceivingTeamScore() > $match->getHostingTeamScore()) {
                $match->setWinner($match->getReceivingTeam());
            } else if ($match->getReceivingTeamScore() < $match->getHostingTeamScore()) {
                $match->setWinner($match->getHostingTeam());
            }

            $result = EloCalculator::DRAW;
            if ($match->getHostingTeamScore() > $match->getReceivingTeamScore()) {
                $result = EloCalculator::WIN;
            } else if ($match->getHostingTeamScore() < $match->getReceivingTeamScore()) {
                $result = EloCalculator::LOSE;
            }

            $eloEvolution = $eloCalculator->getEloEvolution(
                $match->getHostingTeam()?->getRating(),
                $match->getReceivingTeam()?->getRating(),
                $result
            );

            $match->getHostingTeam()?->addRating($eloEvolution);
            $match->getReceivingTeam()?->addRating(-$eloEvolution);

            $manager->persist($match);
            $manager->flush();

            $this->addFlash('success', "Le match " . $match->getHostingTeam() . " - " . $match->getReceivingTeam() .  " a bien été créé. Les classements ELO des équipes
            ont été ajustés.");
        }

        return $this->render('football_match/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/match/edit/{id}', name: 'match_edit')]
    #[IsGranted('ROLE_USER')]
    public function edit(FootballMatch $match, Request $request, EntityManagerInterface $manager): Response
    {
        $eloCalculator = new EloCalculator();
        if ($id = $request->query->has('tournament')) {
            $tournament = $manager->getRepository(Tournament::class)->find($id);
            $match->setTournament($tournament);
        }

        $form = $this->createForm(FootballMatchEditType::class, $match);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            if ($match->getReceivingTeamScore() > $match->getHostingTeamScore()) {
                $match->setWinner($match->getReceivingTeam());
            } else if ($match->getReceivingTeamScore() < $match->getHostingTeamScore()) {
                $match->setWinner($match->getHostingTeam());
            }
            $manager->persist($match);
            $manager->flush();

            $this->addFlash('success', "Le match " . $match->getHostingTeam() . " - " . $match->getReceivingTeam() .  " a bien été édité");
        }

        return $this->render('football_match/edit.html.twig', [
            'form' => $form->createView(),
            'match' => $match
        ]);
    }
}
