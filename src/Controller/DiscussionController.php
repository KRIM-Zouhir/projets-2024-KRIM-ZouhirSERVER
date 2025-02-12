<?php

namespace App\Controller;

use App\Entity\Discussion;
use App\Entity\Theme;
use App\Entity\Community;
use App\Form\DiscussionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class DiscussionController extends AbstractController
{
    #[Route('/discussion/new', name: 'app_create_discussion')]
    #[IsGranted('ROLE_USER')]
    // DiscussionController.php
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $discussion = new Discussion();
        $discussion->setAuthor($this->getUser());
        
        // Get community ID from request if available
        $communityId = $request->query->get('community');
        if ($communityId) {
            $community = $entityManager->getRepository(Community::class)->find($communityId);
            $discussion->setCommunity($community);
        }

        $form = $this->createForm(DiscussionType::class, $discussion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($discussion);
            $entityManager->flush();

            return $this->redirectToRoute('app_community_show', ['id' => $discussion->getCommunity()->getId()]);
        }

        return $this->render('discussion/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}