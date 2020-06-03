<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Match;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Match|null find($id, $lockMode = null, $lockVersion = null)
 * @method Match|null findOneBy(array $criteria, array $orderBy = null)
 * @method Match[]    findAll()
 * @method Match[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MatchRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Match::class);
    }

    public function findByDateUser(User $user, \DateTime $date)
    {
        $queryBuilder = $this->createQueryBuilder('m');

        return $this->createQueryBuilder('m')
            ->andWhere(
                $queryBuilder->expr()->orX(
                    $queryBuilder->expr()->eq('m.callerOne', ':user'),
                    $queryBuilder->expr()->eq('m.callerTwo', ':user')
                )
            )
            ->andWhere(
                $queryBuilder->expr()->like('Date(m.createdAt)', ':date')
            )
            ->setParameters([
                'user' => $user,
                'date' => $date->format('Y-m-d') . '%'
            ])
            ->getQuery()
            ->getResult();
    }
}
