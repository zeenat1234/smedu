<?php

namespace App\Controller;

#can instantiate the entity
use App\Entity\ClassOptional;
use App\Entity\SchoolUnit;
use App\Entity\SchoolYear;
use App\Entity\User;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#allows us to restrict methods like get and post
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

#form type definition
use App\Form\ClassOptionalType;

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

        $optional = new ClassOptional();

        $optional->setSchoolUnit($currentUnit);

        $form = $this->createForm(ClassOptionalType::Class, $optional, array(
          'school_unit' => $currentUnit,
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
}
