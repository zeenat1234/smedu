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
// use Symfony\Component\HttpFoundation\JsonResponse;
//
// use Symfony\Component\Serializer\Serializer;
// use Symfony\Component\Serializer\Encoder\XmlEncoder;
// use Symfony\Component\Serializer\Encoder\JsonEncoder;
// use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

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

    /**
     * @Route("/school/services/{id}", name="school_services_view")
     * @Method({"GET"})
     */
    public function school_services_view($id)
    {
        $schoolYear = $this->getDoctrine()->getRepository
        (SchoolYear::class)->find($id);

        $schoolUnits = $schoolYear->getSchoolunits();

        return $this->render('school_service/school.services.html.twig', [
            'school_units'    => $schoolUnits,
        ]);
    }

    //TODO Consider implementing Add where Unit updates according to year (ie. using jQuery, Ajax calls etc)
    //TODO Try JSON response for dynamic fields

    // /**
    //  * @Route("/school/service/new", name="school_service_new")
    //  * @Method({"GET", "POST"})
    //  */
    //  public function new_service (Request $request)
    //  {
    //       $currentSchoolYear = $this->getDoctrine()->getRepository
    //       (SchoolYear::class)->findCurrentYear();
    //
    //       $jsonResponse = array();
    //
    //
    //      //return new JsonResponse($jsonResponse);
    //
    //      return $this->render('school_service/new.html.twig', [
    //           'json' => json_encode($jsonResponse),
    //      ]);
    //  }
    //  //TODO Use this GET request when using JSON
    //  /**
    //   * @Route("/school/service/getData")
    //   * @Method({"GET"})
    //   */
    //   public function json_get_data(Request $request)
    //   {
    //       $schoolYears = $schoolYear = $this->getDoctrine()->getRepository
    //       (SchoolYear::class)->findCurrentAndNew();
    //
    //       // $jsonResponse = array();
    //
    //       // foreach ($schoolYears as $schoolYear) {
    //       //   $jsonResponse[$schoolYear->getYearlabel()]=$schoolYear->getSchoolunits();
    //       //     foreach ($jsonResponse[$schoolYear->getYearlabel()] as $schoolUnit) {
    //       //     $jsonResponse[$schoolYear->getYearlabel()]=$schoolUnit->getUnitname();
    //       //   }
    //       // }
    //       // $jsonResponse = json_encode($schoolYears);
    //       $encoders = array(new XmlEncoder(), new JsonEncoder());
    //       $normalizers = array(new ObjectNormalizer());
    //
    //       $normalizers[0]->setCircularReferenceHandler(function ($object) {
    //           return $object->getId();
    //       });
    //
    //       $serializer = new Serializer($normalizers, $encoders);
    //
    //       $jsonResponse = $serializer->serialize($schoolYears, 'json');
    //
    //       //return new JsonResponse($jsonResponse);
    //       return new Response($jsonResponse);
    //   }


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

            return $this->redirectToRoute('school_services_view', array('id'=>$schoolYear->getId()));
         }

         return $this->render('school_service/add.to.year.html.twig', [
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

           return $this->redirectToRoute('school_services_view', array('id'=>$schoolService->getSchoolyear()->getId()));
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

           return $this->redirectToRoute('school_services_view', array('id'=>$schoolService->getSchoolyear()->getId()));
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

    }

}
