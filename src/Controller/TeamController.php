<?php

namespace App\Controller;

use App\Entity\Team;
use App\Form\TeamType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TeamController extends AbstractController
{
    #[Route('/', name: 'team_index')]
    public function index(EntityManagerInterface $manager): Response
    {
        $teams = $manager->getRepository(Team::class)->findRankings();
        return $this->render('team/index.html.twig', [
            'teams' => $teams
        ]);
    }

    #[Route('/team/create', name: 'team_create')]
    public function create(Request $request, EntityManagerInterface $manager): Response
    {
        $team = new Team();
        $form = $this->createForm(TeamType::class, $team);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($team);
            $manager->flush();

            $this->addFlash('success', "L'équipe \"" . $team->getName() . "\" a bien été créée.");
        }

        return $this->render('team/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/team/{id}', name: 'team_view', requirements: [
        'id' => '\d+'
    ])]
    public function view(Team $team, Request $request, EntityManagerInterface $manager): Response
    {
        return $this->render('team/view.html.twig', [
            'team' => $team
        ]);
    }

    #[Route('/team/edit/{id}', name: 'team_edit', requirements: [
        'id' => '\d+'
    ])]
    public function edit(Team $team, Request $request, EntityManagerInterface $manager): Response
    {
        $form = $this->createForm(TeamType::class, $team);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->flush();
            $this->addFlash('success', "L'équipe \"" . $team->getName() . "\" a bien été éditée.");
        }

        return $this->render('team/edit.html.twig', [
            'form' => $form->createView(),
            'team' => $team
        ]);
    }
}
