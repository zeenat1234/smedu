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
        $schoolYear->setYearname(date('Y')."/".date('Y',strtotime('+1 year')));
        $schoolYear->setStartDate(\DateTime::createFromFormat('Y-m-d', date('Y-m-d')));
        $schoolYear->setEndDate(\DateTime::createFromFormat('Y-m-d', date('Y-m-d', strtotime('+1 year'))));
        $schoolYear->setYearlabel("An școlar ".date('Y')."/".date('Y',strtotime('+1 year')));
        $schoolYear->setIsPermActivity(1);

        $manager->persist($schoolYear);

        $manager->flush();
    }
}
