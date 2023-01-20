<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Factory\ProjectFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ProjectsFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $projects = ProjectFactory::new()
            ->many(20)
            ->create();

        $i = 0;
        foreach ($projects as $project) {
            ++$i;
            $this->addReference('project_' . $i, $project->object());
        }
    }
}
