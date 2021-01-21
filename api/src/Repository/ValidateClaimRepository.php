<?php

namespace App\Repository;

use App\Entity\ValidateClaim;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ValidateClaim|null find($id, $lockMode = null, $lockVersion = null)
 * @method ValidateClaim|null findOneBy(array $criteria, array $orderBy = null)
 * @method ValidateClaim[]    findAll()
 * @method ValidateClaim[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ValidateClaimRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ValidateClaim::class);
    }

    // /**
    //  * @return Certificate[] Returns an array of Certificate objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Certificate
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
