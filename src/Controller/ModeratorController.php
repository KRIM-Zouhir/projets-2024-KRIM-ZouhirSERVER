<?php

namespace App\Controller;
use App\Entity\User;
use App\Entity\Topic;
use App\Entity\Post;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Community;
use App\Entity\Discussion;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use App\Entity\Reply;


#[Route('/moderator')]
#[IsGranted('ROLE_MODERATOR')]
class ModeratorController extends AbstractController
{
    #[Route('/', name: 'app_moderator_dashboard')]
    public function dashboard(EntityManagerInterface $entityManager): Response
    {
        // Get repositories
        $userRepository = $entityManager->getRepository(User::class);
        $communityRepository = $entityManager->getRepository(Community::class);
        $discussionRepository = $entityManager->getRepository(Discussion::class);

        // Calculate statistics
        $totalUsers = $userRepository->count([]);
        $totalCommunities = $communityRepository->count([]);
        $totalDiscussions = $discussionRepository->count([]);

        // Get new users today
        $today = new \DateTime('today');
        $newUsersToday = $userRepository->count([
            'createdAt' => $today
        ]);

        // Get recent data
        $recentData = [
            'users' => $userRepository->findBy([], ['createdAt' => 'DESC'], 5),
            'communities' => $communityRepository->findBy([], ['createdAt' => 'DESC'], 5),
            'discussions' => $discussionRepository->findBy([], ['createdAt' => 'DESC'], 5)
        ];

        return $this->render('admin/dashboard.html.twig', [
            'totalUsers' => $totalUsers,
            'totalCommunities' => $totalCommunities,
            'totalDiscussions' => $totalDiscussions,
            'newUsersToday' => $newUsersToday,
            'recentData' => $recentData,
        ]);
    }
    
    #[Route('/topic/{id}/edit', name: 'app_moderator_topic_edit')]
    public function editTopic(Request $request, Topic $topic, EntityManagerInterface $entityManager): Response
    {
        // Add your topic editing logic here
        if ($request->isMethod('POST')) {
            $topic->setTitle($request->request->get('title'));
            $topic->setContent($request->request->get('content'));
            
            $entityManager->flush();
            
            $this->addFlash('success', 'Topic updated successfully.');
            return $this->redirectToRoute('app_moderator_dashboard');
        }

        return $this->render('admin/edit_topic.html.twig', [
            'topic' => $topic,
        ]);
    }
    #[Route('/communities', name: 'app_moderator_communities')]
    public function listCommunities(
        Request $request, 
        EntityManagerInterface $entityManager,
        PaginatorInterface $paginator
    ): Response {
        $queryBuilder = $entityManager->getRepository(Community::class)
            ->createQueryBuilder('c')
            ->leftJoin('c.creator', 'u')
            ->leftJoin('c.members', 'm')
            ->leftJoin('c.discussions', 'd');

        // Apply search filter
        if ($search = $request->query->get('search')) {
            $queryBuilder->andWhere('c.name LIKE :search OR c.description LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        // Apply status filter
        if ($status = $request->query->get('status')) {
            $queryBuilder->andWhere('c.isArchived = :archived')
                ->setParameter('archived', $status === 'archived');
        }

        // Apply sorting
        switch ($request->query->get('sort', 'newest')) {
            case 'oldest':
                $queryBuilder->orderBy('c.createdAt', 'ASC');
                break;
            case 'members':
                $queryBuilder->groupBy('c.id')
                    ->orderBy('COUNT(m.id)', 'DESC');
                break;
            case 'activity':
                $queryBuilder->groupBy('c.id')
                    ->orderBy('COUNT(d.id)', 'DESC');
                break;
            default: // newest
                $queryBuilder->orderBy('c.createdAt', 'DESC');
        }

        $pagination = $paginator->paginate(
            $queryBuilder->getQuery(),
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('admin/communities.html.twig', [
            'communities' => $pagination
        ]);
    }

    #[Route('/discussions', name: 'app_moderator_discussions')]
    public function listDiscussions(
        Request $request, 
        EntityManagerInterface $entityManager,
        PaginatorInterface $paginator
    ): Response {
        $queryBuilder = $entityManager->getRepository(Discussion::class)
            ->createQueryBuilder('d')
            ->leftJoin('d.author', 'u')
            ->leftJoin('d.community', 'c')
            ->leftJoin('d.replies', 'r');

        // Apply search filter
        if ($search = $request->query->get('search')) {
            $queryBuilder->andWhere('d.title LIKE :search OR d.content LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        // Apply community filter
        if ($communityId = $request->query->get('community')) {
            $queryBuilder->andWhere('d.community = :communityId')
                ->setParameter('communityId', $communityId);
        }

        // Apply sorting
        switch ($request->query->get('sort', 'newest')) {
            case 'oldest':
                $queryBuilder->orderBy('d.createdAt', 'ASC');
                break;
            case 'replies':
                $queryBuilder->groupBy('d.id')
                    ->orderBy('COUNT(r.id)', 'DESC');
                break;
            case 'views':
                $queryBuilder->orderBy('d.views', 'DESC');
                break;
            default: // newest
                $queryBuilder->orderBy('d.createdAt', 'DESC');
        }

        $pagination = $paginator->paginate(
            $queryBuilder->getQuery(),
            $request->query->getInt('page', 1),
            10
        );

        // Get all communities for the filter dropdown
        $communities = $entityManager->getRepository(Community::class)
            ->findBy([], ['name' => 'ASC']);

        return $this->render('admin/discussions.html.twig', [
            'discussions' => $pagination,
            'communities' => $communities
        ]);
    }

    #[Route('/community/{id}/toggle-archive', name: 'app_moderator_community_archive', methods: ['POST'])]
    public function toggleCommunityArchive(
        Community $community, 
        Request $request,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $community->setIsArchived($data['archived']);
        $entityManager->flush();

        return new JsonResponse([
            'success' => true,
            'message' => sprintf(
                'Community has been %s successfully',
                $data['archived'] ? 'archived' : 'activated'
            )
        ]);
    }

    #[Route('/discussion/{id}/toggle-lock', name: 'app_moderator_discussion_lock', methods: ['POST'])]
    public function toggleDiscussionLock(
        Discussion $discussion, 
        Request $request,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $discussion->setIsLocked($data['locked']);
        $entityManager->flush();

        return new JsonResponse([
            'success' => true,
            'message' => sprintf(
                'Discussion has been %s successfully',
                $data['locked'] ? 'locked' : 'unlocked'
            )
        ]);
    }
    #[Route('/community/{id}/delete', name: 'app_moderator_community_delete', methods: ['POST'])]
    public function deleteCommunity(
        Community $community, 
        EntityManagerInterface $entityManager,
        Request $request
    ): Response {
        try {
            // Security check
            if (!$this->isGranted('ROLE_MODERATOR')) {
                throw new AccessDeniedException('Only moderators can delete communities.');
            }

            // Store community name for flash message
            $communityName = $community->getName();

            // Remove all discussions first to handle cascade properly
            foreach ($community->getDiscussions() as $discussion) {
                $entityManager->remove($discussion);
            }

            // Remove the community
            $entityManager->remove($community);
            $entityManager->flush();

            $this->addFlash('success', sprintf('Community "%s" has been deleted successfully.', $communityName));

            // If the request is AJAX, return JSON response
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse([
                    'success' => true,
                    'message' => sprintf('Community "%s" has been deleted successfully.', $communityName)
                ]);
            }

            return $this->redirectToRoute('app_moderator_communities');

        } catch (\Exception $e) {
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Failed to delete community: ' . $e->getMessage()
                ], 400);
            }

            $this->addFlash('error', 'Failed to delete community: ' . $e->getMessage());
            return $this->redirectToRoute('app_moderator_communities');
        }
    }
    #[Route('/discussion/{id}', name: 'app_discussion_show')]
    public function show(
        Discussion $discussion,
        Request $request,
        EntityManagerInterface $entityManager,
        PaginatorInterface $paginator
    ): Response {
        // Increment view count
        $discussion->incrementViews();
        $entityManager->flush();

        // Get replies with pagination
        $queryBuilder = $entityManager->getRepository(Reply::class)
            ->createQueryBuilder('r')
            ->where('r.discussion = :discussion')
            ->setParameter('discussion', $discussion)
            ->orderBy('r.createdAt', 'ASC');

        $replies = $paginator->paginate(
            $queryBuilder->getQuery(),
            $request->query->getInt('page', 1),
            10 // Number of replies per page
        );

        // Get related discussions from same community
        $relatedDiscussions = $entityManager->getRepository(Discussion::class)
            ->createQueryBuilder('d')
            ->where('d.community = :community')
            ->andWhere('d.id != :currentId')
            ->setParameter('community', $discussion->getCommunity())
            ->setParameter('currentId', $discussion->getId())
            ->orderBy('d.createdAt', 'DESC')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult();

        return $this->render('discussion/show.html.twig', [
            'discussion' => $discussion,
            'replies' => $replies,
            'relatedDiscussions' => $relatedDiscussions,
        ]);
    }

    #[Route('/discussion/{id}/delete', name: 'app_moderator_discussion_delete', methods: ['POST'])]
    public function deleteDiscussion(
        Discussion $discussion, 
        EntityManagerInterface $entityManager,
        Request $request
    ): Response {
        try {
            // Security check
            if (!$this->isGranted('ROLE_MODERATOR')) {
                throw new AccessDeniedException('Only moderators can delete discussions.');
            }

            // Store discussion title for flash message
            $discussionTitle = $discussion->getTitle();

            // Remove all replies first to handle cascade properly
            foreach ($discussion->getReplies() as $reply) {
                $entityManager->remove($reply);
            }

            // Remove the discussion
            $entityManager->remove($discussion);
            $entityManager->flush();

            $this->addFlash('success', sprintf('Discussion "%s" has been deleted successfully.', $discussionTitle));

            // If the request is AJAX, return JSON response
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse([
                    'success' => true,
                    'message' => sprintf('Discussion "%s" has been deleted successfully.', $discussionTitle)
                ]);
            }

            return $this->redirectToRoute('app_moderator_discussions');

        } catch (\Exception $e) {
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Failed to delete discussion: ' . $e->getMessage()
                ], 400);
            }

            $this->addFlash('error', 'Failed to delete discussion: ' . $e->getMessage());
            return $this->redirectToRoute('app_moderator_discussions');
        }
    }

    #[Route('/discussion/reply/{id}/delete', name: 'app_moderator_reply_delete', methods: ['POST'])]
    public function deleteReply(
        Reply $reply, 
        EntityManagerInterface $entityManager,
        Request $request
    ): Response {
        try {
            // Security check
            if (!$this->isGranted('ROLE_MODERATOR')) {
                throw new AccessDeniedException('Only moderators can delete replies.');
            }

            // Store the discussion ID for redirection
            $discussionId = $reply->getDiscussion()->getId();

            // Remove the reply
            $entityManager->remove($reply);
            $entityManager->flush();

            $this->addFlash('success', 'Reply has been deleted successfully.');

            // If the request is AJAX, return JSON response
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse([
                    'success' => true,
                    'message' => 'Reply has been deleted successfully.'
                ]);
            }

            // Redirect back to the discussion
            return $this->redirectToRoute('app_discussion_show', ['id' => $discussionId]);

        } catch (\Exception $e) {
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Failed to delete reply: ' . $e->getMessage()
                ], 400);
            }

            $this->addFlash('error', 'Failed to delete reply: ' . $e->getMessage());
            return $this->redirectToRoute('app_discussion_show', ['id' => $reply->getDiscussion()->getId()]);
        }
    }

    
    #[Route('/user/{id}/role', name: 'app_moderator_user_role', methods: ['POST'])]
    public function updateUserRole(
        Request $request,
        User $user,
        EntityManagerInterface $entityManager
    ): Response {
        // Basic security checks
        if (!$this->isGranted('ROLE_MODERATOR') || $user->getId() === $this->getUser()->getId()) {
            $this->addFlash('error', 'Permission denied');
            return $this->redirectToRoute('app_moderator_dashboard');
        }

        $action = $request->request->get('action');

        if ($action === 'add') {
            $user->addRole('ROLE_MODERATOR');
            $this->addFlash('success', 'Moderator privileges granted successfully');
        } elseif ($action === 'remove') {
            $user->removeRole('ROLE_MODERATOR');
            $this->addFlash('success', 'Moderator privileges removed successfully');
        }

        $entityManager->flush();
        return $this->redirectToRoute('app_moderator_dashboard');
    }
}