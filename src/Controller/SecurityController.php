<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
# Now comes our custom stuff
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends Controller
{
    /**
     * @Route("/login", name="login")
     */
    public function login(Request $request, AuthenticationUtils $utils)
    {
        $error = $utils->getLastAuthenticationError();
        $lastUsername = $utils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'error'           => $error,
            'last_username'   => $lastUsername
        ]);
    }

    /**
     * @Route("/login_success", name="login_success")
     */
    public function postLoginRedirectAction()
    {
        // This logic is also implemented as a backup
        // method in HomeController. This method is
        // defined in the security.yaml file

        if ($this->getUser()->getUsertype() === 'ROLE_PARENT') {
            return $this->redirectToRoute("myaccount");
        //} else if (/* user needs to see location B */) {
        //    return $this->redirectToRoute("location_b");
        } else {
            return $this->redirectToRoute("index");
        }
    }

    /**
     * @Route("/logout", name="logout")
     */
    public function logout()
    {

    }
}
