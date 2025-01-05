<?php
namespace App\Controller;

use App\Entity\Discussion;
use App\Entity\Theme;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class DiscussionController extends AbstractController
{
    #[Route('/theme/{id}/add-discussion', name: 'app_add_discussion', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function addDiscussion(
        int $id, 
        Request $request, 
        EntityManagerInterface $entityManager
    ): Response {
        $content = $request->request->get('content');

        if (!$content) {
            $this->addFlash('error', 'Content cannot be empty');
            return $this->redirectToRoute('app_theme', ['id' => $id]);
        }

        $theme = $entityManager->getRepository(Theme::class)->find($id);
        if (!$theme) {
            throw $this->createNotFoundException('Theme not found');
        }

        $discussion = new Discussion();
        $discussion->setTheme($theme);
        $discussion->setAuthor($this->getUser());
        $discussion->setContent($content);
        $discussion->setCreatedAt(new \DateTime());

        $entityManager->persist($discussion);
        $entityManager->flush();

        return $this->redirectToRoute('app_theme', ['id' => $id]);
    }
}
