<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Commission;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class CommissionFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $count_project = 25;

        $projects = [];

        for ($i = 1; $i <= $count_project; ++$i) {
            $projects[] = $this->getReference('project_' . $i);
        }

        $count_user = 6;

        $users = [];

        for ($i = 1; $i <= $count_user; ++$i) {
            $users[] = $this->getReference('user_' . $i);
        }

        foreach ($projects as $project) {
            /*foreach ($project->getProductEvent() as $productEvent) {
                foreach ($users as $user) {
                    $com = new Commission();
                    $com->setUser($user);
                    $com->setProduct($productEvent);
                    $com->setPercentCom(random_int(5, 20));
                    $manager->persist($com);
                }
            }

            foreach ($project->getProductDivers() as $productDivers) {
                foreach ($users as $user) {
                    $com = new Commission();
                    $com->setUser($user);
                    $com->setProduct($productDivers);
                    $com->setPercentCom(random_int(5, 20));
                    $manager->persist($com);
                }
            }*/

            foreach ($project->getProductPackage() as $productPackage) {
                foreach ($users as $user) {
                    $com = new Commission();
                    $com->setUser($user);
                    $com->setProduct($productPackage);
                    $com->setPercentCom(random_int(5, 20));
                    $manager->persist($com);
                }
            }

            foreach ($project->getProductSponsoring() as $productSponsor) {
                foreach ($users as $user) {
                    $com = new Commission();
                    $com->setUser($user);
                    $com->setProduct($productSponsor);
                    $com->setPercentCom(random_int(5, 20));
                    $manager->persist($com);
                }
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            ProjectsFixtures::class,
        ];
    }
}
