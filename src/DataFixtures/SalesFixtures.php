<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Commission;
use App\Entity\Product;
use App\Entity\Sales;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class SalesFixtures extends Fixture implements DependentFixtureInterface
{
    private array $projects;
    private array $users;
    private array $clients;

    public function load(ObjectManager $manager): void
    {
        $count_project = 26;

        $this->projects = [];

        for ($i = 1; $i <= $count_project; ++$i) {
            $this->projects[] = $this->getReference('project_' . $i);
        }

        $count_user = 6;

        $this->users = [];

        for ($i = 1; $i <= $count_user; ++$i) {
            $this->users[] = $this->getReference('user_' . $i);
        }

        $count_client = 29;

        $this->clients = [];

        for ($i = 1; $i <= $count_client; ++$i) {
            $this->clients[] = $this->getReference('client_' . $i);
        }

        foreach ($this->getDatas() as $com) {
            /** @var Product $p */
            $p = $com['product'];

            $commission = new Sales();
            $commission->setUser($com['user']);
            $commission->setProduct($com['product']);
            $commission->setContact($com['client']);
            $commission->setDate(new \DateTime());
            $commission->setPrice($com['price']);
            $commission->setPercentVr($p->getPercentVr());
            $commission->setPercentCom(15);
            $commission->setPercentComType('percent_pv');
            $commission->setPercentVrType('percent');

            $manager->persist($commission);
        }

        $manager->flush();

        /*
                foreach ($projects as $project) {
                    foreach ($project->getProductEvent() as $productEvent) {
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
                    }

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
        **/
        $manager->flush();
    }

    public function getDatas(): array
    {
        return [
            [
                'client' => $this->clients[0]->getContact()[random_int(0, \count($this->clients[0]->getContact()) - 1)],
                'user' => $this->users[0],
                'product' => $this->projects[13]->getProductPackage()[0],
                'price' => 585,
            ],
            [
                'client' => $this->clients[1]->getContact()[random_int(0, \count($this->clients[1]->getContact()) - 1)],
                'user' => $this->users[0],
                'product' => $this->projects[13]->getProductPackage()[1],
                'price' => 1290,
            ],
            [
                'client' => $this->clients[2]->getContact()[random_int(0, \count($this->clients[2]->getContact()) - 1)],
                'user' => $this->users[0],
                'product' => $this->projects[13]->getProductPackage()[0],
                'price' => 2340,
            ],
            [
                'client' => $this->clients[3]->getContact()[random_int(0, \count($this->clients[3]->getContact()) - 1)],
                'user' => $this->users[0],
                'product' => $this->projects[20]->getProductSponsoring()[0],
                'price' => 12600,
            ],
            [
                'client' => $this->clients[4]->getContact()[random_int(0, \count($this->clients[4]->getContact()) - 1)],
                'user' => $this->users[0],
                'product' => $this->projects[20]->getProductSponsoring()[1],
                'price' => 2000,
            ],
            [
                'client' => $this->clients[5]->getContact()[random_int(0, \count($this->clients[5]->getContact()) - 1)],
                'user' => $this->users[0],
                'product' => $this->projects[20]->getProductSponsoring()[1],
                'price' => 30000,
            ],
            [
                'client' => $this->clients[6]->getContact()[random_int(0, \count($this->clients[6]->getContact()) - 1)],
                'user' => $this->users[0],
                'product' => $this->projects[20]->getProductSponsoring()[1],
                'price' => 2500,
            ],
            [
                'client' => $this->clients[7]->getContact()[random_int(0, \count($this->clients[7]->getContact()) - 1)],
                'user' => $this->users[0],
                'product' => $this->projects[20]->getProductSponsoring()[1],
                'price' => 18500,
            ],
            [
                'client' => $this->clients[8]->getContact()[random_int(0, \count($this->clients[8]->getContact()) - 1)],
                'user' => $this->users[0],
                'product' => $this->projects[20]->getProductSponsoring()[1],
                'price' => 1500,
            ],
            [
                'client' => $this->clients[28]->getContact()[random_int(0, \count($this->clients[28]->getContact()) - 1)],
                'user' => $this->users[2],
                'product' => $this->projects[25]->getProductSponsoring()[0],
                'price' => 1500,
            ],
        ];
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            ProjectsFixtures::class,
            ClientsFixtures::class,
        ];
    }
}
