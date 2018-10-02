<?php

namespace App\Controller;

#can instantiate the entity
use App\Entity\ClassOptional;
use App\Entity\SchoolUnit;
use App\Entity\SchoolYear;
use App\Entity\Student;
use App\Entity\User;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#allows us to restrict methods like get and post
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

#form type definition
use App\Form\ClassOptionalType;
use App\Form\ClassOptionalEnrollType;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ClassOptionalController extends AbstractController
{
    /**
     * @Route("/class/optionals", name="class_optionals")
     */
    public function index()
    {
        $currentSchoolYear = $this->getDoctrine()->getRepository
        (SchoolYear::class)->findCurrentYear();

        $schoolUnits = $currentSchoolYear->getSchoolunits();

        return $this->render('class_optional/class.optionals.html.twig', [
            'current_year'  => $currentSchoolYear,
            'current_units' => $schoolUnits,
        ]);
    }

    /**
     * @Route("/class/optionals/{id}", name="class_optionals_by_year")
     * @Method({"GET"})
     */
    public function index_year($id)
    {
        $schoolYear = $this->getDoctrine()->getRepository
        (SchoolYear::class)->find($id);

        $schoolUnits = $schoolYear->getSchoolunits();

        return $this->render('class_optional/class.optionals.html.twig', [
            'current_year' => $schoolYear,
            'current_units' => $schoolUnits,
        ]);
    }

    /**
     * @Route("/class/optional/new/{unitId}", name="class_optional_new")
     * @Method({"GET", "POST"})
     */
    public function new_optional(Request $request, $unitId)
    {
        $currentUnit = $this->getDoctrine()->getRepository
        (SchoolUnit::class)->find($unitId);

        $profChoice = array();
        $profChoice = $this->getDoctrine()->getRepository
        (User::class)->findAllProfs();

        $optional = new ClassOptional();

        $optional->setSchoolUnit($currentUnit);

        $form = $this->createForm(ClassOptionalType::Class, $optional, array(
          'school_unit' => $currentUnit,
          'professors'  => $profChoice,
        ));

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
           $optional = $form->getData();

           $entityManager = $this->getDoctrine()->getManager();
           $entityManager->persist($optional);
           $entityManager->flush();

           return $this->redirectToRoute('class_optionals_by_year', array('id' => $currentUnit->getSchoolyear()->getId()) );
        }

        return $this->render('class_optional/class.optional.new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/class/optional/edit/{id}", name="class_optional_edit")
     * @Method({"GET", "POST"})
     */
    public function edit_optional(Request $request, $id)
    {
        $optional = $this->getDoctrine()->getRepository
        (ClassOptional::class)->find($id);

        $currentUnit = $optional->getSchoolUnit();

        $profChoice = array();
        $profChoice = $this->getDoctrine()->getRepository
        (User::class)->findAllProfs();

        $form = $this->createForm(ClassOptionalType::Class, $optional, array(
          'school_unit' => $currentUnit,
          'professors'  => $profChoice,
        ));

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
           $optional = $form->getData();

           $entityManager = $this->getDoctrine()->getManager();
           $entityManager->persist($optional);
           $entityManager->flush();

           return $this->redirectToRoute('class_optionals_by_year', array('id' => $currentUnit->getSchoolyear()->getId()) );
        }

        return $this->render('class_optional/class.optional.edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/class/optional/{id}/students", name="class_optional_students")
     * @Method({"GET", "POST"})
     */
     public function optional_students(Request $request, $id)
     {
         $optional = $this->getDoctrine()->getRepository
         (ClassOptional::class)->find($id);

         $students = $optional->getStudents();

         return $this->render('class_optional/class.optional.students.html.twig', [
             'optional' => $optional,
             'students' => $students,
         ]);

     }

     /**
      * @Route("/class/optional/{id}/enroll", name="class_optional_enroll")
      * @Method({"GET", "POST"})
      */
      public function optional_enroll(Request $request, $id)
      {
          $optional = $this->getDoctrine()->getRepository
          (ClassOptional::class)->find($id);

          $currentSchoolYear = $optional->getSchoolUnit()->getSchoolyear();

          $allStudents = $this->getDoctrine()->getRepository
          (Student::class)->findAllYear($currentSchoolYear);

          //getavailablestudents
          $availableStudents = $optional->getSchoolUnit()->getStudents();
          foreach ($allStudents as $student) {
            if ($availableStudents->contains($student)) {
              if ($student->getEnrollment()->getIsActive() == 1) {
                $students[]=$student;
              }
            }
          }

          $form = $this->createForm(ClassOptionalEnrollType::Class, $optional, array(
            'students' => $students,
          ));

          $form->handleRequest($request);

          if($form->isSubmitted() && $form->isValid()) {
            $optional = $form->getData();

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($optional);
            $entityManager->flush();

            if (!$optional->isSyncd()) {
              if ($optional->isModified()) {
                return $this->redirectToRoute('update_optional_attendance', array('optId' => $optional->getId(), 'redirect' => 'optional_enroll') );
              } else {
                $canCreate = false;
                if ($optional->getStudents()->count() > 0) {
                  foreach($optional->getOptionalSchedules() as $schedule) {
                    if ($schedule->getScheduledDateTime() > new \DateTime('now')) {
                      $canCreate = true;
                    }
                  }
                }
                if ($canCreate == true) {
                  return $this->redirectToRoute('generate_optional_attendance', array('optId' => $optional->getId(), 'redirect' => 'optional_enroll') );
                } else {
                  return $this->redirectToRoute('class_optional_students', array('id' => $optional->getId()) );
                }
              }
            } else {
              return $this->redirectToRoute('class_optional_students', array('id' => $optional->getId()) );
            }

            //return $this->redirectToRoute('update_optional_attendance', array('optId' => $optional->getId(), 'redirect' => 'optional_enroll') );
          }

          return $this->render('class_optional/class.optional.enroll.html.twig', [
              'optional' => $optional,
              'students' => $students,
              'form' => $form->createView(),
          ]);

      }
}
