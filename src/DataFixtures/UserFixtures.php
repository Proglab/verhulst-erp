<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Factory\UserFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class UserFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $i = 0;
        $users = UserFactory::new()
            ->enabled()
            ->verified()
            ->commercial()
            ->many(3)
            ->create();

        foreach ($users as $user) {
            ++$i;
            $this->addReference('user_' . $i, $user->object());
        }

        $user = UserFactory::new()
            ->enabled()
            ->verified()
            ->commercial()
            ->create([
                'email' => 'commercial@admin.be',
            ]);
        ++$i;
        $this->addReference('user_' . $i, $user->object());

        $user = UserFactory::new()
            ->enabled()
            ->verified()
            ->admin()
            ->create([
                'email' => 'admin@admin.be',
            ]);
        ++$i;
        $this->addReference('user_' . $i, $user->object());

        $user = UserFactory::new()
            ->enabled()
            ->verified()
            ->boss()
            ->create([
                'email' => 'fabrice@insideweb.be',
                'password' => 'fabrice',
            ]);
        ++$i;
        $this->addReference('user_' . $i, $user->object());
    }
}
