<?php

namespace App\Controller;

use App\Entity\FootballMatch;
use App\Entity\Tournament;
use App\Form\TournamentType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TournamentController extends AbstractController
{
    #[Route('/tournament', name: 'tournament_index')]
    public function index(EntityManagerInterface $manager): Response
    {
        $tournaments = $manager->getRepository(Tournament::class)->findAll();
        return $this->render('tournament/index.html.twig', [
            'tournaments' => $tournaments
        ]);
    }

    #[Route('/tournament/{id}', name: 'tournament_view', requirements: [
        "id" => "\d+"
    ])]
    public function view(Tournament $tournament, EntityManagerInterface $manager): Response
    {
        $matches = $manager->getRepository(FootballMatch::class)->findBy([
            'tournament' => $tournament
        ], ['id' => 'desc']);

        return $this->render('tournament/view.html.twig', [
            'tournament' => $tournament,
            'matches' => $matches
        ]);
    }

    #[Route('/tournament/create', name: 'tournament_create')]
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
}
