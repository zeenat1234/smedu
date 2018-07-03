<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use App\Entity\User;

class UserController extends Controller
{
    /**
     * @Route("/users", name="users")
     */
    public function users()
    {
        $users = $this->getDoctrine()->getRepository
        (User::class)->findAll();

        return $this->render('user/users.html.twig', array(
          'users' => $users
        ));

    }

    /**
     * @Route("/user/{id}", name="user_show")
     */
    public function show($id)
    {
        $user = $this->getDoctrine()->getRepository
        (User::class)->find($id);

        return $this->render('user/user.show.html.twig', array(
          'user' => $user
        ));

    }
}
