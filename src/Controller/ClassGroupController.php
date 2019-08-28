<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#allows us to restrict methods like get and post
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

#can instantiate the entity
use App\Entity\ClassGroup;
use App\Entity\SchoolUnit;
use App\Entity\SchoolYear;
use App\Entity\User;

#form type definition
use App\Form\ClassGroupType;
use App\Form\ClassGroupEnrollType;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ClassGroupController extends AbstractController
{
    /**
     * @Route("/class/groups", name="class_groups")
     * @Method({"GET"})
     */
    public function index()
    {
        $currentSchoolYear = $this->getDoctrine()->getRepository
        (SchoolYear::class)->findCurrentYear();

        $schoolUnits = $currentSchoolYear->getSchoolunits();

        return $this->render('class_group/class.groups.html.twig', [
            'school_year' => $currentSchoolYear,
            'school_units' => $schoolUnits,
        ]);
    }


    /**
     * @Route("/class/groups/new/{unitId}", name="class_groups_new")
     * @Method({"GET", "POST"})
     */
    public function new_classgroup(Request $request, $unitId)
    {
        $currentUnit = $this->getDoctrine()->getRepository
        (SchoolUnit::class)->find($unitId);

        $profChoice = array();
        $profChoice = $this->getDoctrine()->getRepository
        (User::class)->findAllProfs();

        $classGroup = new ClassGroup();

        $classGroup->setSchoolUnit($currentUnit);

        $form = $this->createForm(ClassGroupType::Class, $classGroup, array(
          'school_unit' => $currentUnit,
          'prof_choice' => $profChoice,
        ));

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
           $classGroup = $form->getData();

           $entityManager = $this->getDoctrine()->getManager();
           $entityManager->persist($classGroup);
           $entityManager->flush();

           return $this->redirectToRoute('class_groups_by_year', array('id' => $currentUnit->getSchoolyear()->getId()) );
        }

        return $this->render('class_group/add.to.unit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/class/groups/edit/{id}", name="class_group_edit")
     * @Method({"GET", "POST"})
     */
    public function class_group_edit(Request $request, $id)
    {
        $classGroup = $this->getDoctrine()->getRepository
        (ClassGroup::class)->find($id);

        $currentUnit = $classGroup->getSchoolUnit();

        $profChoice = array();
        $profChoice = $this->getDoctrine()->getRepository
        (User::class)->findAllProfs();

        $form = $this->createForm(ClassGroupType::Class, $classGroup, array(
          'school_unit' => $currentUnit,
          'prof_choice' => $profChoice,
        ));

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
           $classGroup = $form->getData();

           $entityManager = $this->getDoctrine()->getManager();
           $entityManager->flush();

           return $this->redirectToRoute('class_groups_by_year', array('id' => $currentUnit->getSchoolyear()->getId()) );
        }

        return $this->render('class_group/class.group.edit.html.twig', [
            'form' => $form->createView(),
        ]);

    }

    /**
     * @Route("/class/groups/{id}", name="class_groups_by_year")
     * @Method({"GET"})
     */
    public function index_year($id)
    {
      $schoolYear = $this->getDoctrine()->getRepository
      (SchoolYear::class)->find($id);

      $schoolUnits = $schoolYear->getSchoolunits();

      return $this->render('class_group/class.groups.html.twig', [
        'school_year' => $schoolYear,
        'school_units' => $schoolUnits,
      ]);
    }

    /**
     * @Route("/class/group/{groupId}", name="class_group_view")
     * @Method({"GET"})
     */
    public function class_group_view($groupId)
    {
        $classGroup = $this->getDoctrine()->getRepository
        (ClassGroup::class)->find($groupId);

        $students = $classGroup->getStudents();

        return $this->render('class_group/class.group.view.html.twig', [
            'class_group' => $classGroup,
            'students' => $students,
        ]);
    }

    /**
     * @Route("/class/group/{groupId}.{redirect?'class_groups'}/enroll", name="class_group_enroll")
     * @Method({"GET", "POST"})
     */
    public function class_group_enroll(Request $request, $groupId, $redirect)
    {
      $classgroup = $this->getDoctrine()->getRepository
      (ClassGroup::class)->find($groupId);

      $students = array();

      //getavailablestudents
      $availableStudents = $classgroup->getSchoolUnit()->getStudents();
      foreach ($availableStudents as $student) {
          if ($student->getEnrollment()->getIsActive() == 1
            && ($student->getClassGroup() == $classgroup || empty($student->getClassGroup()))
          ) {
            $students[]=$student;
          }
      }

      $view = NULL;

      if (count($students) > 0) {
        $form = $this->createForm(ClassGroupEnrollType::Class, $classgroup, array(
          'students' => $students,
        ));

        $view = $form->createView();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
          $classgroup = $form->getData();

          foreach ($students as $student) {
            if ($classgroup->getStudents()->contains($student)) {
              $student->setClassGroup($classgroup);
            } else {
              $student->setClassGroup(NULL);
            }
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();
          }

          if ($redirect == 'class_groups') {
            return $this->redirectToRoute('class_groups');
          } else if ($redirect == 'group') {
            return $this->redirectToRoute('class_group_view', ['groupId' => $groupId]);
          } else {
            return $this->redirectToRoute('class_groups');
          }
        }
      }

      return $this->render('class_group/class.group.enroll.html.twig', [
          'classgroup' => $classgroup,
          'students' => $students,
          'form' => $view,
      ]);
    }
}
