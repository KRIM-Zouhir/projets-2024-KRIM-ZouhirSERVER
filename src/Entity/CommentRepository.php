<?php

namespace App\Repository;

use App\Entity\Comment;
use App\Entity\Discussion;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Comment>
 *
 * @method Comment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Comment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Comment[]    findAll()
 * @method Comment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Comment::class);
    }

    /**
     * Find comments for a specific discussion with optional filtering
     * 
     * @param Discussion $discussion
     * @param array $filters
     * @return Comment[]
     */
    public function findCommentsByDiscussion(
        Discussion $discussion, 
        array $filters = []
    ): array {
        $qb = $this->createQueryBuilder('c')
            ->where('c.discussion = :discussion')
            ->setParameter('discussion', $discussion)
            ->orderBy('c.createdAt', 'DESC');

        // Apply additional filters
        if (isset($filters['excludeDeleted']) && $filters['excludeDeleted'] === true) {
            $qb->andWhere('c.isDeleted = false');
        }

        // Pagination
        if (isset($filters['limit'])) {
            $qb->setMaxResults($filters['limit']);
        }

        // Optional author filtering
        if (isset($filters['author'])) {
            $qb->andWhere('c.author = :author')
               ->setParameter('author', $filters['author']);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Count comments for a discussion
     * 
     * @param Discussion $discussion
     * @param bool $excludeDeleted
     * @return int
     */
    public function countCommentsForDiscussion(
        Discussion $discussion, 
        bool $excludeDeleted = true
    ): int {
        $qb = $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.discussion = :discussion')
            ->setParameter('discussion', $discussion);

        if ($excludeDeleted) {
            $qb->andWhere('c.isDeleted = false');
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Find recent comments across all discussions
     * 
     * @param int $limit
     * @return Comment[]
     */
    public function findRecentComments(int $limit = 10): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.isDeleted = false')
            ->orderBy('c.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Add a new comment to a discussion
     * 
     * @param Comment $comment
     * @throws \Doctrine\ORM\ORMException
     */
    public function add(Comment $comment): void
    {
        $this->_em->persist($comment);
        $this->_em->flush();
    }

    /**
     * Delete or soft delete a comment
     * 
     * @param Comment $comment
     * @param bool $softDelete
     * @throws \Doctrine\ORM\ORMException
     */
    public function remove(Comment $comment, bool $softDelete = true): void
    {
        if ($softDelete) {
            $comment->markAsDeleted();
            $this->_em->persist($comment);
        } else {
            $this->_em->remove($comment);
        }
        $this->_em->flush();
    }
}