<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

use App\Entity\SchoolYear;

class SchoolYearFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        // $product = new Product();
        // $manager->persist($product);

        $schoolYear = new SchoolYear();
        $schoolYear->setYearname(date('Y',time())."/".date('Y',strtotime('+1 year')));
        $schoolYear->setStartDate(new \DateTime(time()));
        $schoolYear->setEndDate(new \DateTime(strtotime('+1 year')));
        $schoolYear->setYearlabel("An școlar ".date('Y',time())."/".date('Y',strtotime('+1 year')));
        $schoolYear->setIsPermActivity(1);

        $manager->persist($schoolYear);

        $manager->flush();
    }
}
