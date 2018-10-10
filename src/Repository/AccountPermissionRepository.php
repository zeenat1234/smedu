<?php

namespace App\Repository;

use App\Entity\AccountPermission;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method AccountPermission|null find($id, $lockMode = null, $lockVersion = null)
 * @method AccountPermission|null findOneBy(array $criteria, array $orderBy = null)
 * @method AccountPermission[]    findAll()
 * @method AccountPermission[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AccountPermissionRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, AccountPermission::class);
    }

//    /**
//     * @return AccountPermission[] Returns an array of AccountPermission objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?AccountPermission
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
