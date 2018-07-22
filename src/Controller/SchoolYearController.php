<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

#allows us to restrict methods like get and post
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

#can instantiate the entity
use App\Entity\SchoolYear;
use App\Entity\SchoolUnit;
use App\Entity\SchoolService;

#form type definition
use App\Form\SchoolYearType;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SchoolYearController extends Controller
{
    /**
     * @Route("/school/year", name="school_year")
     * @Method({"GET", "POST"})
     */
    public function schoolYear(Request $request)
    {
        $currentSchoolYear = $this->getDoctrine()->getRepository
        (SchoolYear::class)->findCurrentYear();

        $nextSchoolYears = $this->getDoctrine()->getRepository
        (SchoolYear::class)->findNextYears();

        $prevSchoolYears = $this->getDoctrine()->getRepository
        (SchoolYear::class)->findPreviousYears();

        $form = $this->CreateForm(SchoolYearType::class, $currentSchoolYear);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
          $currentSchoolYear = $form->getData();

          $entityManager = $this->getDoctrine()->getManager();
          $entityManager->persist($currentSchoolYear);
          $entityManager->flush();
        }

        return $this->render('school_year/school.year.settings.html.twig', [
            'school_year' => $currentSchoolYear->getYearname(),
            'next_years' => $nextSchoolYears,
            'prev_years' => $prevSchoolYears,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/school/year/new", name="school_year_new")
     * @Method({"GET", "POST"})
     */
     public function newSchoolYear(Request $request)
     {
        $schoolYear = new SchoolYear();

        $form = $this->CreateForm(SchoolYearType::class, $schoolYear);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
          $schoolYear = $form->getData();

          $schoolYearName = $schoolYear->getStartDate()->format("y")."/".$schoolYear->getEndDate()->format("y");
          $schoolYear->setYearname($schoolYearName);

          $entityManager = $this->getDoctrine()->getManager();
          $entityManager->persist($schoolYear);
          $entityManager->flush();

          return $this->redirectToRoute('school_year');
        }

        return $this->render('school_year/school.year.new.html.twig', [
            'form' => $form->createView(),
        ]);
     }

     //TODO Find out how to fix 500 error which occurs in the browser console
     //when a delete statement is executed
     /**
      * @Route("/school/year/delete/{id}", name="school_year_delete")
      * @Method({"DELETE"})
      */

     public function deleteYear(Request $request, $id)
     {
       $schoolYear = $this->getDoctrine()->getRepository
       (SchoolYear::class)->find($id);


       $entityManager = $this->getDoctrine()->getManager();

       $entityManager->remove($schoolYear);
       $entityManager->flush();

       //console.log('A mers!');
       $response = new Response();
       $response->send();

       //return $this->redirectToRoute('users');
     }

    /**
     * @Route("/school/year/{id}", name="school_year_view")
     * @Method({"GET", "POST"})
     */
     public function viewSchoolYear(Request $request, $id)
     {
        $schoolYear = $currentSchoolYear = $this->getDoctrine()->getRepository
        (SchoolYear::class)->find($id);

        $form = $this->CreateForm(SchoolYearType::class, $schoolYear);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
          $schoolYear = $form->getData();

          $entityManager = $this->getDoctrine()->getManager();
          $entityManager->persist($schoolYear);
          $entityManager->flush();
        }

        return $this->render('school_year/school.year.view.html.twig', [
            'school_year' => $schoolYear,
            'form' => $form->createView(),
        ]);
     }
}
