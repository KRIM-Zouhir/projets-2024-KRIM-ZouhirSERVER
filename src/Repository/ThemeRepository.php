<?php

namespace App\Repository;

use App\Entity\Theme;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;
/**
 * @extends ServiceEntityRepository<Theme>
 */
class ThemeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Theme::class);
    }
    
    public function getPaginatedThemes(int $currentPage, int $perPage): array
    {
        $query = $this->createQueryBuilder('t')
            ->select('t', 'COUNT(d.id) as discussionCount', 'MAX(d.createdAt) as lastDiscussionDate')
            ->leftJoin('t.discussions', 'd')
            ->groupBy('t.id')
            ->orderBy('lastDiscussionDate', 'DESC')
            ->getQuery();

        $paginator = new Paginator($query);
        $totalItems = count($paginator);
        $lastPage = ceil($totalItems / $perPage);

        $query->setFirstResult(($currentPage - 1) * $perPage)
                ->setMaxResults($perPage);

        return [
            'results' => $query->getResult(),
            'totalItems' => $totalItems,
            'lastPage' => max(1, $lastPage),
            'currentPage' => $currentPage
        ];
    }
    
}
