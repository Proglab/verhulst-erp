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
        $user = UserFactory::new()
            ->enabled()
            ->verified()
            ->adminAndCommercial()
            ->create([
                'firstname' => 'Gonzague', // 1
                'lastname' => 'Lefere',
                'email' => 'gonzague@verhulst.be',
            ]);
        ++$i;
        $this->addReference('user_' . $i, $user->object());
        $user = UserFactory::new()
            ->enabled()
            ->verified()
            ->adminAndCommercial()
            ->create([
                'firstname' => 'Jonathan', // 2
                'lastname' => 'Jossart',
                'email' => 'jonathan@verhulst.be',
            ]);
        ++$i;
        $this->addReference('user_' . $i, $user->object());
        $user = UserFactory::new()
            ->enabled()
            ->verified()
            ->commercial()
            ->create([
                'firstname' => 'Cedric', // 3
                'lastname' => 'Boulanger',
                'email' => 'cedric@verhulst.be',
            ]);
        ++$i;
        $this->addReference('user_' . $i, $user->object());
        $user = UserFactory::new()
            ->enabled()
            ->verified()
            ->commercial()
            ->create([
                'firstname' => 'Thierry', // 4
                'lastname' => 'Verhulst',
                'email' => 'thierry@verhulst.be',
            ]);
        ++$i;
        $this->addReference('user_' . $i, $user->object());
        $user = UserFactory::new()
            ->enabled()
            ->verified()
            ->commercial()
            ->create([
                'firstname' => 'Michael', // 5
                'lastname' => 'Veys',
                'email' => 'michael@verhulst.be',
            ]);
        ++$i;
        $this->addReference('user_' . $i, $user->object());
        $user = UserFactory::new()
            ->enabled()
            ->verified()
            ->commercial()
            ->create([
                'firstname' => 'Anthony', // 6
                'lastname' => 'Delhauteur',
                'email' => 'anthony@verhulst.be',
            ]);
        ++$i;
        $this->addReference('user_' . $i, $user->object());
        $user = UserFactory::new()
            ->enabled()
            ->verified()
            ->boss()
            ->create([
                'firstname' => 'Fabrice',
                'lastname' => 'Gyre',
                'email' => 'fabrice@insideweb.be',
                'password' => 'fabrice',
            ]);
        ++$i;
        $this->addReference('user_' . $i, $user->object());
        $user = UserFactory::new()
            ->enabled()
            ->verified()
            ->create([
                'firstname' => 'Gonzague', // 1
                'lastname' => 'Lefere',
                'email' => 'compta@verhulst.be',
                'roles' => ['ROLE_COMPTA'],
            ]);
        ++$i;
        $this->addReference('user_' . $i, $user->object());
        $user = UserFactory::new()
            ->enabled()
            ->verified()
            ->create([
                'firstname' => 'Gonzague', // 1
                'lastname' => 'Lefere',
                'email' => 'encodeur@verhulst.be',
                'roles' => ['ROLE_ENCODE'],
            ]);
        ++$i;
        $this->addReference('user_' . $i, $user->object());
        $user = UserFactory::new()
            ->enabled()
            ->verified()
            ->create([
                'firstname' => 'Admin', // 1
                'lastname' => 'Budget',
                'email' => 'adminbudget@verhulst.be',
                'roles' => ['ROLE_ADMIN_BUDGET'],
            ]);
        ++$i;
        $this->addReference('user_' . $i, $user->object());
        $user = UserFactory::new()
            ->enabled()
            ->verified()
            ->create([
                'firstname' => 'User', // 1
                'lastname' => 'Budget',
                'email' => 'budget@verhulst.be',
                'roles' => ['ROLE_BUDGET'],
            ]);
        ++$i;
        $this->addReference('user_' . $i, $user->object());
    }
}
