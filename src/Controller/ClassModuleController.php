<?php

namespace App\Controller;

#can instantiate the entity
use App\Entity\ClassModule;
use App\Entity\SchoolUnit;
use App\Entity\SchoolYear;
use App\Entity\User;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#allows us to restrict methods like get and post
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

#form type definition
//use App\Form\ClassOptionalType;
//use App\Form\ClassOptionalEnrollType;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ClassModuleController extends AbstractController
{
    /**
     * @Route("/class/modules", name="class_modules")
     */
    public function index()
    {
        $currentSchoolYear = $this->getDoctrine()->getRepository
        (SchoolYear::class)->findCurrentYear();

        $schoolUnits = $currentSchoolYear->getSchoolunits();

        return $this->render('class_module/class.modules.html.twig', [
            'current_year'  => $currentSchoolYear,
            'current_units' => $schoolUnits,
        ]);

    }
}
