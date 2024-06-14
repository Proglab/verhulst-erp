<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Factory\ProductDiversFactory;
use App\Factory\ProductEventFactory;
use App\Factory\ProductPackageFactory;
use App\Factory\ProductSponsorFactory;
use App\Factory\ProjectFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ProjectsFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $i = 0;
        foreach ($this->getDatas() as $data) {
            $project = ProjectFactory::new()
                ->create([
                    'name' => $data['name'],
                ]);
            foreach ($data['projects']['sponsoring'] as $sponsoring) {
                ProductSponsorFactory::new()
                    ->project($project->object())
                    ->create($sponsoring);
            }
            foreach ($data['projects']['package'] as $package) {
                ProductPackageFactory::new()
                    ->project($project->object())
                    ->create($package);
            }

            ++$i;
            $this->addReference('project_' . $i, $project->object());
        }
    }

    private function getDatas(): array
    {
        return [
            [
                'name' => 'Chapelle Reine Elisabeth', // 1
                'projects' => [
                    'sponsoring' => [],
                    'package' => [
                        ['name' => 'package 1', 'percent_vr' => 20],
                        ['name' => 'package 2', 'percent_vr' => 20],
                    ],
                ],
            ],
            [
                'name' => 'Concert - La Hulpe', // 2
                'projects' => [
                    'sponsoring' => [
                        ['name' => 'sponsoring 1', 'percent_vr' => 20],
                        ['name' => 'sponsoring 2', 'percent_vr' => 20],
                        ['name' => 'sponsoring 3', 'percent_vr' => 20],
                    ],
                    'package' => [
                        ['name' => 'package 1', 'percent_vr' => 15],
                        ['name' => 'package 2', 'percent_vr' => 15],
                    ],
                ],
            ],
            [
                'name' => 'Concours Reine Elisabeth', // 3
                'projects' => [
                    'sponsoring' => [],
                    'package' => [
                        ['name' => 'package 1', 'percent_vr' => 20],
                        ['name' => 'package 2', 'percent_vr' => 20],
                    ],
                ],
            ],
            [
                'name' => 'Cyclisme: ASO: Flêche, Paris-Roubaix,...', // 4
                'projects' => [
                    'sponsoring' => [],
                    'package' => [
                        ['name' => 'package 1', 'percent_vr' => 15],
                        ['name' => 'package 2', 'percent_vr' => 15],
                    ],
                ],
            ],
            [
                'name' => 'Football : Championnat de Belgique', // 5
                'projects' => [
                    'sponsoring' => [],
                    'package' => [
                        ['name' => 'package 1', 'percent_vr' => 20],
                        ['name' => 'package 2', 'percent_vr' => 20],
                    ],
                ],
            ],
            [
                'name' => 'Football : Champions League', // 6
                'projects' => [
                    'sponsoring' => [],
                    'package' => [
                        ['name' => 'package 1', 'percent_vr' => 20],
                        ['name' => 'package 2', 'percent_vr' => 20],
                    ],
                ],
            ],
            [
                'name' => 'Football : coupe de Belgique', // 7
                'projects' => [
                    'sponsoring' => [],
                    'package' => [
                        ['name' => 'package 1', 'percent_vr' => 20],
                        ['name' => 'package 2', 'percent_vr' => 20],
                    ],
                ],
            ],
            [
                'name' => 'Football : Red Devils', // 8
                'projects' => [
                    'sponsoring' => [],
                    'package' => [
                        ['name' => 'package 1', 'percent_vr' => 20],
                        ['name' => 'package 2', 'percent_vr' => 20],
                    ],
                ],
            ],
            [
                'name' => 'Formule 1 à Spa et Monaco', // 9
                'projects' => [
                    'sponsoring' => [],
                    'package' => [
                        ['name' => 'package 1', 'percent_vr' => 20],
                        ['name' => 'package 2', 'percent_vr' => 20],
                    ],
                ],
            ],
            [
                'name' => 'Hockey : ION Hockey Finals - Via Sportero (tbc) ', // 10
                'projects' => [
                    'sponsoring' => [],
                    'package' => [
                        ['name' => 'package 1', 'percent_vr' => 15],
                        ['name' => 'package 2', 'percent_vr' => 15],
                    ],
                ],
            ],
            [
                'name' => 'Les grands Crus', // 11
                'projects' => [
                    'sponsoring' => [],
                    'package' => [
                        ['name' => 'package 1', 'percent_vr' => 15],
                        ['name' => 'package 2', 'percent_vr' => 15],
                    ],
                ],
            ],
            [
                'name' => 'MB Productions (Cirque du Soleil, … )', // 12
                'projects' => [
                    'sponsoring' => [],
                    'package' => [
                        ['name' => 'package 1', 'percent_vr' => 20],
                        ['name' => 'package 2', 'percent_vr' => 20],
                    ],
                ],
            ],
            [
                'name' => 'Padel Tour 2024', // 13
                'projects' => [
                    'sponsoring' => [
                        ['name' => 'sponsoring 1', 'percent_vr' => 10],
                        ['name' => 'sponsoring 2', 'percent_vr' => 10],
                    ],
                    'package' => [
                        ['name' => 'package 1', 'percent_vr' => 10],
                        ['name' => 'package 2', 'percent_vr' => 10],
                    ],
                ],
            ],
            [
                'name' => 'Palais 12', // 14
                'projects' => [
                    'sponsoring' => [],
                    'package' => [
                        ['name' => 'package 1', 'percent_vr' => 20],
                        ['name' => 'package 2', 'percent_vr' => 20],
                    ],
                ],
            ],
            [
                'name' => 'Roland Garros / Wimbledon', // 15
                'projects' => [
                    'sponsoring' => [],
                    'package' => [
                        ['name' => 'package 1', 'percent_vr' => 6],
                        ['name' => 'package 2', 'percent_vr' => 6],
                    ],
                ],
            ],
            [
                'name' => 'Tomorrowland', // 16
                'projects' => [
                    'sponsoring' => [],
                    'package' => [
                        ['name' => 'package 1', 'percent_vr' => 15],
                        ['name' => 'package 2', 'percent_vr' => 15],
                    ],
                ],
            ],
            [
                'name' => 'Rugby : World Cup', // 17
                'projects' => [
                    'sponsoring' => [],
                    'package' => [
                        ['name' => 'package 1', 'percent_vr' => 15],
                        ['name' => 'package 2', 'percent_vr' => 15],
                    ],
                ],
            ],
            [
                'name' => 'Namur HC  / Cash', // 18
                'projects' => [
                    'sponsoring' => [
                        ['name' => 'sponsoring 1', 'percent_vr' => 20],
                        ['name' => 'sponsoring 2', 'percent_vr' => 20],
                    ],
                    'package' => [],
                ],
            ],
            [
                'name' => 'Namur HC  / Echange', // 19
                'projects' => [
                    'sponsoring' => [
                        ['name' => 'sponsoring 1', 'percent_vr' => 20],
                        ['name' => 'sponsoring 2', 'percent_vr' => 20],
                    ],
                    'package' => [],
                ],
            ],
            [
                'name' => 'Royal Léopold Club', // 20
                'projects' => [
                    'sponsoring' => [
                        ['name' => 'sponsoring 1', 'percent_vr' => 20],
                        ['name' => 'sponsoring 2', 'percent_vr' => 20],
                    ],
                    'package' => [],
                ],
            ],
            [
                'name' => 'E-Sports', // 21
                'projects' => [
                    'sponsoring' => [
                        ['name' => 'sponsoring 1', 'percent_vr' => 10],
                        ['name' => 'sponsoring 2', 'percent_vr' => 10],
                    ],
                    'package' => [],
                ],
            ],
            [
                'name' => 'Led / Foot', // 22
                'projects' => [
                    'sponsoring' => [
                        ['name' => 'sponsoring 1', 'percent_vr' => 20],
                        ['name' => 'sponsoring 2', 'percent_vr' => 20],
                    ],
                    'package' => [],
                ],
            ],
            [
                'name' => 'La Hulpe concert', // 23
                'projects' => [
                    'sponsoring' => [
                        ['name' => 'sponsoring 1', 'percent_vr' => 20],
                        ['name' => 'sponsoring 2', 'percent_vr' => 20],
                    ],
                    'package' => [
                        ['name' => 'package 1', 'percent_vr' => 15],
                        ['name' => 'package 2', 'percent_vr' => 15],
                    ],
                ],
            ],
            [
                'name' => 'Dinner on the Wheel', // 24
                'projects' => [
                    'sponsoring' => [
                        ['name' => 'sponsoring 1', 'percent_vr' => 20],
                        ['name' => 'sponsoring 2', 'percent_vr' => 20],
                    ],
                    'package' => [
                        ['name' => 'package 1', 'percent_vr' => 20],
                        ['name' => 'package 2', 'percent_vr' => 20],
                    ],
                ],
            ],
            [
                'name' => 'AAF', // 25
                'projects' => [
                    'sponsoring' => [
                        ['name' => 'sponsoring 1', 'percent_vr' => 20],
                        ['name' => 'sponsoring 2', 'percent_vr' => 20],
                    ],
                    'package' => [],
                ],
            ],
            [
                'name' => 'Affordable art fair', // 26
                'projects' => [
                    'sponsoring' => [
                        ['name' => 'sponsoring 1', 'percent_vr' => 20],
                        ['name' => 'sponsoring 2', 'percent_vr' => 20],
                    ],
                    'package' => [],
                ],
            ],
        ];
    }
}
