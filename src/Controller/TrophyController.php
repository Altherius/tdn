<?php

namespace App\Controller;

use App\Entity\Trophy;
use App\Form\TrophyType;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TrophyController extends AbstractController
{
    #[Route('/trophy/create', name: 'trophy_create')]
    #[IsGranted('ROLE_USER')]
    public function create(Request $request, EntityManagerInterface $manager): Response
    {
        $trophy = new Trophy();
        $form = $this->createForm(TrophyType::class, $trophy);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($trophy);
            $manager->flush();

            $this->addFlash('success', "Le trophée \"$trophy\" a été créé avec succès");
        }

        return $this->render('trophy/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/trophy/gallery', name: 'trophy_gallery')]
    public function gallery(EntityManagerInterface $manager): Response
    {
        $trophies = $manager->getRepository(Trophy::class)->findAll();
        return $this->render('trophy/gallery.html.twig', [
            'trophies' => $trophies
        ]);
    }
}
