<?php

namespace App\Repository;

use App\Entity\Topic;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TopicRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Topic::class);
    }

    /**
     * Find recent topics with their authors
     *
     * @param int $limit Maximum number of topics to return
     * @return array
     */
    public function findRecentTopics(int $limit = 5): array
    {
        return $this->createQueryBuilder('t')
            ->select('t', 'a') // Select topics and authors
            ->leftJoin('t.author', 'a')
            ->orderBy('t.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Count topics created today
     *
     * @return int
     */
    public function countTodayTopics(): int
    {
        $today = new \DateTime('today');
        
        return $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->where('t.createdAt >= :today')
            ->setParameter('today', $today)
            ->getQuery()
            ->getSingleScalarResult();
    }
}