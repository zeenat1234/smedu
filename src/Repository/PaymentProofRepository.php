<?php

namespace App\Repository;

use App\Entity\PaymentProof;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method PaymentProof|null find($id, $lockMode = null, $lockVersion = null)
 * @method PaymentProof|null findOneBy(array $criteria, array $orderBy = null)
 * @method PaymentProof[]    findAll()
 * @method PaymentProof[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PaymentProofRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, PaymentProof::class);
    }

//    /**
//     * @return PaymentProof[] Returns an array of PaymentProof objects
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
    public function findOneBySomeField($value): ?PaymentProof
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
