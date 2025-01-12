<?php
namespace App\Controller;

use App\Entity\Discussion;
use App\Entity\Theme;
use App\Form\DiscussionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class DiscussionController extends AbstractController
{
    #[Route('/create-discussion', name: 'app_create_discussion')]
    #[IsGranted('ROLE_USER')]
    public function createDiscussion(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $themeId = $request->query->get('theme');
        $theme = null;
        
        if ($themeId) {
            $theme = $entityManager->getRepository(Theme::class)->find($themeId);
        }
    
        $discussion = new Discussion();
        $form = $this->createForm(DiscussionType::class, $discussion);
    
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $discussion->setTheme($theme);
            $discussion->setAuthor($this->getUser());
            $discussion->setCreatedAt(new \DateTimeImmutable());
    
            $entityManager->persist($discussion);
            $entityManager->flush();
    
            $this->addFlash('success', 'Discussion created successfully!');
            return $this->redirectToRoute('app_theme', ['id' => $id]);
        }
    
        // Get discussions for the theme
        $discussionRepository = $entityManager->getRepository(Discussion::class);
        $page = $request->query->getInt('page', 1);
        $discussionsPerPage = 10;
        $discussions = $discussionRepository->getPaginatedDiscussionsByTheme($id, $page, $discussionsPerPage);
        
        $totalDiscussions = $discussionRepository->count(['theme' => $theme]);
        $lastPage = ceil($totalDiscussions / $discussionsPerPage);
    
        return $this->render('theme/theme.html.twig', [
            'theme' => $theme,
            'discussions' => $discussions,
            'lastPage' => $lastPage,
            'currentPage' => $page,
            'form' => $form->createView(),
        ]);
    }
}
