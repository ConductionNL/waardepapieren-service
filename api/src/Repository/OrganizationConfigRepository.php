<?php

namespace App\Repository;

use App\Entity\OrganizationConfig;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method OrganizationConfig|null find($id, $lockMode = null, $lockVersion = null)
 * @method OrganizationConfig|null findOneBy(array $criteria, array $orderBy = null)
 * @method OrganizationConfig[]    findAll()
 * @method OrganizationConfig[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrganizationConfigRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OrganizationConfig::class);
    }

    // /**
    //  * @return OrganizationConfig[] Returns an array of OrganizationConfig objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('o.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?OrganizationConfig
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
