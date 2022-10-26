<?php

namespace App\Controller;

use App\Entity\FootballMatch;
use App\Entity\Tournament;
use App\Form\GenerateRosterType;
use App\Form\TournamentEditType;
use App\Form\TournamentType;
use App\Randomizer\Randomizer;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TournamentController extends AbstractController
{
    #[Route('/tournament', name: 'tournament_index')]
    public function index(EntityManagerInterface $manager): Response
    {
        $tournaments = $manager->getRepository(Tournament::class)->findBy([], ['id' => 'desc']);
        return $this->render('tournament/index.html.twig', [
            'tournaments' => $tournaments
        ]);
    }

    #[Route('/tournament/{id}', name: 'tournament_view', requirements: [
        "id" => "\d+"
    ])]
    public function view(int $id, EntityManagerInterface $manager): Response
    {
        $tournament = $manager->getRepository(Tournament::class)->findWithMatches($id);
        $matches = $manager->getRepository(FootballMatch::class)->findBy([
            'tournament' => $tournament
        ], ['id' => 'desc']);

        $lastMatch = $manager->getRepository(FootballMatch::class)->findTournamentLastMatch($tournament);

        return $this->render('tournament/view.html.twig', [
            'tournament' => $tournament,
            'matches' => $matches,
            'lastMatch' => $lastMatch
        ]);
    }

    #[Route('/tournament/generate-roster', name: 'tournament_generate_roster')]
    public function generateTournamentRoster(EntityManagerInterface $manager, Request $request): Response
    {
        $form = $this->createForm(GenerateRosterType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $tickets = new ArrayCollection();
            $teamsCount = 0;
            foreach ($form->getData()['threeTicketsTeams'] as $team) {
                $teamsCount++;
                for ($i = 0 ; $i < 3 ; ++$i) {
                    $tickets->add($team);
                }
            }
            foreach ($form->getData()['twoTicketsTeams'] as $team) {
                $teamsCount++;
                for ($i = 0 ; $i < 2 ; ++$i) {
                    $tickets->add($team);
                }
            }
            foreach ($form->getData()['oneTicketTeams'] as $team) {
                $teamsCount++;
                $tickets->add($team);
            }

            $teams = [];
            foreach ($form->getData()['qualifiedTeams'] as $team) {
                $teamsCount++;
                $teams[] = $team;
            }

            $tickets = new ArrayCollection(array_values($tickets->toArray()));
            $ticketsCount = $tickets->count();
            if ($form->getData()['teamsCount'] > $teamsCount) {
                $this->addFlash('warning', sprintf("Vous avez voulu générer un tournoi avec %s équipes mais vous n'en avez saisi que %s", $form->getData()['teamsCount'], $teamsCount));
            }

            for ($i = 0 ; $i < ($form->getData()['teamsCount'] - count($form->getData()['qualifiedTeams'])) ; ++$i) {

                if ($ticketsCount < 1) {
                    break;
                }
                $teamIndex = random_int(0, $ticketsCount - 1);
                $team = $tickets->get($teamIndex);
                $teams[] = $team;

                do {
                    $hasMore = $tickets->removeElement($team);
                } while($hasMore);

                $tickets = new ArrayCollection(array_values($tickets->toArray()));
                $ticketsCount = $tickets->count();
            }

            usort($teams, static function($a, $b) {
                return $a->getRating() < $b->getRating();
            });
        }

        return $this->renderForm('tournament/generate_roster.html.twig', [
            'form' => $form,
            'teams' => $teams ?? null,
        ]);
    }


    #[Route('/tournament/create', name: 'tournament_create')]
    #[IsGranted('ROLE_USER')]
    public function create(Request $request, EntityManagerInterface $manager): Response
    {
        $tournament = new Tournament();
        $form = $this->createForm(TournamentType::class, $tournament);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($tournament);
            $manager->flush();

            $this->addFlash('success', "Le tournoi \"" . $tournament->getName() . "\" a bien été créée.");
            return $this->redirectToRoute('tournament_index');
        }

        return $this->render('tournament/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/tournament/edit/{id}', name: 'tournament_edit', requirements: [
        "id" => "\d+"
    ])]
    public function edit(Tournament $tournament, Request $request, EntityManagerInterface $manager): Response
    {
        $form = $this->createForm(TournamentEditType::class, $tournament);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $manager->flush();

            $this->addFlash('success', "Le tournoi \"" . $tournament->getName() . "\" a bien été édité.");
            return $this->redirectToRoute('tournament_view', [
                'id' => $tournament->getId()
            ]);
        }

        return $this->render('tournament/edit.html.twig', [
            'form' => $form->createView(),
            'tournament' => $tournament
        ]);
    }
}
