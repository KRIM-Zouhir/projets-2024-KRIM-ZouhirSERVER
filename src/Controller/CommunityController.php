<?php

namespace App\Controller;

use App\Entity\Community;
use App\Form\CommunityType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class CommunityController extends AbstractController
{
    #[Route('/community/new', name: 'app_community_new')]
    #[IsGranted('ROLE_USER')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $community = new Community();
        $community->setCreator($this->getUser());
        
        $form = $this->createForm(CommunityType::class, $community);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($community);
            $entityManager->flush();

            $this->addFlash('success', 'Community created successfully!');
            return $this->redirectToRoute('app_community_show', ['id' => $community->getId()]);
        }

        return $this->render('community/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/community/{id}', name: 'app_community_show')]
    public function show(Community $community): Response
    {
        return $this->render('community/show.html.twig', [
            'community' => $community,
        ]);
    }
}