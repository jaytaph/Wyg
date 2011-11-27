<?php

namespace Wyg\WygBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * MeetupRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class MeetupRepository extends EntityRepository
{

    public function getLatest($limit = 15)
    {
        $qb = $this->createQueryBuilder('b')
                   ->select('b')
                   ->addOrderBy('b.dt_created', 'DESC')
                   ->setMaxResults($limit);

        return $qb->getQuery()
                  ->getResult();
    }

}