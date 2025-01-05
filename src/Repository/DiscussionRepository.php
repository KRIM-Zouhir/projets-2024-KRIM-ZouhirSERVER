<?php

namespace App\Repository;

use App\Entity\Discussion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Discussion>
 */
class DiscussionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Discussion::class);
    }

    public function getPaginatedDiscussionsByTheme(int $themeId, int $page, int $discussionsPerPage)
    {
        $query = $this->createQueryBuilder('d')
            ->where('d.theme = :theme')
            ->setParameter('theme', $themeId)
            ->orderBy('d.createdAt', 'ASC')
            ->getQuery();
    
        return $query->setFirstResult(($page - 1) * $discussionsPerPage)
                    ->setMaxResults($discussionsPerPage)
                    ->getResult();
    }
    
}
