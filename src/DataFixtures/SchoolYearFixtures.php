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
        $startDate = date('Y-m-d');
        $schoolYear->setStartDate(\DateTime::createFromFormat('Y-m-d', $startDate)->setTime(00, 00, 00));
        $endDate = date('Y-m-d', strtotime('+1 year'));
        $schoolYear->setEndDate(\DateTime::createFromFormat('Y-m-d', $endDate)->setTime(23, 59, 59));
        $schoolYear->setYearlabel("An școlar ".date('Y')."/".date('Y',strtotime('+1 year')));
        $schoolYear->setIsPermActivity(1);
        $schoolYear->setLicense("XXXX-YYYY-XXXX-YYYY");
        $schoolYear->setLicenseStatus("Activă");

        $manager->persist($schoolYear);

        $manager->flush();
    }
}
