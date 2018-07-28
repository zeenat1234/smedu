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
}
