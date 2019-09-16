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
use App\Entity\ClassGroup;
use App\Entity\ClassOptional;
use App\Entity\Student;
use App\Entity\Enrollment;
use App\Entity\TransportRoute;

#form type definition
use App\Form\SchoolYearType;
use App\Form\SYStep1Type;
use App\Form\SYStep2Type;
use App\Form\SYStep3Type;
use App\Form\SYStep4Type;
use App\Form\SYStep5Type;
use App\Form\SYStep6Type;
use App\Form\SYStep7Type;
use App\Form\SYStep8Type;

use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

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

          $schoolYearName = $schoolYear->getStartDate()->format("Y")."/".$schoolYear->getEndDate()->format("Y");
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

     /**
      * @Route("/school/prev_year/{id}", name="prev_school_year")
      * @Method({"GET", "POST"})
      */
      public function prevSchoolYear(Request $request, $id)
      {
        $schoolYear = $this->getDoctrine()->getRepository
        (SchoolYear::class)->find($id);

        return $this->render('school_year/prev.school.year.html.twig', [
            'school_year' => $schoolYear,
        ]);
      }

     /**
      * @Route("/school/year/setup/{id}", name="school_year_setup")
      * @Method({"GET", "POST"})
      */
      public function setupSchoolYear(Request $request, $id)
      {
          $schoolYear = $this->getDoctrine()->getRepository
          (SchoolYear::class)->find($id);

          $prevSchoolYear = $this->getDoctrine()->getRepository
          (SchoolYear::class)->findCurrentYear();

          if ($schoolYear->getIsSetupComplete() == false) {
            if ($schoolYear->getIsSetup6()) {
              $schoolYear->setIsSetupComplete(1);

              $entityManager = $this->getDoctrine()->getManager();
              $entityManager->persist($schoolYear);
              $entityManager->flush();
            }
          }

          if ($schoolYear->getIsSetup1() == 1 && $schoolYear->getIsSetup2() == 0) {
            $isStep2Complete = true;
            foreach ($schoolYear->getSchoolunits() as $unit) {
              if ($unit->getIsSetup1Complete() == false) {
                $isStep2Complete = false;
              }
            }
            if($isStep2Complete) {
              $schoolYear->setIsSetup2(1);

              $entityManager = $this->getDoctrine()->getManager();
              $entityManager->persist($schoolYear);
              $entityManager->flush();
            }
          }

          if ($schoolYear->getIsSetup2() == 1 && $schoolYear->getIsSetup3() == 0) {
            $isStep3Complete = true;
            foreach($schoolYear->getSchoolUnits() as $unit) {
              if ($unit->getIsSetup2Complete() == false) {
                $isStep3Complete = false;
              }
            }
            if($isStep3Complete) {
              $schoolYear->setIsSetup3(1);

              $entityManager = $this->getDoctrine()->getManager();
              $entityManager->persist($schoolYear);
              $entityManager->flush();
            }
          }

          if ($schoolYear->getIsSetup2() == 1 && $schoolYear->getIsSetup4() == 0) {
            $isStep4Complete = true;
            foreach($schoolYear->getSchoolUnits() as $unit) {
              if ($unit->getIsSetup3Complete() == false) {
                $isStep4Complete = false;
              }
            }
            if($isStep4Complete) {
              $schoolYear->setIsSetup4(1);

              $entityManager = $this->getDoctrine()->getManager();
              $entityManager->persist($schoolYear);
              $entityManager->flush();
            }
          }

          if ($schoolYear->getIsSetup6() == 1 && $schoolYear->getIsSetup7() == 0) {
            $isStep7Complete = true;
            foreach($schoolYear->getSchoolUnits() as $unit) {
              if ($unit->getIsSetup4Complete() == false) {
                $isStep7Complete = false;
              }
            }
            if($isStep7Complete) {
              $schoolYear->setIsSetup7(1);

              $entityManager = $this->getDoctrine()->getManager();
              $entityManager->persist($schoolYear);
              $entityManager->flush();
            }
          }

          return $this->render('school_year/school.year.setup.html.twig', [
              'school_year' => $schoolYear,
              'prev_year'   => $prevSchoolYear,
          ]);
      }

     /**
      * @Route("/school/year/setup1/{id}", name="school_year_setup1")
      * @Method({"GET", "POST"})
      */
      public function setupStep1(Request $request, $id)
      {
        $schoolYear = $this->getDoctrine()->getRepository
        (SchoolYear::class)->find($id);

        if ($schoolYear->getIsSetup1()) {
          $this->get('session')->getFlashBag()->add(
              'notice',
              "Importul pentru PASUL 1 a fost deja realizat!\n"
          );

          return $this->redirectToRoute('school_year_setup', array('id'=>$id));
        }

        $prevSchoolYear = $this->getDoctrine()->getRepository
        (SchoolYear::class)->findCurrentYear();

        $existingUnits = array();
        $newUnits = array();

        $forms = array();
        $views = array();

        foreach ($prevSchoolYear->getSchoolunits() as $prevUnit) {

          $existingUnits[] = $prevUnit;

          $newUnit = new SchoolUnit();
          $newUnit->setUnitname($prevUnit->getUnitname()." (nou)");
          $newUnit->setStartDate($prevUnit->getStartDate()->modify('+1 year'));
          $newUnit->setEndDate($prevUnit->getEndDate()->modify('+1 year'));
          $newUnit->setDescription($prevUnit->getDescription());
          $newUnit->setSchoolYear($schoolYear);
          $newUnit->setAvailableSpots($prevUnit->getAvailableSpots());
          $newUnit->setFirstInvoiceSerial($prevUnit->getFirstInvoiceSerial());
          $newUnit->setFirstInvoiceNumber($prevUnit->getFirstInvoiceNumber());
          $newUnit->setFirstReceiptSerial($prevUnit->getFirstReceiptSerial());
          $newUnit->setFirstReceiptNumber($prevUnit->getFirstReceiptNumber());
          $newUnit->setImportedFrom($prevUnit);

          $newUnits[] = $newUnit;

        }

        if (sizeof($newUnits) > 0) {
          $formFactory = $this->get('form.factory');
          $form = $formFactory->createNamedBuilder('import_units_form', FormType::class, array('units' => $newUnits));

          $form->add('units', CollectionType::class, array(
            'label' => false,
            'entry_type' => SYStep1Type::class,
          ));

          $the_form = $form->getForm();
          $the_view = $form->getForm()->createView();
        } else {
          return $this->redirectToRoute('school_year_setup', array('id'=>$id));
        }

        if ($request->isMethod('POST')) {

          $the_form->handleRequest($request);

          if ($the_form->isSubmitted() && $the_form->isValid()) {

            $data = $the_form->getData();

            $summary = '';

            // $data['units'] contains an array of AppBundle\Entity\SchoolUnit
            // use it to persist the categories in a foreach loop

            $j = 0;
            foreach ($data['units'] as $newUnit) {

              if ($request->get("import_units_form")["units"][$j]["isImport"] == 1) {
                $summary = $summary."Salvăm ".$newUnit->getUnitname()."...\n";

                $newUnit->setFirstInvoiceSerial(strtoupper($newUnit->getFirstInvoiceSerial()));
                $newUnit->setFirstReceiptSerial(strtoupper($newUnit->getFirstReceiptSerial()));

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($newUnit);
                $entityManager->flush();
              }

              $j++;

            }

            $schoolYear->setIsSetup1(true);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($schoolYear);
            $entityManager->flush();

            $this->get('session')->getFlashBag()->add(
                'notice',
                "Importul pentru PASUL 1 a fost realizat cu succes!\n\n".$summary
            );

            return $this->redirectToRoute('school_year_setup', array('id'=>$id));

          } else {
            $this->get('session')->getFlashBag()->add(
                'notice',
                "Există erori!\n".$the_form->getErrors()
            );
          }
        }


        return $this->render('school_year/school.year.setup1.html.twig', [
            'school_year' => $schoolYear,
            'prev_year'   => $prevSchoolYear,
            'existing_units' => $existingUnits,
            'form' => $the_view,
        ]);

      }

     /**
      * @Route("/school/year/setup2/{id}", name="school_year_setup2")
      * @Method({"GET", "POST"})
      */
      public function setupStep2(Request $request, $id)
      {
        $schoolUnit = $this->getDoctrine()->getRepository
        (SchoolUnit::class)->find($id);

        $schoolYear = $schoolUnit->getSchoolyear();

        if ($schoolYear->getIsSetup2()) {
          $this->get('session')->getFlashBag()->add(
            'notice',
            "Importul pentru PASUL 2 a fost deja realizat!\n"
          );

          return $this->redirectToRoute('school_year_setup', array('id'=>$schoolYear->getId()));
        }

        $prevSchoolYear = $this->getDoctrine()->getRepository
        (SchoolYear::class)->findCurrentYear();

        $existingServices = array();
        $newServices = array();

        $forms = array();
        $views = array();

        foreach ($schoolUnit->getImportedFrom()->getSchoolservices() as $prevService) {

          $existingServices[] = $prevService;

          $newService = new SchoolService();
          $newService->setServicename($prevService->getServicename());
          $newService->setSchoolyear($schoolYear);
          $newService->setSchoolunit($schoolUnit);
          $newService->setServicedescription($prevService->getServicedescription());
          $newService->setServiceprice($prevService->getServiceprice());
          $newService->setInAdvance($prevService->getInAdvance());
          $newService->setImportedFrom($prevService);

          $newServices[] = $newService;

        }

        if (sizeof($newServices) > 0) {
          $formFactory = $this->get('form.factory');
          $form = $formFactory->createNamedBuilder('import_services_form', FormType::class, array('services' => $newServices));

          $form->add('services', CollectionType::class, array(
            'label' => false,
            'entry_type' => SYStep2Type::class,
          ));

          $the_form = $form->getForm();
          $the_view = $form->getForm()->createView();
        } else {
          return $this->redirectToRoute('school_year_setup', array('id'=>$schoolYear->getId()));
        }

        if ($request->isMethod('POST')) {

          $the_form->handleRequest($request);

          if ($the_form->isSubmitted() && $the_form->isValid()) {

            $data = $the_form->getData();

            $summary = '';

            // $data['services'] contains an array of AppBundle\Entity\SchoolService
            // use it to persist the categories in a foreach loop

            $j = 0;
            foreach ($data['services'] as $newService) {

              if ($request->get("import_services_form")["services"][$j]["isImport"] == 1) {
                $summary = $summary."Salvăm ".$newService->getServicename()."...\n";

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($newService);
                $entityManager->flush();
              }

              $j++;

            }

            $schoolUnit->setIsSetup1Complete(true);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($schoolUnit);
            $entityManager->flush();

            $this->get('session')->getFlashBag()->add(
                'notice',
                "Importul pentru PASUL 2: ".$schoolUnit->getUnitname()." a fost realizat cu succes!\n\n".$summary
            );

            return $this->redirectToRoute('school_year_setup', array('id'=>$schoolYear->getId()));

          } else {
            $this->get('session')->getFlashBag()->add(
                'notice',
                "Există erori!\n".$the_form->getErrors()
            );
          }
        }


        return $this->render('school_year/school.year.setup2.html.twig', [
            'school_year' => $schoolYear,
            'prev_year'   => $prevSchoolYear,
            'school_unit' => $schoolUnit,
            'existing_services' => $existingServices,
            'form' => $the_view,
        ]);

      }

     /**
      * @Route("/school/year/setup3/{id}", name="school_year_setup3")
      * @Method({"GET", "POST"})
      */
      public function setupStep3(Request $request, $id)
      {
        $schoolUnit = $this->getDoctrine()->getRepository
        (SchoolUnit::class)->find($id);

        $schoolYear = $schoolUnit->getSchoolyear();

        if ($schoolYear->getIsSetup3()) {
          $this->get('session')->getFlashBag()->add(
            'notice',
            "Importul pentru PASUL 3 a fost deja realizat!\n"
          );

          return $this->redirectToRoute('school_year_setup', array('id'=>$schoolYear->getId()));
        }

        $prevSchoolYear = $this->getDoctrine()->getRepository
        (SchoolYear::class)->findCurrentYear();

        $existingClassGroups = array();
        $newClassGroups = array();

        $forms = array();
        $views = array();

        foreach ($schoolUnit->getImportedFrom()->getClassGroups() as $prevGroup) {

          $existingClassGroups[] = $prevGroup;

          $newClassGroup = new ClassGroup();
          $newClassGroup->setGroupName($prevGroup->getGroupName());
          $newClassGroup->setSchoolUnit($schoolUnit);
          $newClassGroup->setProfessor($prevGroup->getProfessor());
          $newClassGroup->setImportedFrom($prevGroup);

          $newClassGroups[] = $newClassGroup;

        }

        if (sizeof($newClassGroups) > 0) {
          $formFactory = $this->get('form.factory');
          $form = $formFactory->createNamedBuilder('import_classgroups_form', FormType::class, array('classgroups' => $newClassGroups));

          $form->add('classgroups', CollectionType::class, array(
            'label' => false,
            'entry_type' => SYStep3Type::class,
          ));

          $the_form = $form->getForm();
          $the_view = $form->getForm()->createView();
        } else {
          return $this->redirectToRoute('school_year_setup', array('id'=>$schoolYear->getId()));
        }

        if ($request->isMethod('POST')) {

          $the_form->handleRequest($request);

          if ($the_form->isSubmitted() && $the_form->isValid()) {

            $data = $the_form->getData();

            $summary = '';

            // $data['classgroups'] contains an array of AppBundle\Entity\ClassGroup
            // use it to persist the categories in a foreach loop

            $j = 0;
            foreach ($data['classgroups'] as $newClassGroup) {

              if ($request->get("import_classgroups_form")["classgroups"][$j]["isImport"] == 1) {
                $summary = $summary."Salvăm ".$newClassGroup->getGroupName()."...\n";

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($newClassGroup);
                $entityManager->flush();
              }

              $j++;

            }

            $schoolUnit->setIsSetup2Complete(true);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($schoolUnit);
            $entityManager->flush();

            $this->get('session')->getFlashBag()->add(
                'notice',
                "Importul pentru PASUL 3: ".$schoolUnit->getUnitname()." a fost realizat cu succes!\n\n".$summary
            );

            return $this->redirectToRoute('school_year_setup', array('id'=>$schoolYear->getId()));

          } else {
            $this->get('session')->getFlashBag()->add(
                'notice',
                "Există erori!\n".$the_form->getErrors()
            );
          }
        }


        return $this->render('school_year/school.year.setup3.html.twig', [
            'school_year' => $schoolYear,
            'prev_year'   => $prevSchoolYear,
            'school_unit' => $schoolUnit,
            'existing_groups' => $existingClassGroups,
            'form' => $the_view,
        ]);

      }

     /**
      * @Route("/school/year/setup4/{id}", name="school_year_setup4")
      * @Method({"GET", "POST"})
      */
      public function setupStep4(Request $request, $id)
      {
        $schoolUnit = $this->getDoctrine()->getRepository
        (SchoolUnit::class)->find($id);

        $schoolYear = $schoolUnit->getSchoolyear();

        if ($schoolYear->getIsSetup4()) {
          $this->get('session')->getFlashBag()->add(
            'notice',
            "Importul pentru PASUL 4 a fost deja realizat!\n"
          );

          return $this->redirectToRoute('school_year_setup', array('id'=>$schoolYear->getId()));
        }

        $prevSchoolYear = $this->getDoctrine()->getRepository
        (SchoolYear::class)->findCurrentYear();

        $existingClassOptionals = array();
        $newClassOptionals = array();

        $forms = array();
        $views = array();

        foreach ($schoolUnit->getImportedFrom()->getClassOptionals() as $prevOptional) {

          $existingClassOptionals[] = $prevOptional;

          $newClassOptional = new ClassOptional();
          $newClassOptional->setOptionalName($prevOptional->getOptionalName());
          $newClassOptional->setDescription($prevOptional->getDescription());
          $newClassOptional->setPrice($prevOptional->getPrice());
          $newClassOptional->setSchoolUnit($schoolUnit);
          $newClassOptional->setProfessor($prevOptional->getProfessor());
          $newClassOptional->setUseAttend($prevOptional->getUseAttend());
          $newClassOptional->setImportedFrom($prevOptional);

          $newClassOptionals[] = $newClassOptional;

        }

        if (sizeof($newClassOptionals) > 0) {
          $formFactory = $this->get('form.factory');
          $form = $formFactory->createNamedBuilder('import_classoptionals_form', FormType::class, array('classoptionals' => $newClassOptionals));

          $form->add('classoptionals', CollectionType::class, array(
            'label' => false,
            'entry_type' => SYStep4Type::class,
          ));

          $the_form = $form->getForm();
          $the_view = $form->getForm()->createView();
        } else {
          return $this->redirectToRoute('school_year_setup', array('id'=>$schoolYear->getId()));
        }

        if ($request->isMethod('POST')) {

          $the_form->handleRequest($request);

          if ($the_form->isSubmitted() && $the_form->isValid()) {

            $data = $the_form->getData();

            $summary = '';

            // $data['classoptionals'] contains an array of AppBundle\Entity\ClassOptional
            // use it to persist the categories in a foreach loop

            $j = 0;
            foreach ($data['classoptionals'] as $newClassOptional) {

              if ($request->get("import_classoptionals_form")["classoptionals"][$j]["isImport"] == 1) {
                $summary = $summary."Salvăm ".$newClassOptional->getOptionalName()."...\n";

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($newClassOptional);
                $entityManager->flush();
              }

              $j++;

            }

            $schoolUnit->setIsSetup3Complete(true);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($schoolUnit);
            $entityManager->flush();

            $this->get('session')->getFlashBag()->add(
                'notice',
                "Importul pentru PASUL 4: ".$schoolUnit->getUnitname()." a fost realizat cu succes!\n\n".$summary
            );

            return $this->redirectToRoute('school_year_setup', array('id'=>$schoolYear->getId()));

          } else {
            $this->get('session')->getFlashBag()->add(
                'notice',
                "Există erori!\n".$the_form->getErrors()
            );
          }
        }


        return $this->render('school_year/school.year.setup4.html.twig', [
            'school_year' => $schoolYear,
            'prev_year'   => $prevSchoolYear,
            'school_unit' => $schoolUnit,
            'existing_optionals' => $existingClassOptionals,
            'form' => $the_view,
        ]);

      }

     /**
      * @Route("/school/year/setup5/{id}", name="school_year_setup5")
      * @Method({"GET", "POST"})
      */
      public function setupStep5(Request $request, $id)
      {
        $schoolYear = $this->getDoctrine()->getRepository
        (SchoolYear::class)->find($id);

        // NOTE: Code to make step non-repeatable
        // if ($schoolYear->getIsSetup5()) {
        //   $this->get('session')->getFlashBag()->add(
        //     'notice',
        //     "Importul pentru PASUL 5 a fost deja realizat!\n"
        //   );
        //
        //   return $this->redirectToRoute('school_year_setup', array('id'=>$schoolYear->getId()));
        // }
        // NOTE: End of note

        $prevSchoolYear = $this->getDoctrine()->getRepository
        (SchoolYear::class)->findCurrentYear();

        $existingStudents = array();
        $newStudents = array();
        $newUnits = $schoolYear->getSchoolUnits();

        $forms = array();
        $views = array();

        foreach ($prevSchoolYear->getSchoolUnits() as $prevUnit) {
          foreach ($prevUnit->getStudents() as $prevStudent) {

            $newStudent = $this->getDoctrine()->getRepository
            (Student::class)->findOneBy(array(
              'importedFrom' => $prevStudent->getId(),
            ));

            if ($prevStudent->getEnrollment()->getIsActive() && $newStudent == NULL) {

              $newUnit = $this->getDoctrine()->getRepository
              (SchoolUnit::class)->findOneBy([
                'importedFrom' => $prevUnit->getId(),
              ]);

              if ($newUnit == NULL) {
                $newUnit = $newUnits[0];
              }

              if ($newUnit) {
                $existingStudents[] = $prevStudent;

                $newStudent = new Student();
                $newStudent->setUser($prevStudent->getUser());
                $newStudent->setSchoolUnit($newUnit);
                $newStudent->setImportedFrom($prevStudent);

                $newStudents[] = $newStudent;
              }

            }
          }
        }

        if (sizeof($newStudents) > 0) {
          $formFactory = $this->get('form.factory');
          $form = $formFactory->createNamedBuilder('import_students_form', FormType::class, array('students' => $newStudents));

          $form->add('students', CollectionType::class, array(
            'label' => false,
            'entry_type' => SYStep5Type::class,
            'entry_options' => array(
              'schoolunits' => $newUnits->getValues(),
            ),
          ));

          $the_form = $form->getForm();
          $the_view = $form->getForm()->createView();
        } else {
          return $this->redirectToRoute('school_year_setup', array('id'=>$schoolYear->getId()));
        }

        if ($request->isMethod('POST')) {

          $the_form->handleRequest($request);

          if ($the_form->isSubmitted() && $the_form->isValid()) {

            $data = $the_form->getData();

            $summary = '';

            // $data['classoptionals'] contains an array of AppBundle\Entity\ClassOptional
            // use it to persist the categories in a foreach loop

            $j = 0;
            foreach ($data['students'] as $newStudent) {

              if ($request->get("import_students_form")["students"][$j]["isImport"] == 1) {
                $summary = $summary."Salvăm ".$newStudent->getUser()->getFullName(1)."...\n";

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($newStudent);
                $entityManager->flush();
              }

              $j++;

            }

            $schoolYear->setIsSetup5(true);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($schoolYear);
            $entityManager->flush();

            $this->get('session')->getFlashBag()->add(
                'notice',
                "Importul pentru PASUL 5 a fost realizat cu succes!\n\n".$summary
            );

            return $this->redirectToRoute('school_year_setup', array('id'=>$schoolYear->getId()));

          } else {
            $this->get('session')->getFlashBag()->add(
                'notice',
                "Există erori!\n".$the_form->getErrors()
            );
          }
        }


        return $this->render('school_year/school.year.setup5.html.twig', [
            'school_year' => $schoolYear,
            'prev_year'   => $prevSchoolYear,
            'existing_students' => $existingStudents,
            'form' => $the_view,
        ]);

      }

     /**
      * @Route("/school/year/setup6/{id}", name="school_year_setup6")
      * @Method({"GET", "POST"})
      */
      public function setupStep6(Request $request, $id)
      {
        $schoolYear = $this->getDoctrine()->getRepository
        (SchoolYear::class)->find($id);

        // NOTE: Code to make step non-repeatable
        // if ($schoolYear->getIsSetup6()) {
        //   $this->get('session')->getFlashBag()->add(
        //     'notice',
        //     "Importul pentru PASUL 6 a fost deja realizat!\n"
        //   );
        //
        //   return $this->redirectToRoute('school_year_setup', array('id'=>$schoolYear->getId()));
        // }
        // NOTE: End note

        $prevSchoolYear = $this->getDoctrine()->getRepository
        (SchoolYear::class)->findCurrentYear();

        $existingEnrollments = array();
        $newEnrollments = array();

        // used for debug
        //$classGroup = NULL;

        $forms = array();
        $views = array();

        foreach ($schoolYear->getSchoolUnits() as $newUnit) {
          foreach ($newUnit->getStudents() as $student) {

            // To make the step repeatable, only show students which don't have an enrollment associated
            if ($student->getEnrollment() == NULL) {

              $prevEnrollment = $student->getImportedFrom()->getEnrollment();
              $existingEnrollments[] = $prevEnrollment;

              $prevService = $prevEnrollment->getIdService();
              $newService = $this->getDoctrine()->getRepository
              (SchoolService::class)->findOneBy(array(
                'importedFrom' => $prevService->getId(),
              ));

              $newEnrollment = new Enrollment();
              $newEnrollment->setIdChild($student->getUser());
              $newEnrollment->setIdParent($prevEnrollment->getIdParent());
              $newEnrollment->setIdUnit($newUnit);
              $newEnrollment->setIdService($newService);
              $newEnrollment->setEnrollDate(new \DateTime('now'));
              $newEnrollment->setNotes($prevEnrollment->getNotes());
              $newEnrollment->setIsActive(true);
              $newEnrollment->setSchoolYear($schoolYear);
              $newEnrollment->setStudent($student);
              $newEnrollment->setDaysToPay($prevEnrollment->getDaysToPay());

              $newEnrollments[] = $newEnrollment;
            }
          }
        }

        if (sizeof($newEnrollments) > 0) {
          $formFactory = $this->get('form.factory');
          $form = $formFactory->createNamedBuilder('import_enrollments_form', FormType::class, array('enrollments' => $newEnrollments));

          $form->add('enrollments', CollectionType::class, array(
            'label' => false,
            'entry_type' => SYStep6Type::class,
          ));

          $the_form = $form->getForm();
          $the_view = $form->getForm()->createView();
        } else {
            $this->get('session')->getFlashBag()->add(
              'notice',
              "Toți elevii importați au fost deja înscriși!\n"
            );
          return $this->redirectToRoute('school_year_setup', array('id'=>$schoolYear->getId()));
        }

        if ($request->isMethod('POST')) {

          $the_form->handleRequest($request);

          if ($the_form->isSubmitted() && $the_form->isValid()) {

            $data = $the_form->getData();

            $summary = '';

            // $data['enrollments'] contains an array of AppBundle\Entity\Enrollment
            // use it to persist the categories in a foreach loop

            $j = 0;
            foreach ($data['enrollments'] as $newEnrollment) {

              if ($request->get("import_enrollments_form")["enrollments"][$j]["isImport"] == 1) {
                $summary = $summary."Salvăm ".$newEnrollment->getIdChild()->getFullName(1)."...\n";

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($newEnrollment);
                $entityManager->flush();

                $classGroup = $newEnrollment->getImportClassGroup();
                $classGroup->addStudent($newEnrollment->getStudent());

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($classGroup);
                $entityManager->flush();

              }

              $j++;

            }

            $schoolYear->setIsSetup6(true);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($schoolYear);
            $entityManager->flush();

            $this->get('session')->getFlashBag()->add(
                'notice',
                "Importul pentru PASUL 6 a fost realizat cu succes!\n\n".$summary
            );

            return $this->redirectToRoute('school_year_setup', array('id'=>$schoolYear->getId()));

          } else {
            $this->get('session')->getFlashBag()->add(
                'notice',
                "Există erori!\n".$the_form->getErrors()
            );
          }
        }


        return $this->render('school_year/school.year.setup6.html.twig', [
            'school_year' => $schoolYear,
            'prev_year'   => $prevSchoolYear,
            'existing_enrollments' => $existingEnrollments,
            'form' => $the_view,
            //'debug' => $classGroup,
        ]);

      }

     /**
      * @Route("/school/year/setup7/{id}", name="school_year_setup7")
      * @Method({"GET", "POST"})
      */
      public function setupStep7(Request $request, $id)
      {
        $schoolUnit = $this->getDoctrine()->getRepository
        (SchoolUnit::class)->find($id);

        $schoolYear = $schoolUnit->getSchoolyear();

        // NOTE: Use the following to make it non-repeatable
        // if ($schoolYear->getIsSetup7()) {
        //   $this->get('session')->getFlashBag()->add(
        //     'notice',
        //     "Importul pentru PASUL 7 a fost deja realizat!\n"
        //   );
        //
        //   return $this->redirectToRoute('school_year_setup', array('id'=>$schoolYear->getId()));
        // }
        // NOTE: ENDNOTE

        $prevSchoolYear = $this->getDoctrine()->getRepository
        (SchoolYear::class)->findCurrentYear();

        // Get all imported optionals which had previously had enrolled students
        $importedClassOptionals = array();

        if ($schoolUnit->getClassOptionals()->count() > 0) {
          foreach($schoolUnit->getClassOptionals() as $newOptional) {
            if ($newOptional->getImportedFrom() != NULL && $newOptional->getImportedFrom()->getStudents()->count() > 0) {
              $importedClassOptionals[] = $newOptional;
            }
          }
        } else {
          $this->get('session')->getFlashBag()->add(
              'notice',
              "Importul pentru PASUL 7 nu poate fi realizat întrucât nu există opționale importate din vechiul an."
          );
          return $this->redirectToRoute('school_year_setup', array('id'=>$schoolYear->getId()));
        }

        // Create form for valid optionals
        if (count($importedClassOptionals) > 0) {
          $form = $this->createForm(SYStep7Type::Class, null, array(
            'classoptionals' => $importedClassOptionals,
          ));

          $view = $form->createView();

          $form->handleRequest($request);

          if($form->isSubmitted() && $form->isValid()) {
            $optionals = $form->getData()['classOptionals'];

            $summary = '';
            foreach ($optionals as $optional) {

              //do the work
              $summary = $summary."==> ".$optional->getOptionalName()."\n";

              $oldOptional = $optional->getImportedFrom();
              $j = 0;
              foreach ($oldOptional->getStudents() as $oldStudent) {

                $newStudent = $this->getDoctrine()->getRepository
                (Student::class)->findOneBy(array(
                  'importedFrom' => $oldStudent->getId(),
                ));

                if ($newStudent != NULL) {
                  if ($newStudent->getSchoolUnit()->getImportedFrom() == $oldStudent->getSchoolUnit() && $newStudent->getEnrollment()->getIsActive()) {

                    $j = $j + 1;

                    $optional->addStudent($newStudent);

                    $entityManager = $this->getDoctrine()->getManager();
                    $entityManager->persist($optional);
                    $entityManager->flush();

                    $summary = $summary." --> ".$newStudent->getUser()->getFullName(1)."...\n";
                  }
                }
              }
              $summary = $summary."TOTAL ==> ".$j." înscrieri\n\n";
            }

            $schoolUnit->setIsSetup4Complete(true);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($schoolUnit);
            $entityManager->flush();


            $this->get('session')->getFlashBag()->add(
                'notice',
                "Importul pentru PASUL 7: ".$schoolUnit->getUnitname()." a fost realizat cu succes!\n\nSUMAR:\n".$summary
            );
            return $this->redirectToRoute('school_year_setup', array('id'=>$schoolYear->getId()));
          }

        } else {
          $this->get('session')->getFlashBag()->add(
              'notice',
              "Importul pentru PASUL 7 nu poate fi realizat întrucât nu există opționale cu elevi asociați."
          );
          return $this->redirectToRoute('school_year_setup', array('id'=>$schoolYear->getId()));
        }


        return $this->render('school_year/school.year.setup7.html.twig', [
            'school_year' => $schoolYear,
            'prev_year'   => $prevSchoolYear,
            'school_unit' => $schoolUnit,
            'form' => $view,
        ]);

      }

     /**
      * @Route("/school/year/setup8/{id}", name="school_year_setup8")
      * @Method({"GET", "POST"})
      */
      public function setupStep8(Request $request, $id)
      {
        $schoolYear = $this->getDoctrine()->getRepository
        (SchoolYear::class)->find($id);

        //NOTE: Use the following to make it non-repeatable
        // if ($schoolYear->getIsSetup8()) {
        //   $this->get('session')->getFlashBag()->add(
        //     'notice',
        //     "Importul pentru PASUL 8 a fost deja realizat!\n"
        //   );
        //
        //   return $this->redirectToRoute('school_year_setup', array('id'=>$schoolYear->getId()));
        // }
        //NOTE: ENDNOTE

        $prevSchoolYear = $this->getDoctrine()->getRepository
        (SchoolYear::class)->findCurrentYear();

        $importableRoutes = array();
        $newRoutes = array();

        foreach ($schoolYear->getSchoolUnits() as $unit) {
          foreach ($unit->getStudents() as $newStudent) {
            $oldStudent = $newStudent->getImportedFrom();
            if ($oldStudent != NULL) {
              if ($newStudent->getTransportRoute() == NULL && $oldStudent->getTransportRoute() != NULL) {
                $oldRoute = $oldStudent->getTransportRoute();
                $importableRoutes[] = $oldRoute;

                $newRoute = new TransportRoute();
                $newRoute->setStudent($newStudent);
                $newRoute->setDistance($oldRoute->getDistance());
                $newRoute->setPricePerKm($oldRoute->getPricePerKm());
                $newRoute->setPrice($oldRoute->getPrice());

                $newRoutes[] = $newRoute;
              }
            }
          }
        }

        if (count($importableRoutes) > 0) {

          $formFactory = $this->get('form.factory');
          $form = $formFactory->createNamedBuilder('import_routes_form', FormType::class, array('routes' => $newRoutes));

          $form->add('routes', CollectionType::class, array(
            'label' => false,
            'entry_type' => SYStep8Type::class,
          ));

          $the_form = $form->getForm();
          $the_view = $form->getForm()->createView();


          if ($request->isMethod('POST')) {

            $the_form->handleRequest($request);

            if ($the_form->isSubmitted() && $the_form->isValid()) {

              $data = $the_form->getData();

              $summary = '';

              // $data['routes'] contains an array of AppBundle\Entity\TransportRoute
              // use it to persist the categories in a foreach loop

              $j = 0;
              foreach ($data['routes'] as $newRoute) {

                if ($request->get("import_routes_form")["routes"][$j]["isImport"] == 1) {
                  $summary = $summary."Salvăm Ruta pentru ".$newRoute->getStudent()->getUser()->getFullName(1)."...\n";

                  $entityManager = $this->getDoctrine()->getManager();
                  $entityManager->persist($newRoute);
                  $entityManager->flush();

                }

                $j++;

              }

              $schoolYear->setIsSetup8(true);
              $entityManager = $this->getDoctrine()->getManager();
              $entityManager->persist($schoolYear);
              $entityManager->flush();

              $this->get('session')->getFlashBag()->add(
                  'notice',
                  "Importul pentru PASUL 8 a fost realizat cu succes!\n\n".$summary
              );

              return $this->redirectToRoute('school_year_setup', array('id'=>$schoolYear->getId()));

            } else {
              $this->get('session')->getFlashBag()->add(
                  'notice',
                  "Există erori!\n".$the_form->getErrors()
              );
            }
          }

        } else {
          $this->get('session')->getFlashBag()->add(
            'notice',
            "Nu există rute importabile!\n"
          );

          return $this->redirectToRoute('school_year_setup', array('id'=>$schoolYear->getId()));
        }



        return $this->render('school_year/school.year.setup8.html.twig', [
            'school_year' => $schoolYear,
            'prev_year'   => $prevSchoolYear,
            'imported_routes' => $importableRoutes,
            'form' => $the_view,
        ]);

      }

     /**
      * @Route("/school/year/setup6_undo/{id}", name="school_year_setup6_undo")
      * @Method({"GET", "POST", "DELETE"})
      */
      public function setupStep6_undo(Request $request, $id)
      {
        $schoolYear = $this->getDoctrine()->getRepository
        (SchoolYear::class)->find($id);

        $allEnrollments = $schoolYear->getEnrollments()->toArray();

        $entityManager = $this->getDoctrine()->getManager();

        foreach ($allEnrollments as $enrollment) {

          $student = $enrollment->getStudent();


          foreach ($student->getClassOptionals() as $optional) {
            $optional->removeStudent($student);
            $entityManager->persist($optional);
          }
          if ($student->getTransportRoute()) {
            $route = $student->getTransportRoute();
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($route);
          }

          $entityManager->remove($enrollment);
        }

        $schoolYear->setIsSetupComplete(false);
        $schoolYear->setIsSetup8(false);
        foreach($schoolYear->getSchoolUnits() as $unit) {
          $unit->setIsSetup4Complete(false);

          $entityManager->persist($unit);
        }
        $schoolYear->setIsSetup7(false);
        $schoolYear->setIsSetup6(false);

        $entityManager->persist($schoolYear);

        // Major flush for everything done so far
        $entityManager->flush();

        $this->get('session')->getFlashBag()->add(
          'notice',
          "Pasul 6 a fost ANULAT cu succes!\nNumăr de înscrieri anulate: ".count($allEnrollments)
        );

        return $this->redirectToRoute('school_year_setup', array('id'=>$schoolYear->getId()));
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
