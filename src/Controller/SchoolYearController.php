<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

#allows us to restrict methods like get and post
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

#can instantiate the entity
use App\Entity\SchoolYear;

class SchoolYearController extends Controller
{
    /**
     * @Route("/school/year", name="school_year")
     */
    public function schoolYear()
    {
        return $this->render('school_year/school.year.settings.html.twig', [
            'school_year' => '2018/2019',
        ]);
    }
}
