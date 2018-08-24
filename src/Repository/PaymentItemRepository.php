<?php

namespace App\Repository;

use App\Entity\PaymentItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method PaymentItem|null find($id, $lockMode = null, $lockVersion = null)
 * @method PaymentItem|null findOneBy(array $criteria, array $orderBy = null)
 * @method PaymentItem[]    findAll()
 * @method PaymentItem[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PaymentItemRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, PaymentItem::class);
    }

//    /**
//     * @return PaymentItem[] Returns an array of PaymentItem objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?PaymentItem
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
