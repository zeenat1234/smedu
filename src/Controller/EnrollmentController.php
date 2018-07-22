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

#form type definition
use App\Form\EnrollmentType;

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
        (Enrollment::class)->findLatest(10);

        //TODO: Find unique pupil entries, rather than count all enrollment objects - do this in repo as a new query
        $totalEnrollments = sizeof($this->getDoctrine()->getRepository
        (Enrollment::class)->findAll());

        return $this->render('enrollment/enrollment.html.twig', [
            'current_year' => $currentSchoolYear,
            'current_units' => $currentUnits,
            'enrollments' => $currentEnrollments,
            'total_enrollments' => $totalEnrollments,
        ]);
    }

    /**
     * @Route("/enrollment/new/{unitId}", name="new_enrollment_in_unit")
     * @Method({"GET", "POST"})
     */
    public function add_to_unit(Request $request, $unitId)
    {
        $currentUnit = $this->getDoctrine()->getRepository
        (SchoolUnit::class)->find($unitId);

        //$currentYear = $currentUnit->getSchoolyear();

        $enrollment = new Enrollment();

        $enrollment->setEnrollDate(new \DateTime('now'));
        $enrollment->setIsActive(true);

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

           $entityManager = $this->getDoctrine()->getManager();
           $entityManager->persist($enrollment);
           $entityManager->flush();

           return $this->redirectToRoute('enrollment');
        }


        return $this->render('enrollment/enrollment.to.unit.html.twig', [
            //'current_year' => $currentYear,
            'current_unit' => $currentUnit,
            'form' => $form->createView(),
        ]);
    }

}
