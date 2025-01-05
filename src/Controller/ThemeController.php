<?php

namespace App\Controller;

use App\Entity\Discussion;
use App\Entity\Theme;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ThemeController extends AbstractController
{
    #[Route('/theme/{id}', name: 'app_theme')]
    public function viewTheme(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $discussionsPerPage = 10; // Max discussions per page
        $page = $request->query->getInt('page', 1);
    
        $themeRepository = $entityManager->getRepository(Theme::class);
        $theme = $themeRepository->find($id);
    
        if (!$theme) {
            throw $this->createNotFoundException('Theme not found');
        }
    
        $discussionRepository = $entityManager->getRepository(Discussion::class);
        $discussions = $discussionRepository->getPaginatedDiscussionsByTheme($id, $page, $discussionsPerPage);
    
        return $this->render('theme/theme.html.twig', [
            'theme' => $theme,
            'discussions' => $discussions,
        ]);
    }
    
}
