<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ClassOptionalController extends Controller
{
    /**
     * @Route("/class/optionals", name="class_optionals")
     */
    public function index()
    {
        return $this->render('class_optional/class.optionals.html.twig', [
            'controller_name' => 'ClassOptionalController',
        ]);
    }
}
