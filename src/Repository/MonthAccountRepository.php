<?php

namespace App\Repository;

use App\Entity\MonthAccount;
use App\Entity\Student;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method MonthAccount|null find($id, $lockMode = null, $lockVersion = null)
 * @method MonthAccount|null findOneBy(array $criteria, array $orderBy = null)
 * @method MonthAccount[]    findAll()
 * @method MonthAccount[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MonthAccountRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, MonthAccount::class);
    }

    /**
     * @return MonthAccount[] Returns an array of MonthAccount objects
     */

    public function findByStudent($studId)
    {
        return $this->createQueryBuilder('acc')
             ->andWhere('acc.student = :val')
             ->setParameter('val', $studId)
             ->addOrderBy('acc.accYearMonth', 'ASC')
             ->getQuery()
             ->getResult();
    }

    public function getAdvanceBalanceByStudent($studId)
    {
        return $this->createQueryBuilder('ma')
            ->select('sum(ma.advanceBalance) as advanceBalance')
            ->where('ma.student = :studId')
            ->groupBy('ma.student')
            ->setParameter('studId', $studId)
            ->getQuery()
            ->getResult()[0];
    }

    public function calculateAdvanceBalanceOld($studId, $monthAccountId)
    {
        $sql = "SELECT SUM(advance_balance) as totalAdvance 
            FROM month_account 
            WHERE student_id = :studId";
        $params = ["studId" => $studId];

        $totalAdvance = $this->getEntityManager()
            ->getConnection()
            ->executeQuery($sql, $params)
            ->fetch();

        $totalAdvance = $totalAdvance ? $totalAdvance['totalAdvance'] : 0;
        
        $sql = "UPDATE month_account 
                SET advance_balance = 0 
                WHERE student_id = :studId";
        $params = ["studId" => $studId];
        $this->getEntityManager()->getConnection()->executeQuery($sql, $params);

        $sql = "UPDATE month_account
                SET advance_balance = {$totalAdvance}
                WHERE student_id = :studId 
                    AND id = :monthAccountId";
        $params = [
            "studId" => $studId,
            "monthAccountId" => $monthAccountId
        ];
        $this->getEntityManager()->getConnection()->executeQuery($sql, $params);
    }

    public function calculateAdvanceBalance($accYearMonth)
    {
        $conn = $this->getEntityManager()->getConnection();

        // CALCULATE TOTAL ADVANCE FOR EACH STUDENT AND SAVE THE AMOUNT IN THE CURRENT MONTH
        $sql = "UPDATE month_account 
            JOIN (SELECT student_id, SUM(advance_balance) AS totalAdvance FROM month_account 
                    WHERE student_id IN (SELECT student_id FROM month_account WHERE acc_year_month = :accYearMonth) 
                    GROUP BY student_id
                ) AS totalsAdvance 
            ON month_account.student_id = totalsAdvance.student_id AND acc_year_month = :accYearMonth 
            SET month_account.advance_balance = totalsAdvance.totalAdvance";
        
        $params = ['accYearMonth' => $accYearMonth];
        $conn->executeQuery($sql, $params);

        // SET ADVANCE COLUMN TO 0 FOR ALL MONTHS EXCEPT CURRENT MONTH
        $sql = "UPDATE month_account 
            JOIN (SELECT id FROM month_account WHERE acc_year_month != :accYearMonth) AS table_1 ON month_account.id = table_1.id
            JOIN (SELECT student_id FROM month_account WHERE acc_year_month = :accYearMonth) AS table_2
                ON month_account.student_id = table_2.student_id
        SET month_account.advance_balance = 0";

        $params = ['accYearMonth' => $accYearMonth];
        $conn->executeQuery($sql, $params);
    }

    public function getOldAccounts ($student, $monthYear) {
        return $this->createQueryBuilder('ma')
            ->where('ma.student = :studentId')
            ->andWhere('ma.accYearMonth = :monthYear')
            ->setParameter('studentId', $student->getId())
            ->setParameter('monthYear', $monthYear)
            ->getQuery()
            ->getResult();
    }

//    /**
//     * @return MonthAccount[] Returns an array of MonthAccount objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?MonthAccount
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
