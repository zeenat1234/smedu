<?php

namespace App\Repository;

use App\Entity\OptionalsAttendance;
use App\Entity\Student;
use DateTime;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method OptionalsAttendance|null find($id, $lockMode = null, $lockVersion = null)
 * @method OptionalsAttendance|null findOneBy(array $criteria, array $orderBy = null)
 * @method OptionalsAttendance[]    findAll()
 * @method OptionalsAttendance[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OptionalsAttendanceRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, OptionalsAttendance::class);
    }

    /**
     * @return OptionalsAttendance[] Returns an array of OptionalsAttendance objects
     */
    public function findAllByMonth(\DateTimeInterface $mY)
    {
        return $this->createQueryBuilder('oa')
            ->leftJoin('oa.optionalSchedule', 'sched')
            ->andWhere('sched.scheduledDateTime >= :firstOfMonth')
            ->andWhere('sched.scheduledDateTime <= :lastOfMonth')
            ->setParameter('firstOfMonth', $mY->modify('first day of this month')->format('Y-m-d H:i:s'))
            ->setParameter('lastOfMonth', $mY->modify('last day of this month')->format('Y-m-d H:i:s'))
            ->orderBy('oa.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return OptionalsAttendance[] Returns an array of OptionalsAttendance objects
     */
    public function findAllForStudByMonth(\DateTimeInterface $mY, $student)
    {
        return $this->createQueryBuilder('oa')
            ->andWhere('oa.student = :theStudent')
            ->setParameter('theStudent', $student->getId())
            ->leftJoin('oa.optionalSchedule', 'sched')
            ->andWhere('sched.scheduledDateTime >= :firstOfMonth')
            ->andWhere('sched.scheduledDateTime <= :lastOfMonth')
            ->setParameter('firstOfMonth', $mY->modify('first day of this month')->format('Y-m-d H:i:s'))
            ->setParameter('lastOfMonth', $mY->modify('last day of this month')->format('Y-m-d H:i:s'))
            ->orderBy('oa.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return OptionalsAttendance[] Returns an array of OptionalsAttendance objects
     */
    public function findAllForStudByInterval(\DateTimeInterface $start, \DateTimeInterface $end, Student $student)
    {
        return $this->createQueryBuilder('oa')
            ->andWhere('oa.student = :theStudent')
            ->setParameter('theStudent', $student->getId())
            ->leftJoin('oa.optionalSchedule', 'sched')
            ->andWhere('sched.scheduledDateTime >= :firstDay')
            ->andWhere('sched.scheduledDateTime <= :lastDay')
            ->setParameter('firstDay', $start->format('Y-m-d H:i:s'))
            ->setParameter('lastDay', $end->format('Y-m-d H:i:s'))
            ->orderBy('oa.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @param Student  $student
     * @param \DateTime $start
     * @param \DateTime $end
     */
    public function getAllOptionalsByStudent($student, \DateTime $start, \DateTime $end)
    {
        //SELECT *, sum(has_attended) FROM `optionals_attendance` oa join optional_schedule os on oa.optional_schedule_id = os.id WHERE oa.student_id = 195 group by student_id, oa.class_optional_id
        return $this->createQueryBuilder('oa')
            ->select('oa as oOptionalsAttendance, SUM(oa.hasAttended) as optionalCount')
            ->innerJoin('oa.optionalSchedule', 'os', Join::WITH, 'oa.student = :student')
            //->innerJoin('oa.classOptional', 'co')
            ->groupBy('oa.student')
            ->addGroupBy('oa.classOptional')
            ->andWhere('os.scheduledDateTime >= :startDay')
            ->andWhere('os.scheduledDateTime <= :endDay')
            ->setParameter('student', 195)//$student->getId())
            ->setParameter('startDay', $start->format('Y-m-d H:i:s'))
            ->setParameter('endDay', $end->format('Y-m-d H:i:s'))
            ->getQuery()
            ->getResult();

        //var_dump($q->getSQL());

        //return $q
          //  ->getResult();
    }


//    /**
//     * @return OptionalsAttendance[] Returns an array of OptionalsAttendance objects
//     */
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
    public function findOneBySomeField($value): ?OptionalsAttendance
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
