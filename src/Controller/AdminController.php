<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Topic;
use App\Entity\Post;
use App\Repository\UserRepository;
use App\Repository\TopicRepository;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserRepository $userRepository,
        private readonly TopicRepository $topicRepository,
        private readonly PostRepository $postRepository
    ) {}

    #[Route('', name: 'app_admin_dashboard')]
    public function dashboard(): Response
    {
        // Fetch users with pagination
        $users = $this->userRepository->findBy([], ['createdAt' => 'DESC'], 5);
        
        // Fetch recent topics
        $topics = $this->topicRepository->findBy([], ['createdAt' => 'DESC'], 5);
        
        // Fetch recent posts
        $posts = $this->postRepository->findBy([], ['createdAt' => 'DESC'], 5);

        // Get total counts
        $totalUsers = $this->userRepository->count([]);
        $totalTopics = $this->topicRepository->count([]);
        $totalPosts = $this->postRepository->count([]);

        // Get new users today
        $today = new \DateTime('today');
        $newUsersToday = $this->userRepository->count([
            'createdAt' => $today
        ]);

        return $this->render('admin/dashboard.html.twig', [
            'users' => $users,
            'topics' => $topics,
            'posts' => $posts,
            'totalUsers' => $totalUsers,
            'totalTopics' => $totalTopics,
            'totalPosts' => $totalPosts,
            'newUsersToday' => $newUsersToday
        ]);
    }

    #[Route('/users', name: 'app_admin_users')]
    public function users(): Response
    {
        $users = $this->userRepository->findAll();
        
        return $this->render('admin/users.html.twig', [
            'users' => $users
        ]);
    }

    #[Route('/user/{id}/role', name: 'app_admin_user_role', methods: ['POST'])]
    public function updateUserRole(User $user, string $role): Response
    {
        if (!in_array($role, ['ROLE_ADMIN', 'ROLE_MODERATOR', 'ROLE_USER'])) {
            throw $this->createAccessDeniedException('Invalid role specified.');
        }

        // Update user role
        if (!in_array($role, $user->getRoles())) {
            $user->addRole($role);
            $this->entityManager->flush();
            $this->addFlash('success', sprintf('User %s has been granted the role %s', $user->getUsername(), $role));
        }

        return $this->redirectToRoute('app_admin_users');
    }

    #[Route('/topics', name: 'app_admin_topics')]
    public function topics(): Response
    {
        $topics = $this->topicRepository->findAll();
        
        return $this->render('admin/topics.html.twig', [
            'topics' => $topics
        ]);
    }

    #[Route('/posts', name: 'app_admin_posts')]
    public function posts(): Response
    {
        $posts = $this->postRepository->findAll();
        
        return $this->render('admin/posts.html.twig', [
            'posts' => $posts
        ]);
    }

    #[Route('/topic/{id}/delete', name: 'app_admin_topic_delete', methods: ['POST'])]
    public function deleteTopic(Topic $topic): Response
    {
        $this->entityManager->remove($topic);
        $this->entityManager->flush();
        
        $this->addFlash('success', 'Topic deleted successfully');
        return $this->redirectToRoute('app_admin_topics');
    }

    #[Route('/post/{id}/delete', name: 'app_admin_post_delete', methods: ['POST'])]
    public function deletePost(Post $post): Response
    {
        $this->entityManager->remove($post);
        $this->entityManager->flush();
        
        $this->addFlash('success', 'Post deleted successfully');
        return $this->redirectToRoute('app_admin_posts');
    }
}