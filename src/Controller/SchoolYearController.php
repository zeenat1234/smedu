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
        $currentSchoolYear = new SchoolYear();

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
            'school_year' => $schoolYear->getYearname(),
            'form' => $form->createView(),
        ]);
     }
}
