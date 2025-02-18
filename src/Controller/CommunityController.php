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
    #[Route('/community/explore', name: 'app_community_explore')]
    public function explore(Request $request, EntityManagerInterface $entityManager): Response
    {
        $page = $request->query->getInt('page', 1);
        $limit = 12; // Communities per page

        $communities = $entityManager->getRepository(Community::class)
            ->createQueryBuilder('c')
            ->orderBy('c.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult(($page - 1) * $limit)
            ->getQuery()
            ->getResult();

        $totalCommunities = $entityManager->getRepository(Community::class)
            ->createQueryBuilder('c')
            ->select('count(c.id)')
            ->getQuery()
            ->getSingleScalarResult();

        return $this->render('community/explore.html.twig', [
            'communities' => $communities,
            'currentPage' => $page,
            'lastPage' => ceil($totalCommunities / $limit)
        ]);
    }

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

    #[Route('/community/{id}/join', name: 'app_community_join', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function join(Community $community, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if (!$community->getMembers()->contains($user)) {
            $community->addMember($user);
            $entityManager->flush();
            $this->addFlash('success', 'You have joined the community!');
        }

        return $this->redirectToRoute('app_community_show', ['id' => $community->getId()]);
    }

    #[Route('/community/{id}/leave', name: 'app_community_leave', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function leave(Community $community, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if ($community->getMembers()->contains($user)) {
            $community->removeMember($user);
            $entityManager->flush();
            $this->addFlash('success', 'You have left the community.');
        }

        return $this->redirectToRoute('app_community_show', ['id' => $community->getId()]);
    }

    #[Route('/community/{id}/search', name: 'app_community_search', methods: ['GET'])]
    public function search(Request $request, Community $community): Response
    {
        $query = $request->query->get('q');
        
        if (!$query) {
            return $this->redirectToRoute('app_community_show', ['id' => $community->getId()]);
        }

        $discussions = $community->getDiscussions()
            ->filter(function($discussion) use ($query) {
                return stripos($discussion->getTitle(), $query) !== false ||
                       stripos($discussion->getContent(), $query) !== false;
            });

        return $this->render('community/search.html.twig', [
            'community' => $community,
            'discussions' => $discussions,
            'query' => $query
        ]);
    }
}