<?php

declare(strict_types=1);

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
            ->commercial()
            ->many(3)
            ->create();

        UserFactory::new()
            ->enabled()
            ->verified()
            ->commercial()
            ->create([
                'email' => 'commercial@admin.be',
            ]);

        UserFactory::new()
            ->enabled()
            ->verified()
            ->admin()
            ->create([
                'email' => 'admin@admin.be',
            ]);

        UserFactory::new()
            ->enabled()
            ->verified()
            ->boss()
            ->create([
                'email' => 'fabrice@insideweb.be',
                'password' => 'fabrice',
            ]);
    }
}
