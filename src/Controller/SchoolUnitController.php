<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Bundle\FrameworkBundle\Console\Application;

#allows us to restrict methods like get and post
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

#can instantiate the entity
use App\Entity\SchoolUnit;
use App\Entity\SchoolYear;

#form type definition
use App\Form\SchoolUnitType;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Intl\Intl;

class SchoolUnitController extends AbstractController
{
    /**
     * @Route("/school/units", name="school_units")
     * @Method({"GET"})
     */
    public function school_units()
    {
        $currentSchoolYear = $this->getDoctrine()->getRepository
        (SchoolYear::class)->findCurrentYear();

        $schoolunits = $this->getDoctrine()->getRepository
        (SchoolUnit::class)->findCurrentUnits(
          $currentSchoolYear->getStartDate(),
          $currentSchoolYear->getEndDate()
        );

        return $this->render('school_unit/school.units.html.twig', [
            'schoolunits' => $schoolunits,
            'school_year' => $currentSchoolYear->getYearname(),
        ]);
    }

    /**
     * @Route("/school/unit/new", name="school_unit_new")
     * @Method({"GET", "POST"})
     */
    public function school_unit_new(Request $request)
    {
        $availableSchoolYears = $this->getDoctrine()->getRepository
        (SchoolYear::class)->findAll();

        $schoolunit = new SchoolUnit();

        $form = $this->createForm(SchoolUnitType::Class, $schoolunit, array(
          'schoolyears' => $availableSchoolYears,
        ));

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
          $schoolunit = $form->getData();

          $entityManager = $this->getDoctrine()->getManager();
          $entityManager->persist($schoolunit);
          $entityManager->flush();

          return $this->redirectToRoute('school_units');
        }

        return $this->render('school_unit/school.unit.new.html.twig', [
             'form' => $form->createView()
        ]);
    }

   /**
    * @Route("/school/unit/edit/{id}", name="school_unit_edit")
    * @Method({"GET", "POST"})
    */
    public function school_unit_edit(Request $request, $id)
    {
        $availableSchoolYears = $this->getDoctrine()->getRepository
        (SchoolYear::class)->findAll();

        $schoolunit = new SchoolUnit();

        $schoolunit = $this->getDoctrine()->getRepository
        (SchoolUnit::class)->find($id);

        $form = $this->createForm(SchoolUnitType::Class, $schoolunit, array(
          'schoolyears' => $availableSchoolYears,
        ));

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

          $entityManager = $this->getDoctrine()->getManager();
          $entityManager->flush();

          return $this->redirectToRoute('school_units');
        }

        return $this->render('school_unit/school.unit.edit.html.twig', array(
          'form' => $form->createView()
        ));
    }

    //TODO Find out how to fix 500 error which occurs in the browser console
    //when a delete statement is executed
    /**
     * @Route("/school/unit/delete/{id}", name="school_unit_delete")
     * @Method({"DELETE"})
     */
    public function school_unit_delete(Request $request, $id)
    {
      $schoolunit = $this->getDoctrine()->getRepository
      (SchoolUnit::class)->find($id);


      $entityManager = $this->getDoctrine()->getManager();

      $entityManager->remove($schoolunit);
      $entityManager->flush();

      //console.log('A mers!');
      $response = new Response();
      $response->send();

      //return $this->redirectToRoute('school_units');
    }

}
