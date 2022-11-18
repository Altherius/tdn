<?php

namespace App\Controller;

use App\Entity\FootballMatch;
use App\Entity\Tournament;
use App\Elo\EloCalculator;
use App\Form\FootballMatchEditType;
use App\Form\FootballMatchType;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Loggable\Entity\LogEntry;
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
        if ($id = $request->query->get('tournament')) {
            $tournament = $manager->getRepository(Tournament::class)->find($id);
            $match->setTournament($tournament);
        }

        $form = $this->createForm(FootballMatchType::class, $match);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            if ($match->getReceivingTeamScore() > $match->getHostingTeamScore()) {
                $match->setWinner($match->getReceivingTeam());
                $match->setLoser($match->getHostingTeam());
            } else if ($match->getReceivingTeamScore() < $match->getHostingTeamScore()) {
                $match->setWinner($match->getHostingTeam());
                $match->setLoser($match->getReceivingTeam());
            }

            $result = EloCalculator::DRAW;
            if ($match->getHostingTeamScore() > $match->getReceivingTeamScore()) {
                $result = EloCalculator::WIN;
            } else if ($match->getHostingTeamScore() < $match->getReceivingTeamScore()) {
                $result = EloCalculator::LOSE;
            }

            $eloEvolution = $eloCalculator->getEloEvolutionWithGoals($match);

            $match->getHostingTeam()?->addRating($eloEvolution);
            $match->getReceivingTeam()?->addRating(-$eloEvolution);

            $manager->persist($match);
            $manager->flush();

            $this->addFlash('success', "Le match " . $match->getHostingTeam() . " - " . $match->getReceivingTeam() .  " a bien été créé. Les classements Elo des équipes
            ont été ajustés.");

            return $this->redirectToRoute('tournament_view', [
                'id' => $match->getTournament()?->getId()
            ]);
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

        $form = $this->createForm(FootballMatchType::class, $match);

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

    public function lastMatch(): Response
    {
        return new Response('Dernier match : ');
    }

    #[Route('/match/cancel/{id}', name: 'match_cancel')]
    #[IsGranted('ROLE_USER')]
    public function cancel(FootballMatch $match, EntityManagerInterface $manager): Response
    {
        $hostingTeam = $match->getHostingTeam();
        $receivingTeam = $match->getReceivingTeam();

        $tournament = $match->getTournament();

        if ($hostingTeam === null || $receivingTeam === null || $tournament === null) {
            return $this->redirectToRoute($match->getTournament());
        }

        $logRepo = $manager->getRepository(LogEntry::class);

        $hostingTeamLog = $logRepo->getLogEntries($hostingTeam);
        $receivingTeamLog = $logRepo->getLogEntries($receivingTeam);

        if (count($hostingTeamLog) > 1 && count($receivingTeamLog) > 1) {

            $lastHostingLog = $hostingTeamLog[0];
            $lastReceivingLog = $receivingTeamLog[0];
            $prevHostingLog = $hostingTeamLog[1];
            $prevReceivingLog = $receivingTeamLog[1];

            $manager->remove($lastHostingLog);
            $manager->remove($lastReceivingLog);

            $logRepo->revert($hostingTeam, $prevHostingLog->getVersion());
            $logRepo->revert($receivingTeam, $prevReceivingLog->getVersion());

            $manager->persist($hostingTeam);
            $manager->persist($receivingTeam);
            $manager->remove($match);

            $manager->flush();

            $hostingTeamLog = $logRepo->getLogEntries($hostingTeam);
            $receivingTeamLog = $logRepo->getLogEntries($receivingTeam);

            $lastHostingLog = $hostingTeamLog[0];
            $lastReceivingLog = $receivingTeamLog[0];

            $manager->remove($lastHostingLog);
            $manager->remove($lastReceivingLog);

        }

        return $this->redirectToRoute('tournament_view', [
            'id' => $tournament->getId()
        ]);
    }
}
