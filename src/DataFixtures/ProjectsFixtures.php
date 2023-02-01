<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Product;
use App\Entity\Project;
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
            foreach ($data['projects']['event'] as $event)
            {
                ProductEventFactory::new()
                    ->project($project->object())
                    ->create($event);
            }
            foreach ($data['projects']['sponsoring'] as $sponsoring)
            {
                ProductSponsorFactory::new()
                    ->project($project->object())
                    ->create($sponsoring);
            }
            foreach ($data['projects']['package'] as $package)
            {
                ProductPackageFactory::new()
                    ->project($project->object())
                    ->create($package);
            }
            foreach ($data['projects']['divers'] as $divers)
            {
                ProductDiversFactory::new()
                    ->project($project->object())
                    ->create($divers);
            }

            ++$i;
            $this->addReference('project_' . $i, $project->object());
        }
    }

    private function getDatas(): array
    {
        return [
            [
                'name' => 'Chapelle Reine Elisabeth',//1
                'projects' => [
                    'event' =>  [],
                    'sponsoring' =>  [],
                    'package' =>  [
                        ['name' => 'package 1', 'percent_vr' => 20],
                        ['name' => 'package 2', 'percent_vr' => 20],
                    ],
                    'divers' =>  [],
                ]
            ],
            [
                'name' => 'Concert - La Hulpe',//2
                'projects' => [
                    'event' =>  [],
                    'sponsoring' =>  [
                        ['name' => 'sponsoring 1', 'percent_vr' => 20],
                        ['name' => 'sponsoring 2', 'percent_vr' => 20],
                        ['name' => 'sponsoring 3', 'percent_vr' => 20],
                    ],
                    'package' =>  [
                        ['name' => 'package 1', 'percent_vr' => 15],
                        ['name' => 'package 2', 'percent_vr' => 15],
                    ],
                    'divers' =>  [],
                ]
            ],
            [
                'name' => 'Concours Reine Elisabeth',//3
                'projects' => [
                    'event' =>  [],
                    'sponsoring' =>  [],
                    'package' =>  [
                        ['name' => 'package 1', 'percent_vr' => 20],
                        ['name' => 'package 2', 'percent_vr' => 20],
                    ],
                    'divers' =>  [],
                ]
            ],
            [
                'name' => 'Cyclisme: ASO: Flêche, Paris-Roubaix,...',//4
                'projects' => [
                    'event' =>  [],
                    'sponsoring' =>  [],
                    'package' =>  [
                        ['name' => 'package 1', 'percent_vr' => 15],
                        ['name' => 'package 2', 'percent_vr' => 15],
                    ],
                    'divers' =>  [],
                ]
            ],
            [
                'name' => 'Football : Championnat de Belgique',//5
                'projects' => [
                    'event' =>  [],
                    'sponsoring' =>  [],
                    'package' =>  [
                        ['name' => 'package 1', 'percent_vr' => 20],
                        ['name' => 'package 2', 'percent_vr' => 20],
                    ],
                    'divers' =>  [],
                ]
            ],
            [
                'name' => 'Football : Champions League',//6
                'projects' => [
                    'event' =>  [],
                    'sponsoring' =>  [],
                    'package' =>  [
                        ['name' => 'package 1', 'percent_vr' => 20],
                        ['name' => 'package 2', 'percent_vr' => 20],
                    ],
                    'divers' =>  [],
                ]
            ],
            [
                'name' => 'Football : coupe de Belgique',//7
                'projects' => [
                    'event' =>  [],
                    'sponsoring' =>  [],
                    'package' =>  [
                        ['name' => 'package 1', 'percent_vr' => 20],
                        ['name' => 'package 2', 'percent_vr' => 20],
                    ],
                    'divers' =>  [],
                ]
            ],
            [
                'name' => 'Football : Red Devils',//8
                'projects' => [
                    'event' =>  [],
                    'sponsoring' =>  [],
                    'package' =>  [
                        ['name' => 'package 1', 'percent_vr' => 20],
                        ['name' => 'package 2', 'percent_vr' => 20],
                    ],
                    'divers' =>  [],
                ]
            ],
            [
                'name' => 'Formule 1 à Spa et Monaco',//9
                'projects' => [
                    'event' =>  [],
                    'sponsoring' =>  [],
                    'package' =>  [
                        ['name' => 'package 1', 'percent_vr' => 20],
                        ['name' => 'package 2', 'percent_vr' => 20],
                    ],
                    'divers' =>  [],
                ]
            ],
            [
                'name' => 'Hockey : ION Hockey Finals - Via Sportero (tbc) ',//10
                'projects' => [
                    'event' =>  [],
                    'sponsoring' =>  [],
                    'package' =>  [
                        ['name' => 'package 1', 'percent_vr' => 15],
                        ['name' => 'package 2', 'percent_vr' => 15],
                    ],
                    'divers' =>  [],
                ]
            ],
            [
                'name' => 'Les grands Crus',//11
                'projects' => [
                    'event' =>  [],
                    'sponsoring' =>  [],
                    'package' =>  [
                        ['name' => 'package 1', 'percent_vr' => 15],
                        ['name' => 'package 2', 'percent_vr' => 15],
                    ],
                    'divers' =>  [],
                ]
            ],
            [
                'name' => 'MB Productions (Cirque du Soleil, … )',//12
                'projects' => [
                    'event' =>  [],
                    'sponsoring' =>  [],
                    'package' =>  [
                        ['name' => 'package 1', 'percent_vr' => 20],
                        ['name' => 'package 2', 'percent_vr' => 20],
                    ],
                    'divers' =>  [],
                ]
            ],
            [
                'name' => 'Padel Tour 2024',//13
                'projects' => [
                    'event' =>  [],
                    'sponsoring' =>  [
                        ['name' => 'sponsoring 1', 'percent_vr' => 10],
                        ['name' => 'sponsoring 2', 'percent_vr' => 10],
                    ],
                    'package' =>  [
                        ['name' => 'package 1', 'percent_vr' => 10],
                        ['name' => 'package 2', 'percent_vr' => 10],
                    ],
                    'divers' =>  [],
                ]
            ],
            [
                'name' => 'Palais 12',//14
                'projects' => [
                    'event' =>  [],
                    'sponsoring' =>  [],
                    'package' =>  [
                        ['name' => 'package 1', 'percent_vr' => 20],
                        ['name' => 'package 2', 'percent_vr' => 20],
                    ],
                    'divers' =>  [],
                ]
            ],
            [
                'name' => 'Roland Garros / Wimbledon',//15
                'projects' => [
                    'event' =>  [],
                    'sponsoring' =>  [],
                    'package' =>  [
                        ['name' => 'package 1', 'percent_vr' => 6],
                        ['name' => 'package 2', 'percent_vr' => 6],
                    ],
                    'divers' =>  [],
                ]
            ],
            [
                'name' => 'Tomorrowland',//16
                'projects' => [
                    'event' =>  [],
                    'sponsoring' =>  [],
                    'package' =>  [
                        ['name' => 'package 1', 'percent_vr' => 15],
                        ['name' => 'package 2', 'percent_vr' => 15],
                    ],
                    'divers' =>  [],
                ]
            ],
            [
                'name' => 'Rugby : World Cup',//17
                'projects' => [
                    'event' =>  [],
                    'sponsoring' =>  [],
                    'package' =>  [
                        ['name' => 'package 1', 'percent_vr' => 15],
                        ['name' => 'package 2', 'percent_vr' => 15],
                    ],
                    'divers' =>  [],
                ]
            ],
            [
                'name' => 'Namur HC  / Cash',//18
                'projects' => [
                    'event' =>  [],
                    'sponsoring' =>  [
                        ['name' => 'sponsoring 1', 'percent_vr' => 20],
                        ['name' => 'sponsoring 2', 'percent_vr' => 20],
                    ],
                    'package' =>  [],
                    'divers' =>  [],
                ]
            ],
            [
                'name' => 'Namur HC  / Echange',//19
                'projects' => [
                    'event' =>  [],
                    'sponsoring' =>  [
                        ['name' => 'sponsoring 1', 'percent_vr' => 20],
                        ['name' => 'sponsoring 2', 'percent_vr' => 20],
                    ],
                    'package' =>  [],
                    'divers' =>  [],
                ]
            ],
            [
                'name' => 'Royal Léopold Club',//20
                'projects' => [
                    'event' =>  [],
                    'sponsoring' =>  [
                        ['name' => 'sponsoring 1', 'percent_vr' => 20],
                        ['name' => 'sponsoring 2', 'percent_vr' => 20],
                    ],
                    'package' =>  [],
                    'divers' =>  [],
                ]
            ],
            [
                'name' => 'E-Sports',//21
                'projects' => [
                    'event' =>  [],
                    'sponsoring' =>  [
                        ['name' => 'sponsoring 1', 'percent_vr' => 10],
                        ['name' => 'sponsoring 2', 'percent_vr' => 10],
                    ],
                    'package' =>  [],
                    'divers' =>  [],
                ]
            ],
            [
                'name' => 'Led / Foot',//22
                'projects' => [
                    'event' =>  [],
                    'sponsoring' =>  [
                        ['name' => 'sponsoring 1', 'percent_vr' => 20],
                        ['name' => 'sponsoring 2', 'percent_vr' => 20],
                    ],
                    'package' =>  [],
                    'divers' =>  [],
                ]
            ],
            [
                'name' => 'La Hulpe concert',//23
                'projects' => [
                    'event' =>  [],
                    'sponsoring' =>  [
                        ['name' => 'sponsoring 1', 'percent_vr' => 20],
                        ['name' => 'sponsoring 2', 'percent_vr' => 20],
                    ],
                    'package' =>  [
                        ['name' => 'package 1', 'percent_vr' => 15],
                        ['name' => 'package 2', 'percent_vr' => 15],
                    ],
                    'divers' =>  [],
                ]
            ],
            [
                'name' => 'Dinner on the Wheel',//24
                'projects' => [
                    'event' =>  [
                    ],
                    'sponsoring' =>  [
                        ['name' => 'sponsoring 1', 'percent_vr' => 20],
                        ['name' => 'sponsoring 2', 'percent_vr' => 20],
                    ],
                    'package' =>  [
                        ['name' => 'package 1', 'percent_vr' => 20],
                        ['name' => 'package 2', 'percent_vr' => 20],
                    ],
                    'divers' =>  [],
                ]
            ],
            [
                'name' => 'AAF',//25
                'projects' => [
                    'event' =>  [],
                    'sponsoring' =>  [
                        ['name' => 'sponsoring 1', 'percent_vr' => 20],
                        ['name' => 'sponsoring 2', 'percent_vr' => 20],
                    ],
                    'package' =>  [],
                    'divers' =>  [],
                ]
            ],
            [
                'name' => 'Affordable art fair',//26
                'projects' => [
                    'event' =>  [],
                    'sponsoring' =>  [
                        ['name' => 'sponsoring 1', 'percent_vr' => 20],
                        ['name' => 'sponsoring 2', 'percent_vr' => 20],
                    ],
                    'package' =>  [],
                    'divers' =>  [],
                ]
            ],
        ];
    }
}
