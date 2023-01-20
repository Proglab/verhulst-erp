<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Factory\CompanyFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use \stdClass;

class ClientsFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $companies = CompanyFactory::new()
            ->many(10)
            ->create();

        $i=0;
        foreach ($companies as $company) {
            $i++;
            $this->addReference('company_'.$i, $company->object());
        }

    }
}
