<?php

namespace App\Controller;

#can instantiate the entity
use App\Entity\SchoolYear;
use App\Entity\SchoolUnit;
use App\Entity\ClassOptional;
use App\Entity\OptionalSchedule;
use App\Entity\OptionalsAttendance;
use App\Entity\User;
use App\Entity\Student;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#allows us to restrict methods like get and post
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

#form type definition
use App\Form\ClassOptionalType;
use App\Form\ClassOptionalEnrollType;
use App\Form\OptionalsAttendanceType;

use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\FormType;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AttendanceController extends AbstractController
{
    /**
     * @Route("/attendance", name="attendance")
     * @Method({"GET"})
     */
    public function index()
    {
        $currentSchoolYear = $this->getDoctrine()->getRepository
        (SchoolYear::class)->findCurrentYear();

        $schoolUnits = $currentSchoolYear->getSchoolunits();

        return $this->render('attendance/attendance.html.twig', [
          'current_year'  => $currentSchoolYear,
          'current_units' => $schoolUnits,
          'modules'       => [],
        ]);
    }

    /**
     * @Route("/attendance/manual_mods", name="manual_mods")
     * @Method({"GET"})
     */
    public function manual_mods()
    {
        $currentSchoolYear = $this->getDoctrine()->getRepository
        (SchoolYear::class)->findCurrentYear();

        $schoolUnits = $currentSchoolYear->getSchoolunits();

        return $this->render('attendance/manual.mods.html.twig', [
          'current_year'  => $currentSchoolYear,
          'current_units' => $schoolUnits,
          'modules'       => [],
        ]);
    }

    /**
     * @Route("/attendance/man_mods_del/{attId}", name="manual_del_attd")
     * @Method({"GET", "POST", "DELETE"})
     */
    public function manual_del_attd($attId)
    {
      $attendance = $this->getDoctrine()->getRepository
      (OptionalsAttendance::class)->find($attId);

      $entityManager = $this->getDoctrine()->getManager();
      $entityManager->remove($attendance);
      $entityManager->flush();

      $update_date = $attendance->getOptionalSchedule()->getScheduledDateTime();
      $formatter = new \IntlDateFormatter(\Locale::getDefault(), \IntlDateFormatter::NONE, \IntlDateFormatter::NONE);
      $formatter->setPattern('dd MMMM, yyyy');

      $this->get('session')->getFlashBag()->add(
          'notice',
          'Prezența pentru '.$attendance->getStudent()->getUser()->getRoName().' din data de '.$formatter->format($update_date).' a fost ștearsă cu succes!'
      );

      return $this->redirectToRoute('manual_mods_edit', array('schdId'=>$attendance->getOptionalSchedule()->getId()));
    }

    /**
     * @Route("/attendance/man_mods_add/{studId}_{schdId}", name="manual_add_attd")
     * @Method({"GET", "POST"})
     */
    public function manual_add_attd($studId, $schdId)
    {
      $schedule = $this->getDoctrine()->getRepository
      (OptionalSchedule::class)->find($schdId);

      $student = $this->getDoctrine()->getRepository
      (Student::class)->find($studId);

      $newAttendance = new OptionalsAttendance();

      $newAttendance->setClassOptional($schedule->getClassOptional());
      $newAttendance->setOptionalSchedule($schedule);
      $newAttendance->setStudent($student);

      $entityManager = $this->getDoctrine()->getManager();
      $entityManager->persist($newAttendance);
      $entityManager->flush();

      $update_date = $schedule->getScheduledDateTime();
      $formatter = new \IntlDateFormatter(\Locale::getDefault(), \IntlDateFormatter::NONE, \IntlDateFormatter::NONE);
      $formatter->setPattern('dd MMMM, yyyy');

      $this->get('session')->getFlashBag()->add(
          'notice',
          'Prezența pentru '.$student->getUser()->getRoName().' din data de '.$formatter->format($update_date).' a fost adăugată cu succes!'
      );

      return $this->redirectToRoute('manual_mods_edit', array('schdId'=>$schedule->getId()));

    }

    /**
     * @Route("/attendance/manual_mods/{schdId}", name="manual_mods_edit")
     * @Method({"GET", "POST"})
     */
    public function manual_mods_edit(Request $request, $schdId)
    {
        $theSchedule = $this->getDoctrine()->getRepository
        (OptionalSchedule::class)->find($schdId);

        $optional = $theSchedule->getClassOptional();

        $editableScheds = array();
        foreach($optional->getAscOptionalSchedules() as $schedule) {
          if ($schedule->getScheduledDateTime() <= (new \DateTime('now')) ) {
            $editableScheds[] = $schedule;
          }
        }

        $first_sched = $editableScheds[0];
        $last_sched = $editableScheds[count($editableScheds)-1];
        $prev_sched = null;
        $next_sched = null;

        if ($first_sched != $last_sched) {
          if ($first_sched == $theSchedule) {
            $prev_sched = null; //not needed - keeping for easy code read
            $next_sched_key = array_search($theSchedule, $editableScheds) + 1;
            $next_sched = $editableScheds[$next_sched_key];
          } else if ($last_sched == $theSchedule) {
            $next_sched = null; //not needed - keeping for easy code read
            $prev_sched_key = array_search($theSchedule, $editableScheds) - 1;
            $prev_sched = $editableScheds[$prev_sched_key];
          } else {
            $next_sched_key = array_search($theSchedule, $editableScheds) + 1;
            $next_sched = $editableScheds[$next_sched_key];
            $prev_sched_key = array_search($theSchedule, $editableScheds) - 1;
            $prev_sched = $editableScheds[$prev_sched_key];
          }
        }

        $sortedStudents = $this->getDoctrine()->getRepository
        (Student::class)->findAllUnit($optional->getSchoolUnit()->getId());

        $attendances = $this->getDoctrine()->getRepository
        (OptionalsAttendance::class)->findBy(['optionalSchedule' => $theSchedule]);

        $enrolledAttendances = array();
        $enrolledStudents = array();
        $enrollableStudents = array();
        //sorted by student
        foreach($sortedStudents as $student) {
          foreach($attendances as $attendance) {
            if ($student == $attendance->getStudent()) {
              $enrolledAttendances[] = $attendance;
              $enrolledStudents[] = $student;
            }
          }
          if (!in_array($student, $enrolledStudents)) {
            if ($student->getEnrollment()->getIsActive()) {
              $enrollableStudents[] = $student;
            }
          }
        }

        $formFactory = $this->get('form.factory');
        $form = $formFactory->createNamedBuilder('attendance_form', FormType::class, array('attends' => $enrolledAttendances));

        $form->add('attends', CollectionType::class, array(
          'label' => false,
          'entry_type' => OptionalsAttendanceType::class,
        ));
        // $form->add('update', SubmitType::class, array(
        //   'label' => 'Actualizează',
        // ));

        $the_form = $form->getForm();
        $the_view = $form->getForm()->createView();

        if ($request->isMethod('POST')) {

          $the_form->handleRequest($request);

          if ($the_form->isSubmitted()) {

            $data = $form->getData();

            // $data['attends'] contains an array of AppBundle\Entity\OptionalsAttendance
            // use it to persist the categories in a foreach loop
            foreach ($data['attends'] as $attendance) {
              $entityManager = $this->getDoctrine()->getManager();
              $entityManager->persist($attendance);
              $entityManager->flush();
            }

            $update_date = $data['attends'][0]->getOptionalSchedule()->getScheduledDateTime();
            $formatter = new \IntlDateFormatter(\Locale::getDefault(), \IntlDateFormatter::NONE, \IntlDateFormatter::NONE);
            $formatter->setPattern('dd MMMM, yyyy');

            $this->get('session')->getFlashBag()->add(
                'notice',
                'Informația pentru '.$formatter->format($update_date).' a fost actualizată cu succes!'
            );

            return $this->redirectToRoute('manual_mods_edit', array('schdId'=>$schdId));
          }

        }

        return $this->render('attendance/manual.mods.single.html.twig', [
          'the_schedule' => $theSchedule,
          'prev_sched' => $prev_sched,
          'next_sched' => $next_sched,
          'the_optional' => $optional,
          'enrolled_attendances' => $enrolledAttendances,
          'enrollable_students' => $enrollableStudents,
          'form' => $the_view,
        ]);
    }

    /**
     * @Route("/attendance/generate/opt/{optId}/{redirect?'redirect'}", name="generate_optional_attendance")
     * @Method({"GET" , "POST"})
     */
    public function generate_optional_attendance(Request $request, $optId, $redirect) {

        $currentOptional = $this->getDoctrine()->getRepository
        (ClassOptional::class)->find($optId);

        if (count($currentOptional->getOptionalsAttendances()) == 0) {
          foreach ($currentOptional->getOptionalSchedules() as $sched) {
            if ($sched->getScheduledDateTime() > (new \DateTime('now'))) {
              foreach ($currentOptional->getStudents() as $stud) {
                  $attendanceRecord = new OptionalsAttendance();
                  $attendanceRecord->setClassOptional($currentOptional);
                  $attendanceRecord->setOptionalSchedule($sched);
                  $attendanceRecord->setStudent($stud);
                  $attendanceRecord->setHasAttended(0);

                  $entityManager = $this->getDoctrine()->getManager();
                  $entityManager->persist($attendanceRecord);
                  $entityManager->flush();
              }
            }
          }
        }

        if ($redirect == 'attendance') {
          return $this->redirectToRoute('attendance');
        } else if ($redirect == 'optional_enroll') {
          return $this->redirectToRoute('class_optional_students', array('id' => $optId) );
        } else if ($redirect == 'optional_schedule') {
          return $this->redirectToRoute('optional_schedule', array('id' => $optId) );
        } else {
          $redirect = '/';
        }

        //return $this->redirectToRoute('attendance');
    }

    /**
     * @Route("/attendance/update/opt/{optId}/{redirect?'redirect'}", name="update_optional_attendance")
     * @Method({"GET", "POST", "DELETE"})
     */
    public function update_optional_attendance(Request $request, $optId, $redirect) {

        $currentOptional = $this->getDoctrine()->getRepository
        (ClassOptional::class)->find($optId);

        //the following checks for new schedules or students and adds them accordingly
        foreach ($currentOptional->getOptionalSchedules() as $sched) {
            foreach ($currentOptional->getStudents() as $stud) {
                $result = $this->getDoctrine()->getRepository(OptionalsAttendance::class)->findOneBy(
                    array('optionalSchedule' => $sched, 'student' => $stud)
                );
                if (!$result && ($sched->getScheduledDateTime() > (new \DateTime('now'))) ) {
                    $attendanceRecord = new OptionalsAttendance();
                    $attendanceRecord->setClassOptional($currentOptional);
                    $attendanceRecord->setOptionalSchedule($sched);
                    $attendanceRecord->setStudent($stud);
                    $attendanceRecord->setHasAttended(0);

                    $entityManager = $this->getDoctrine()->getManager();
                    $entityManager->persist($attendanceRecord);
                    $entityManager->flush();
                } else {
                    //do nothing
                }
            }
        }

        //the following checks for removed students and removes attendance entries accordingly
        $currentAttendances = $currentOptional->getOptionalsAttendances();
        //orphan removal should work for schedules, otherwise create logic here
        foreach ($currentAttendances as $attendance) {
            $student = $attendance->getStudent();

            if (!$currentOptional->getStudents()->contains($student)) {
                if ($attendance->getOptionalSchedule()->getScheduledDateTime() > (new \DateTime('now'))) {
                    $entityManager = $this->getDoctrine()->getManager();
                    $entityManager->remove($attendance);
                    $entityManager->flush();
                }
            } else {
                //do nothing
            }
        }

        if ($redirect == 'attendance') {
          return $this->redirectToRoute('attendance');
        } else if ($redirect == 'optional_enroll') {
          return $this->redirectToRoute('class_optional_students', array('id' => $optId) );
        } else if ($redirect == 'optional_schedule') {
          return $this->redirectToRoute('optional_schedule', array('id' => $optId) );
        } else {
          $redirect = '/';
        }

    }

    /**
     * @Route("/attendance/view/{optId}", name="view_optional_attendance")
     * @Method({"GET"})
     */
    public function view_optional_attendance(Request $request, $optId)
    {
        $currentOptional = $this->getDoctrine()->getRepository
        (ClassOptional::class)->find($optId);

        return $this->render('attendance/view.for.optional.html.twig', [
          'optional'  => $currentOptional,
        ]);
    }

    /**
     * @Route("/attendance/edit/{optId}", name="edit_optional_attendance")
     * @Method({"GET", "POST"})
     */
    public function edit_optional_attendance(Request $request, $optId)
    {
        $currentOptional = $this->getDoctrine()->getRepository
        (ClassOptional::class)->find($optId);

        $all_attendances = $currentOptional->getOptionalsAttendances();
        $index = 0;
        foreach (array_reverse($currentOptional->getOptionalSchedules()->getValues()) as $schedule) {

          $sched_attds = $schedule->getOptionalsAttendances();

          if ($schedule->getScheduledDateTime() <= new \DateTime('now') && $sched_attds->count() != 0) {

            $formFactory = $this->get('form.factory');
            $form = $formFactory->createNamedBuilder('attendance_form_'.$index, FormType::class, array('attends' => $sched_attds));

            $form->add('attends', CollectionType::class, array(
              'label' => false,
              'entry_type' => OptionalsAttendanceType::class,
            ));
            $form->add('update', SubmitType::class, array(
              'label' => 'Actualizează',
            ));

            $forms[] = $form->getForm();
            $views[] = $form->getForm()->createView();

            $index++;
          }
        }

        if ($request->isMethod('POST')) {

            foreach ($forms as $form) {
              $form->handleRequest($request);
            }

            foreach ($forms as $form) {
              if ($form->isSubmitted()) {

                $data = $form->getData();

                // $data['attends'] contains an array of AppBundle\Entity\OptionalsAttendance
                // use it to persist the categories in a foreach loop
                foreach ($data['attends'] as $attendance) {
                  $entityManager = $this->getDoctrine()->getManager();
                  $entityManager->persist($attendance);
                  $entityManager->flush();
                }

                $update_date = $data['attends'][0]->getOptionalSchedule()->getScheduledDateTime();
                $formatter = new \IntlDateFormatter(\Locale::getDefault(), \IntlDateFormatter::NONE, \IntlDateFormatter::NONE);
                $formatter->setPattern('dd MMMM, yyyy');

                $this->get('session')->getFlashBag()->add(
                    'notice',
                    'Informația pentru '.$formatter->format($update_date).' a fost actualizată cu succes!'
                );

                return $this->redirectToRoute('edit_optional_attendance', array('optId'=>$optId));

              }
            }

        }

        return $this->render('attendance/edit.for.optional.html.twig', [
          'optional'  => $currentOptional,
          'forms' => $views,
        ]);
    }
}
