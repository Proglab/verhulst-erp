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
            ->many(9)
            ->create();

        UserFactory::new()
            ->enabled()
            ->verified()
            ->create([
                'email' => 'user@user.fr',
            ]);

        UserFactory::new()
            ->enabled()
            ->verified()
            ->admin()
            ->create([
                'email' => 'admin@admin.fr',
            ]);
    }
}
