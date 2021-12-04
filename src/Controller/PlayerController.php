<?php

namespace App\Controller;

use App\Entity\Player;
use App\Form\PlayerType;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PlayerController extends AbstractController
{
    #[Route('/player/create', name: 'player_create')]
    #[IsGranted('ROLE_USER')]
    public function create(Request $request, EntityManagerInterface $manager): Response
    {
        $player = new Player();
        $form = $this->createForm(PlayerType::class, $player);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($player);
            $manager->flush();

            $this->addFlash("success", "Le joueur \"$player\" a bien été créé");
        }

        return $this->render('player/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/player', name: 'player_list')]
    public function list(EntityManagerInterface $manager): Response
    {
        $players = $manager->getRepository(Player::class)->findAll();

        return $this->render('player/list.html.twig', [
            'players' => $players
        ]);
    }
}
