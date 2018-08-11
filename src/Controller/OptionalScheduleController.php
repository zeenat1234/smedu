<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#allows us to restrict methods like get and post
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

#can instantiate the entity
use App\Entity\ClassOptional;
use App\Entity\OptionalSchedule;

#form type definition
use App\Form\OptionalScheduleType;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class OptionalScheduleController extends AbstractController
{
    /**
     * @Route("/optional/schedule/edit/{id}", name="optional_schedule_edit")
     * @Method({"GET" , "POST"})
     */
    public function optional_schedule_edit(Request $request, $id)
    {

        $schedule = $this->getDoctrine()->getRepository
        (OptionalSchedule::class)->find($id);

        $theOptional = $schedule->getClassOptional();

        $form = $this->createForm(OptionalScheduleType::Class, $schedule, array(
            'scheduled_time' => $schedule->getScheduledDateTime()->format('Y-m-d\TH:i'),
        ));

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
           $schedule = $form->getData();

           $entityManager = $this->getDoctrine()->getManager();
           $entityManager->persist($schedule);
           $entityManager->flush();

           return $this->redirectToRoute('optional_schedule', array('id' => $theOptional->getId()) );
        }

        return $this->render('optional_schedule/optional.schedule.edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/optional/schedule/{id}", name="optional_schedule")
     * @Method({"GET"})
     */
    public function optional_schedule($id)
    {

        $theOptional = $this->getDoctrine()->getRepository
        (ClassOptional::class)->find($id);

        $schedules = $theOptional->getOptionalSchedules();

        return $this->render('optional_schedule/optional.schedule.html.twig', [
            'the_optional' => $theOptional,
            'schedules'    => $schedules,
        ]);
    }

    /**
     * @Route("/optional/schedule/{id}/add", name="optional_schedule_add")
     * @Method({"GET", "POST"})
     */
    public function optional_schedule_add(Request $request, $id)
    {

        $theOptional = $this->getDoctrine()->getRepository
        (ClassOptional::class)->find($id);

        $schedule = new OptionalSchedule();
        $schedule->setClassOptional($theOptional);

        $form = $this->createForm(OptionalScheduleType::Class, $schedule, array(
          //'students' => $students,
        ));

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
           $schedule = $form->getData();

           $entityManager = $this->getDoctrine()->getManager();
           $entityManager->persist($schedule);
           $entityManager->flush();

           return $this->redirectToRoute('optional_schedule', array('id' => $theOptional->getId()) );
        }

        return $this->render('optional_schedule/optional.schedule.add.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
