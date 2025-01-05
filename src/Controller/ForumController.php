<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Topic;
use App\Entity\Post;
use App\Form\TopicType;
use App\Form\PostType;
use App\Entity\Theme;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class ForumController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $themesPerPage = 5; // Max themes per page
        $page = $request->query->getInt('page', 1);
    
        $themeRepository = $entityManager->getRepository(Theme::class);
        $themes = $themeRepository->getPaginatedThemes($page, $themesPerPage);
    
        return $this->render('home/home.html.twig', [
            'themes' => $themes['results'],
            'lastPage' => $themes['lastPage'],
            'currentPage' => $themes['currentPage'],
            'totalItems' => $themes['totalItems']
        ]);
    }

    #[Route('/category/{id}', name: 'app_category_show')]
    public function showCategory(Category $category): Response
    {
        return $this->render('forum/category.html.twig', [
            'category' => $category,
        ]);
    }

    #[Route('/topic/new', name: 'app_topic_new')]
    public function newTopic(Request $request, EntityManagerInterface $entityManager): Response
    {
        $topic = new Topic();
        $form = $this->createForm(TopicType::class, $topic);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $topic->setAuthor($this->getUser());
            $entityManager->persist($topic);
            $entityManager->flush();

            return $this->redirectToRoute('app_topic_show', ['id' => $topic->getId()]);
        }

        return $this->render('forum/new_topic.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/topic/{id}', name: 'app_topic_show')]
    public function showTopic(Topic $topic): Response
    {
        return $this->render('forum/topic.html.twig', [
            'topic' => $topic,
        ]);
    }

    
}
