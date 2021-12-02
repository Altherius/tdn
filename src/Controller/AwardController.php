<?php

namespace App\Controller;

use App\Entity\Award;
use App\Form\AwardType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AwardController extends AbstractController
{
    #[Route('/award/create', name: 'award_create')]
    public function create(Request $request, EntityManagerInterface $manager): Response
    {
        $award = new Award();
        $form = $this->createForm(AwardType::class, $award);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($award);
            $manager->flush();

            $this->addFlash('success', "La récompense \"$award\" a bien été créée");
        }

        return $this->render('award/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/award', name: 'award_gallery')]
    public function gallery(EntityManagerInterface $manager): Response
    {
        $awards = $manager->getRepository(Award::class)->findAll();

        return $this->render('award/list.html.twig', [
            'awards' => $awards
        ]);
    }
}
