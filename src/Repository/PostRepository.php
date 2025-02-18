<?php

namespace App\Repository;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    /**
     * Find recent posts with their authors
     *
     * @param int $limit Maximum number of posts to return
     * @return array
     */
    public function findRecentPosts(int $limit = 5): array
    {
        return $this->createQueryBuilder('p')
            ->select('p', 'a') // Select posts and authors
            ->leftJoin('p.author', 'a')
            ->orderBy('p.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Count posts created today
     *
     * @return int
     */
    public function countTodayPosts(): int
    {
        $today = new \DateTime('today');
        
        return $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->where('p.createdAt >= :today')
            ->setParameter('today', $today)
            ->getQuery()
            ->getSingleScalarResult();
    }
}