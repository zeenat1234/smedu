<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#allows us to restrict methods like get and post
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

#can instantiate the entity
use App\Entity\Enrollment;
use App\Entity\SchoolService;
use App\Entity\SchoolUnit;
use App\Entity\SchoolYear;
use App\Entity\User;
use App\Entity\Student;

#form type definition
use App\Form\EnrollmentType;

#this is used for forms
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class EnrollmentController extends AbstractController
{
    /**
     * @Route("/enrollment", name="enrollment")
     * @Method({"GET"})
     */
    public function enrollment_home()
    {
        $currentSchoolYear = $this->getDoctrine()->getRepository
        (SchoolYear::class)->findCurrentYear();

        $currentUnits = $currentSchoolYear->getSchoolunits();

        $currentEnrollments = $this->getDoctrine()->getRepository
        (Enrollment::class)->findLatest(10, $currentSchoolYear->getId());

        //TODO: Find unique pupil entries, rather than count all enrollment objects - do this in repo as a new query
        $totalEnrollments = sizeof($this->getDoctrine()->getRepository
        (Enrollment::class)->findAllYear($currentSchoolYear->getId()));

        return $this->render('enrollment/enrollment.html.twig', [
            'current_year' => $currentSchoolYear,
            'current_units' => $currentUnits,
            'enrollments' => $currentEnrollments,
            'total_enrollments' => $totalEnrollments,
        ]);
    }

    /**
     * @Route("/enrollment/all", name="all_enrollments")
     * @Method({"GET"})
     */
    public function enrollment_all_home()
    {
        $currentSchoolYear = $this->getDoctrine()->getRepository
        (SchoolYear::class)->findCurrentYear();

        $currentUnits = $currentSchoolYear->getSchoolunits();

        $allEnrollments = $this->getDoctrine()->getRepository
        (Enrollment::class)->findAllYear($currentSchoolYear->getId());

        return $this->render('enrollment/enrollment.all.html.twig', [
            'current_year' => $currentSchoolYear,
            'current_units' => $currentUnits,
            'enrollments' => $allEnrollments,
        ]);
    }

    /**
     * @Route("/enrollment/{yearId}", name="enrollment_year")
     * @Method({"GET"})
     */
    public function enrollment_year($yearId)
    {
        $schoolYear = $this->getDoctrine()->getRepository
        (SchoolYear::class)->find($yearId);

        $currentUnits = $schoolYear->getSchoolunits();

        $currentEnrollments = $this->getDoctrine()->getRepository
        (Enrollment::class)->findLatest(10, $schoolYear->getId());

        //TODO: Find unique pupil entries, rather than count all enrollment objects - do this in repo as a new query
        $totalEnrollments = sizeof($this->getDoctrine()->getRepository
        (Enrollment::class)->findAllYear($schoolYear->getId()));

        return $this->render('enrollment/enrollment.html.twig', [
            'current_year' => $schoolYear,
            'current_units' => $currentUnits,
            'enrollments' => $currentEnrollments,
            'total_enrollments' => $totalEnrollments,
        ]);
    }

    /**
     * @Route("/enrollment/all/{yearId}", name="all_enrollments_year")
     * @Method({"GET"})
     */
    public function enrollment_all_year($yearId)
    {
        $schoolYear = $this->getDoctrine()->getRepository
        (SchoolYear::class)->find($yearId);

        $currentUnits = $schoolYear->getSchoolunits();

        $allEnrollments = $this->getDoctrine()->getRepository
        (Enrollment::class)->findAllYear($schoolYear->getId());

        return $this->render('enrollment/enrollment.all.html.twig', [
            'current_year' => $schoolYear,
            'current_units' => $currentUnits,
            'enrollments' => $allEnrollments,
        ]);
    }

    //TODO: Deprecated since EnrollWizard - currently removed from navigation - still active in controller
    /**
     * @Route("/enrollment/new/{unitId}", name="new_enrollment_in_unit")
     * @Method({"GET", "POST"})
     */
    public function add_to_unit(Request $request, $unitId)
    {
        $currentUnit = $this->getDoctrine()->getRepository
        (SchoolUnit::class)->find($unitId);

        $enrollment = new Enrollment();

        $enrollment->setEnrollDate(new \DateTime('now'));
        $enrollment->setIsActive(true);
        $enrollment->setSchoolYear($currentUnit->getSchoolYear());

        $parents = $this->getDoctrine()->getRepository
        (User::class)->findAllParents();

        $children = $this->getDoctrine()->getRepository
        (User::class)->findAllChildren();

        $form = $this->createForm(EnrollmentType::Class, $enrollment, array(
         'school_unit' => $currentUnit,
         'parents'     => $parents,
         'children'    => $children
        ));

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

           $enrollment = $form->getData();

           $newStudent = new Student();
           $newStudent->setUser($enrollment->getIdChild());
           $newStudent->setSchoolUnit($currentUnit);
           $newStudent->setEnrollment($enrollment);

           $entityManager = $this->getDoctrine()->getManager();
           $entityManager->persist($newStudent);
           $entityManager->flush();

           $enrollment->setStudent($newStudent);

           $entityManager = $this->getDoctrine()->getManager();
           $entityManager->persist($enrollment);
           $entityManager->flush();



           return $this->redirectToRoute('enrollment_year', array('yearId'=>$enrollment->getSchoolyear()->getId()));
        }

        return $this->render('enrollment/enrollment.to.unit.html.twig', [
            'current_unit' => $currentUnit,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/enrollment/edit/{enrollId}.{studId?0}.{redirect?'all_enrollments_year'}", name="edit_enrollment")
     * @Method({"GET", "POST"})
     */
    public function edit(Request $request, $enrollId, $studId, $redirect)
    {
        $enrollment = $this->getDoctrine()->getRepository
        (Enrollment::class)->find($enrollId);

        $currentUnit = $enrollment->getIdUnit();

        $parent = $this->getDoctrine()->getRepository
        (User::class)->find($enrollment->getIdParent());

        $child = $this->getDoctrine()->getRepository
        (User::class)->find($enrollment->getIdChild());

        $form = $this->createForm(EnrollmentType::Class, $enrollment, array(
         'school_unit' => $currentUnit,
         'parents'     => array($parent),
         'children'    => array($child)
        ));

        $form
          ->add('isActive', CheckboxType::class, array(
            'label'    => 'Înscriere Activă',
            'required' => false,
            'attr' => array('class' => 'form-check form-check-inline'),
          ));

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
          $enrollment = $form->getData();

          $entityManager = $this->getDoctrine()->getManager();
          $entityManager->flush();
          // After a child is deactivated, we also remove it from any existing optional class
          if ($enrollment->getIsActive() == 0) {
             foreach ($enrollment->getStudent()->getClassOptionals() as $optional) {
                $optional->removeStudent($enrollment->getStudent());

                $entityManager->persist($optional);
                $entityManager->flush();
             }
          }
          if ($redirect == 'all_enrollments_year') {
            return $this->redirectToRoute('all_enrollments_year', array('yearId'=>$enrollment->getSchoolyear()->getId()));
          } else if ($redirect == 'class_group') {
            $student = $this->getDoctrine()->getRepository
            (Student::class)->find($studId);
            return $this->redirectToRoute('class_group_view', ['groupId' => $student->getClassGroup()->getId()]);
          } 
        }


        return $this->render('enrollment/enrollment.edit.html.twig', [
            'current_unit' => $currentUnit,
            'form' => $form->createView(),
        ]);
    }

}
