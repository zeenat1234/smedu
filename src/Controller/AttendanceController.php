<?php

namespace App\Controller;

#can instantiate the entity
use App\Entity\SchoolYear;
use App\Entity\SchoolUnit;
use App\Entity\ClassOptional;
use App\Entity\OptionalsAttendance;
use App\Entity\User;

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
     * @Route("/attendance/generate/opt/{optId}", name="generate_optional_attendance")
     * @Method({"GET" , "POST"})
     */
    public function generate_optional_attendance(Request $request, $optId) {

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

        return $this->redirectToRoute('attendance');
    }

    /**
     * @Route("/attendance/update/opt/{optId}", name="update_optional_attendance")
     * @Method({"GET" , "POST", "DELETE"})
     */
    public function update_optional_attendance(Request $request, $optId) {

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

        return $this->redirectToRoute('attendance');
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
                $i = 0;
                foreach ($data['attends'] as $attendance) {
                  $entityManager = $this->getDoctrine()->getManager();
                  $entityManager->persist($attendance);
                  $entityManager->flush();
                  $i++;
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
