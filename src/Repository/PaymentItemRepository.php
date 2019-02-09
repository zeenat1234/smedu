<?php

namespace App\Repository;

use App\Entity\PaymentItem;
use App\Entity\MonthAccount;
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

    public function getOldPayItems($student, $monthYear)
    {
        return $this->createQueryBuilder('pi')
            ->innerJoin('pi.monthAccount', 'ma')
            ->where('ma.accYearMonth < :monthYear')
            ->andWhere('ma.student = :studentId')
            ->andWhere('pi.isInvoiced = 0')
            ->setParameter('monthYear', $monthYear->format('Y-m-d H:i:s'))
            ->setParameter('studentId', $student->getId())
            ->getQuery()
            ->getResult();


        $conn = $this->getEntityManager()->getConnection();
        $sql = "SELECT pi.id FROM month_account ma 
            INNER JOIN payment_item pi ON ma.id = pi.month_account_id
            WHERE ma.acc_year_month < :monthYear
            AND ma.student_id = :studentId
            AND pi.is_invoiced = 0";

        $params = [
            'monthYear' => $monthYear->format('Y-m-d H:i:s'),
            'studentId' => $student->getId()
        ];
        
        $q = $conn->prepare($sql);
        $q->execute($params);
        return $q->fetchAll();
    }

    public function getTotalPayItemByMonth(MonthAccount $monthAccount)
    {
        return $this->createQueryBuilder('pi')
            ->select('SUM(pi.itemPrice) as total')
            ->where('pi.monthAccount = :monthAccount')
            ->setParameter('monthAccount', $monthAccount->getId())
            ->getQuery()
            ->getOneOrNullResult()['total'];
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
