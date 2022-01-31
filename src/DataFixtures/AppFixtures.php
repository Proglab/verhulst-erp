<?php

namespace App\DataFixtures;

use App\Factory\UserFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        UserFactory::new()
            ->enabled()
            ->verified()
            ->many(10)
            ->create();

        UserFactory::new()
            ->enabled()
            ->verified()
            ->admin()
            ->create();
    }
}
