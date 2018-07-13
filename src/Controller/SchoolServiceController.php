<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#allows us to restrict methods like get and post
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

#can instantiate the entity
use App\Entity\SchoolService;
use App\Entity\SchoolUnit;
use App\Entity\SchoolYear;

#form type definition
use App\Form\SchoolServiceType;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SchoolServiceController extends AbstractController
{
    /**
     * @Route("/school/services", name="school_services")
     * @Method({"GET"})
     */
    public function school_services()
    {
        $currentSchoolYear = $this->getDoctrine()->getRepository
        (SchoolYear::class)->findCurrentYear();

        $schoolUnits = $currentSchoolYear->getSchoolunits();

        return $this->render('school_service/school.services.html.twig', [
            'school_units'    => $schoolUnits,
        ]);
    }

    //TODO Consider implementing Add where Unit updates according to year (ie. using jQuery, Ajax calls etc)

    /**
     * @Route("/school/service/add", name="school_service_add_to_currentYear")
     * @Method({"GET", "POST"})
     */
     public function add_service(Request $request)
     {
         $schoolYear = $this->getDoctrine()->getRepository
         (SchoolYear::class)->findCurrentYear();

         $schoolUnits = $schoolYear->getSchoolunits();

         $schoolService = new SchoolService();

         $form = $this->createForm(SchoolServiceType::Class, $schoolService, array(
          'school_units'    => $schoolUnits,
          'school_year'     => $schoolYear,
         ));

         $form->handleRequest($request);

         if($form->isSubmitted() && $form->isValid()) {
            $schoolService = $form->getData();

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($schoolService);
            $entityManager->flush();

            return $this->redirectToRoute('school_services');
         }

         return $this->render('school_service/new.html.twig', [
              'form' => $form->createView()
         ]);

     }

    /**
     * @Route("/school/service/add/{id}", name="school_service_add_to_unit")
     * @Method({"GET", "POST"})
     */
    public function add_service_to_unit(Request $request, $id)
    {
        $currentSchoolUnit = $this->getDoctrine()->getRepository
        (SchoolUnit::class)->find($id);

        $schoolService = new SchoolService();


        $form = $this->createForm(SchoolServiceType::Class, $schoolService, array(
         'school_unit'    => $currentSchoolUnit,
        ));

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
           $schoolService = $form->getData();

           $entityManager = $this->getDoctrine()->getManager();
           $entityManager->persist($schoolService);
           $entityManager->flush();

           return $this->redirectToRoute('school_services');
        }

        return $this->render('school_service/add.to.unit.html.twig', [
          'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/school/service/edit/{id}", name="school_service_edit")
     * @Method({"GET", "POST"})
     */
    public function school_service_edit(Request $request, $id)
    {
        $schoolService = new SchoolService();

        $schoolService = $this->getDoctrine()->getRepository
        (SchoolService::class)->find($id);

        $form = $this->createForm(SchoolServiceType::Class, $schoolService, array(
         'school_unit'    => $schoolService->getSchoolunit(),
         'school_year'    => $schoolService->getSchoolyear(),
        ));

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
          $entityManager = $this->getDoctrine()->getManager();
          $entityManager->flush();

           return $this->redirectToRoute('school_services');
        }

        return $this->render('school_service/edit.unit.html.twig', [
          'form' => $form->createView()
        ]);
    }

    //TODO Find out how to fix 500 error which occurs in the browser console
    //when a delete statement is executed
    /**
     * @Route("/school/service/delete/{id}", name="school_service_delete")
     * @Method({"DELETE"})
     */
    public function school_service_delete(Request $request, $id)
    {
      $schoolservice = $this->getDoctrine()->getRepository
      (SchoolService::class)->find($id);


      $entityManager = $this->getDoctrine()->getManager();

      $entityManager->remove($schoolservice);
      $entityManager->flush();

      //console.log('A mers!');
      $response = new Response();
      $response->send();

      //return $this->redirectToRoute('school_units');
    }

}
