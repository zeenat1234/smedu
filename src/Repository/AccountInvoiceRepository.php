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


    public function findLatestBySerial($serial): ?AccountInvoice
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.invoiceSerial = :val')
            ->setParameter('val', $serial)
            ->orderBy('a.invoiceNumber', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
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


}
