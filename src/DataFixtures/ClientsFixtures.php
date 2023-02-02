<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Factory\CompanyFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ClientsFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $i = 0;
        foreach ($this->getData() as $company) {
            ++$i;
            $companies = CompanyFactory::new()
                ->create([
                    'name' => $company['name'],
                ]);
            $this->addReference('client_' . $i, $companies->object());
        }
    }

    public function getData(): array
    {
        return [
            [
                'name' => 'ARCH-Consult',  // 1
            ],
            [
                'name' => 'Segestra', // 2
            ],
            [
                'name' => 'Van den Berge', // 3
            ],
            [
                'name' => 'BMW', // 4
            ],
            [
                'name' => 'Bon repos', // 5
            ],
            [
                'name' => 'Carlsberg - cash', // 6
            ],
            [
                'name' => 'Carlsberg - mobilier', // 7
            ],
            [
                'name' => 'Carlsberg -Visibilité', // 8
            ],
            [
                'name' => 'Carroserie Weekend', // 9
            ],
            [
                'name' => 'Dim\'s', // 10
            ],
            [
                'name' => 'Duvel', // 11
            ],
            [
                'name' => 'ECS', // 12
            ],
            [
                'name' => 'EF Education', // 13
            ],
            [
                'name' => 'Flex', // 14
            ],
            [
                'name' => 'GBL', // 15
            ],
            [
                'name' => 'HLS', // 16
            ],
            [
                'name' => 'Hockey Player', // 17
            ],
            [
                'name' => 'Hockey Tennis', // 18
            ],
            [
                'name' => 'KBC Brussels', // 19
            ],
            [
                'name' => 'Laurent Perrier', // 20
            ],
            [
                'name' => 'Ladbrokes', // 21
            ],
            [
                'name' => 'miniox', // 22
            ],
            [
                'name' => 'nestlé', // 23
            ],
            [
                'name' => 'schweppes', // 24
            ],
            [
                'name' => 'Segafredo', // 25
            ],
            [
                'name' => 'TAO', // 26
            ],
            [
                'name' => 'Top Secret', // 27
            ],
            [
                'name' => 'Velu Vin', // 28
            ],
            [
                'name' => 'Smart', // 29
            ],
        ];
    }
}
