<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

#can instantiate the entity
use App\Entity\TransportRoute;
use App\Entity\TransportTrip;
use App\Entity\SchoolYear;
use App\Entity\Student;

#form type definition
use App\Form\RouteCreationType;
use App\Form\TransportRouteType;
use App\Form\TransportTripType;

use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

#allows us to restrict methods like get and post
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TransportController extends AbstractController
{
    /**
     * @Route("/transport/routes/{yearId?0}", name="routes")
     */
    public function routes(Request $request, $yearId)
    {
        if ($yearId > 0) {
          $currentSchoolYear = $this->getDoctrine()->getRepository
          (SchoolYear::class)->find($yearId);
        } else {
          $currentSchoolYear = $this->getDoctrine()->getRepository
          (SchoolYear::class)->findCurrentYear();
        }

        $currentUnits = $currentSchoolYear->getSchoolunits();

        $allStudents = $this->getDoctrine()->getRepository
        (Student::class)->findAllYear($currentSchoolYear);

        $existingRoutes = array();
        $enrollableStudents = array();

        foreach ($allStudents as $student) {
          if ($student->getTransportRoute()) {
            $existingRoutes[] = $student->getTransportRoute();
          } else {
            if ($student->getEnrollment()->getIsActive()) {
              $enrollableStudents[] = $student;
            }
          }
        }

        $form = $this->createForm(RouteCreationType::Class, $data = null, array(
          'students' => $enrollableStudents,
        ));

        $view = $form->createView();

        $forms = array();
        $views = array();
        $i = 0;
        foreach ($existingRoutes as $route) {
          $forms[$i] = $this->createForm(TransportRouteType::Class, $route, array(
            //options?
          ));
          $views[$i] = $forms[$i]->createView();
          $i = $i + 1;
        }

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
          $data = $form->getData();

          $selectedStudents = $data['students'];
          $price = $data['price'];
          $pricePerKm = $data['pricePerKm'];

          if (sizeof($selectedStudents) == 0) {
            $this->get('session')->getFlashBag()->add(
                'notice',
                'ATENȚIE: Nu a fost selectat niciun elev!'
            );
            return $this->redirectToRoute('routes');
          }

          $summary = "Înscrieri pentru transport adăugate cu succes!\n\nElevi selectați: ".sizeof($selectedStudents)."\n";

          foreach ($selectedStudents as $student) {
            $newRoute = new TransportRoute();
            $newRoute->setStudent($student);
            $newRoute->setPrice($price);
            $newRoute->setPricePerKm($pricePerKm);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($newRoute);
            $entityManager->flush();

            $summary = $summary."--> ".$student->getUser()->getRoName()."\n";
          }

          $this->get('session')->getFlashBag()->add(
              'hurray',
              $summary
          );

          return $this->redirectToRoute('routes');
        }

        foreach ($forms as $oneForm) {
          $oneForm->handleRequest($request);
        }

        foreach ($forms as $oneForm) {
          if ($oneForm->isSubmitted()) {
            if ($oneForm->isValid()) {
              $route = $oneForm->getData();
              $entityManager = $this->getDoctrine()->getManager();
              $entityManager->persist($route);
              $entityManager->flush();

              $this->get('session')->getFlashBag()->add(
                'hurray',
                "Opțiunile de transport pentru ".$route->getStudent()->getUser()->getRoName()." au fost actualizate!"
              );
              return $this->redirectToRoute('routes');
            } else {
              $this->get('session')->getFlashBag()->add(
                'notice',
                'ATENȚIE: A apărut o problemă la actualizarea datelor!'
              );
              return $this->redirectToRoute('routes');
            }
          }
        }

        return $this->render('transport/routes.html.twig', [
          'current_year' => $currentSchoolYear,
          'current_units' => $currentUnits,
          'sorted_students' => $allStudents,
          'routes' => $existingRoutes,
          'form' => $view,
          'forms' => $views,
        ]);
    }

    /**
     * @Route("/transport/trips", name="trips")
     * @Method({"GET" , "POST"})
     */
     public function trips()
     {
       $currentSchoolYear = $this->getDoctrine()->getRepository
       (SchoolYear::class)->findCurrentYear();

        return $this->render('transport/trips.html.twig', [
          'schoolyear' => $currentSchoolYear,
        ]);
     }

    /**
     * @Route("/transport/trips/{date}", name="trips_date")
     */
    public function trips_date(Request $request, $date)
    {

      $currentSchoolYear = $this->getDoctrine()->getRepository
      (SchoolYear::class)->findCurrentYear();

      $allStudents = $this->getDoctrine()->getRepository
      (Student::class)->findAllYear($currentSchoolYear);

      $all_trips = array();

      foreach ($allStudents as $student) {
        // The following if statement will show students which are either on the Transport Register or
        // the students who have been on the Transport Register on that day.
        if ($student->getTransportRoute() || $student->getTransportTripByDay($date)) {
          if ($student->getTransportTripByDay($date)) {
            $trip = $student->getTransportTripByDay($date);
          } else {
            $trip = new TransportTrip();
            $trip->setStudent($student);
            $trip->setDate(new \DateTime($date));
            $trip->setDistance1($student->getTransportRoute()->getDistance());
            $trip->setDistance2($student->getTransportRoute()->getDistance());
            $trip->setPrice($student->getTransportRoute()->getPrice());
            $trip->setPricePerKm($student->getTransportRoute()->getPricePerKm());
          }
          $all_trips[] = $trip;
        }
      }

      if (sizeof($all_trips) > 0) {
        $formFactory = $this->get('form.factory');
        $form = $formFactory->createNamedBuilder('trips_form', FormType::class, array('trips' => $all_trips));

        $form->add('trips', CollectionType::class, array(
          'label' => false,
          'entry_type' => TransportTripType::class,
        ));
        // $form->add('update', SubmitType::class, array(
        //   'label' => 'Actualizează',
        // ));

        $the_form = $form->getForm();
        $the_view = $form->getForm()->createView();

      } else {
        return $this->redirectToRoute('routes');
      }

      if ($request->isMethod('POST')) {

        $the_form->handleRequest($request);

        if ($the_form->isSubmitted()) {

          $data = $form->getData();

          // $data['trips'] contains an array of AppBundle\Entity\TransportTrip
          // use it to persist the categories in a foreach loop
          foreach ($data['trips'] as $trip) {

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($trip);
            $entityManager->flush();
          }

          $formatter = new \IntlDateFormatter(\Locale::getDefault(), \IntlDateFormatter::NONE, \IntlDateFormatter::NONE);
          $formatter->setPattern('dd MMMM, yyyy');

          $this->get('session')->getFlashBag()->add(
              'notice',
              'Informația pentru '.$formatter->format(new \DateTime($date)).' a fost actualizată cu succes!'
          );

          return $this->redirectToRoute('trips_date', array('date'=>$date));
        }

      }

      return $this->render('transport/trip.html.twig', [
       'date' => $date,
       'form' => $the_view,
      ]);
    }

    /**
     * @Route("/transport/remove_route/{id}", name="remove_route")
     */
    public function remove_route(Request $request, $id)
    {
      $route = $this->getDoctrine()->getRepository
      (TransportRoute::class)->find($id);

      //if route has transports, throw error
      //else

      $entityManager = $this->getDoctrine()->getManager();
      $entityManager->remove($route);
      $entityManager->flush();

      return $this->redirectToRoute('routes');
    }

}
