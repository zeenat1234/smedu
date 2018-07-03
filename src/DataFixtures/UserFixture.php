<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Entity\User;

class UserFixture extends Fixture
{
    # the following is created to encode the password
    private $encoder;
    public function __construct(UserPasswordEncoderInterface $encoder)
    {
      $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        // $product = new Product();
        // $manager->persist($product);
        $user = new User();
        $user->setUsername('exciter');
        $user->setPassword(
          $this->encoder->encodePassword($user, 'cubanezu')
        );
        $user->setEmail('admin@smedu.ro');
        $user->setUsertype('ROLE_ADMIN');

        $manager->persist($user);

        $manager->flush();
    }
}
