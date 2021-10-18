<?php

namespace App\Repository;

use App\Entity\FeedContent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * FeedContent repository contains some methods which are useful when
 * querying for feed information.
 *
 * @author Nikunj Bambhroliya <nikunjpatel190@gmail.com>
 *
 */
class FeedContentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FeedContent::class);
    }

    public function findLatest()
    {
        $qb = $this->createQueryBuilder('fc')
            ->addSelect('f', 'fc')
            ->innerJoin('fc.feed', 'f')
            ->where('fc.createdAt <= :now')
            ->orderBy('fc.createdAt', 'DESC')
            ->setParameter('now', new \DateTime());

        return $qb->getQuery()->getResult();
    }
}
