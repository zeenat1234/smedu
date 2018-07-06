<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

#allows us to restrict methods like get and post
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

#can instantiate the entity
use App\Entity\SchoolYear;

#this is used for forms
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

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

      $form = $this->CreateFormBuilder($currentSchoolYear)
        ->add('yearLabel', TextType::class, array(
          'attr' => array('class' => 'col-12 form-control'),
          'required' => true,
        ))
        ->add('startDate', DateType::class, array(
          'input' => 'datetime',
          'widget' => 'single_text',
          'attr' => array('type' => 'datetime', 'class' => 'col-6 form-control'),
        ))
        ->add('endDate', DateType::class, array(
          'input' => 'datetime',
          'widget' => 'single_text',
          'attr' => array('type' => 'datetime', 'class' => 'col-6 form-control'),
        ))
        ->add('submit', SubmitType::class, array(
          'label' => 'Actualizează',
          'attr' => array('class' => 'btn btn-success mt-3')
        ))
        ->getForm();

        return $this->render('school_year/school.year.settings.html.twig', [
            'school_year' => $currentSchoolYear->getYearname(),
            'form' => $form->createView(),
        ]);
    }
}
