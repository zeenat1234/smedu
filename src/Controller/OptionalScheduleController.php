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
#this is used for forms
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

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
        $form->add('addOne', SubmitType::class, [
            'label' => 'Adaugă 1x',
            'attr' => ['class' => 'btn btn-success mt-3'],
        ]);
        $form->add('addFull', SubmitType::class, [
            'label' => 'Adaugă Toată Durata',
            'attr' => ['class' => 'btn btn-success mt-3'],
        ]);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            if ($form->get('addOne')->isClicked()) {
                $schedule = $form->getData();

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($schedule);
                $entityManager->flush();
            }
            if ($form->get('addFull')->isClicked()) {
                $schedule = $form->getData();

                $unitEndDate = $theOptional->getSchoolUnit()->getEndDate();

                while ($schedule->getScheduledDateTime() <= $unitEndDate) {

                    $entityManager = $this->getDoctrine()->getManager();
                    $entityManager->persist($schedule);
                    $entityManager->flush();

                    $tempSchedule = new OptionalSchedule();
                    $tempSchedule->setClassOptional($schedule->getClassOptional());
                    $tempSchedule->setScheduledDateTime($schedule->getScheduledDateTime()->modify('+1 week'));

                    $schedule = $tempSchedule;

                }
            }
            return $this->redirectToRoute('optional_schedule', array('id' => $theOptional->getId()) );
        }

        return $this->render('optional_schedule/optional.schedule.add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/optional/schedule/{id}/remove", name="optional_schedule_remove")
     * @Method({"GET","DELETE"})
     */
     public function optional_schedule_remove(Request $request, $id)
     {

        $schedule = $this->getDoctrine()->getRepository
        (OptionalSchedule::class)->find($id);

        if ($schedule->getScheduledDateTime() > (new \DateTime('now'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($schedule);
            $entityManager->flush();
        } else {
            //do nothing
        }

        return $this->redirectToRoute('optional_schedule', array('id' => $schedule->getClassOptional()->getId()) );

     }
}
