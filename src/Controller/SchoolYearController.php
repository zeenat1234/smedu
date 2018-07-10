<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

#allows us to restrict methods like get and post
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

#can instantiate the entity
use App\Entity\SchoolYear;

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
            'form' => $form->createView(),
        ]);
    }
}
