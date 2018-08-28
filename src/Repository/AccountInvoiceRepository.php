<?php

namespace App\Repository;

use App\Entity\AccountInvoice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method AccountInvoice|null find($id, $lockMode = null, $lockVersion = null)
 * @method AccountInvoice|null findOneBy(array $criteria, array $orderBy = null)
 * @method AccountInvoice[]    findAll()
 * @method AccountInvoice[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AccountInvoiceRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, AccountInvoice::class);
    }

//    /**
//     * @return AccountInvoice[] Returns an array of AccountInvoice objects
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
    public function findOneBySomeField($value): ?AccountInvoice
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
