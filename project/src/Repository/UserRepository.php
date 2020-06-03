<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function findPossibleMatchesByDistance(User $user, array $activeUsers, int $distance)
    {
        $queryBuilder = $this->createQueryBuilder('u');
    
        return $this->createQueryBuilder('u')
            ->addSelect(
                '( 3959 * acos(cos(radians(:latitude))' .
                    '* cos(radians(u.latitude))' .
                    '* cos(radians(u.longitude)' .
                    '- radians(:longitude))' .
                    '+ sin(radians(:latitude))' .
                    '* sin(radians(u.latitude)))) as distance'
            )
            ->andWhere($queryBuilder->expr()->eq('u.active', ':isActive'))
            ->andWhere($queryBuilder->expr()->eq('u.verified', ':isVerified'))
            ->andWhere($queryBuilder->expr()->neq('u', ':user'))
            ->having($queryBuilder->expr()->lt('distance', ':distance'))
            ->setParameters([
                'latitude' => $user->getLatitude(),
                'longitude' => $user->getLongitude(),
                'user' => $user,
                'distance' => $distance,
                'isActive' => true,
                'isVerified' => true
            ])
            ->getQuery()
            ->getResult();
    }
}
