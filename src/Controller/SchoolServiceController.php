<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class SchoolServiceController extends Controller
{
    /**
     * @Route("/school/service", name="school_service")
     */
    public function index()
    {
        return $this->render('school_service/index.html.twig', [
            'controller_name' => 'SchoolServiceController',
        ]);
    }
}
