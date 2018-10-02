<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
# Now comes our custom stuff
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

use Symfony\Component\Form\Extension\Core\Type\EmailType;

use App\Entity\User;

use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class SecurityController extends Controller
{
    # the following is created to encode the password
    private $encoder;
    public function __construct(UserPasswordEncoderInterface $encoder)
    {
      $this->encoder = $encoder;
    }


    /**
     * @Route("/login", name="login")
     */
    public function login(Request $request, AuthenticationUtils $utils)
    {
        $error = $utils->getLastAuthenticationError();
        $lastUsername = $utils->getLastUsername();

        if ($error) {
          $error = 'Informațiile introduse sunt greșite!';
        }

        return $this->render('security/login.html.twig', [
            'error'           => $error,
            'last_username'   => $lastUsername
        ]);
    }

    /**
     * @Route("/resetpass", name="resetpass")
     */
    public function resetpass(Request $request, \Swift_Mailer $mailer)
    {
        $form = $this->createFormBuilder()
          ->add('email', EmailType::class, array(
            'label' => 'Adresa de e-mail înregistrată:',
            'attr' => array(
              'class' => 'form-control'
            )
          ))
          ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
          $data = $form->getData();
          $email = $data['email'];

          //TODO magic
          $user = $this->getDoctrine()->getRepository
          (User::class)->findOneBy(array('email' => $email));

          if ($user) {
            $plainpass = $this->randomPassword();

            $user->setPassword(
              $this->encoder->encodePassword($user, $plainpass)
            );

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();

            $message = (new \Swift_Message('Resetare Parolă - Planeta Copiilor'))
            ->setFrom('no-reply@iteachsmart.ro')
            ->setTo($user->getEmail())
            ->setBody(
              $this->renderView(
                // templates/emails/registration.html.twig
                'security/resetpass.email.html.twig',
                array('user' => $user, 'plainpass' => $plainpass)
              ),
              'text/html'
              )
              /*
              * If you also want to include a plaintext version of the message
              ->addPart(
              $this->renderView(
              'emails/registration.txt.twig',
              array('name' => $name)
            ),
            'text/plain'
            )
            */
            ;

            $mailer->send($message);
            //TODO magic end

            $this->get('session')->getFlashBag()->add(
              'notice',
              'Un e-mail cu noua parolă a fost trimis la '.$email
            );

            return $this->redirectToRoute('login');
          } else {
            $this->get('session')->getFlashBag()->add(
              'error',
              'Nu există niciun cont cu această adresă de e-mail: '.$email.'!'
            );
            return $this->redirectToRoute('login');
          }

        }

        return $this->render('security/resetpass.html.twig', array(
          'form' => $form->createView()
        ));
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

    private function randomPassword() {

      $alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
      $pass = array(); //remember to declare $pass as an array
      $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache

      for ($i = 0; $i < 8; $i++) {
          $n = rand(0, $alphaLength);
          $pass[] = $alphabet[$n];
      }

      return implode($pass); //turn the array into a string
    }
}
