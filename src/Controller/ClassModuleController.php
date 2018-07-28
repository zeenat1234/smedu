<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ClassModuleController extends Controller
{
    /**
     * @Route("/class/modules", name="class_modules")
     */
    public function index()
    {
        return $this->render('class_module/index.html.twig', [
            'controller_name' => 'ClassModuleController',
        ]);
    }
}
