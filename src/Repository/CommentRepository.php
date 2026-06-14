<?php

namespace App\Repository;

use App\Entity\Comment;
use App\Entity\Project;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Comment>
 */
class CommentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Comment::class);
    }

    /** @return list<Comment> */
    public function findForProject(Project $project): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.project = :project')
            ->setParameter('project', $project)
            ->orderBy('c.createdAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /** @return list<Comment> */
    public function findRecentMentions(User $user, int $limit = 10): array
    {
        return $this->createQueryBuilder('c')
            ->innerJoin('c.mentions', 'm')
            ->where('m = :user')
            ->setParameter('user', $user)
            ->orderBy('c.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
