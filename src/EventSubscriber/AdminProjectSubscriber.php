<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\Commission;
use App\Entity\Product;
use App\Entity\Project;
use App\Entity\User;
use App\Repository\CommissionRepository;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\Collection;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityDeletedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AdminProjectSubscriber implements EventSubscriberInterface
{
    public function __construct(private UserRepository $userRepository, private CommissionRepository $commissionRepository)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            BeforeEntityDeletedEvent::class => ['beforeEntityDeletedEvent'],
            AfterEntityPersistedEvent::class => ['afterEntityPersistedEvent'],
        ];
    }

    public function afterEntityPersistedEvent(AfterEntityPersistedEvent $event): void
    {
        $entity = $event->getEntityInstance();
        if (!($entity instanceof Project)) {
            return;
        }

        $users = $this->userRepository->getCommercials();
        /** @var User $user */
        foreach ($users as $user) {
            foreach ($entity->getProductEvent() as $event) {
                $com = new Commission();
                $com->setUser($user);
                $com->setProduct($event);
                if ($user->isFreelance()) {
                    $com->setPercentCom($event->getPercentFreelance());
                } else {
                    $com->setPercentCom($event->getPercentSalarie());
                }
                $this->commissionRepository->save($com, true);
            }
            foreach ($entity->getProductPackage() as $event) {
                $com = new Commission();
                $com->setUser($user);
                $com->setProduct($event);
                if ($user->isFreelance()) {
                    $com->setPercentCom($event->getPercentFreelance());
                } else {
                    $com->setPercentCom($event->getPercentSalarie());
                }
                $this->commissionRepository->save($com, true);
            }
            foreach ($entity->getProductSponsoring() as $event) {
                $com = new Commission();
                $com->setUser($user);
                $com->setProduct($event);
                if ($user->isFreelance()) {
                    $com->setPercentCom($event->getPercentFreelance());
                } else {
                    $com->setPercentCom($event->getPercentSalarie());
                }
                $this->commissionRepository->save($com, true);
            }
            foreach ($entity->getProductDivers() as $event) {
                $com = new Commission();
                $com->setUser($user);
                $com->setProduct($event);
                if ($user->isFreelance()) {
                    $com->setPercentCom($event->getPercentFreelance());
                } else {
                    $com->setPercentCom($event->getPercentSalarie());
                }
                $this->commissionRepository->save($com, true);
            }
        }
    }

    public function beforeEntityDeletedEvent(BeforeEntityDeletedEvent $event): void
    {
        /** @var Project $entity */
        $entity = $event->getEntityInstance();

        if (!($entity instanceof Project)) {
            return;
        }

        $this->deleteDocs($entity->getProductSponsoring());
        $this->deleteDocs($entity->getProductEvent());
        $this->deleteDocs($entity->getProductDivers());
        $this->deleteDocs($entity->getProductPackage());
    }

    /**
     * @param Product $product
     *
     * @return void
     */
    private function deleteDoc($product)
    {
        if (null !== $product->getDoc()) {
            unlink($product->getUrl());
        }
    }

    /**
     * @return void
     */
    private function deleteDocs(Collection $products)
    {
        foreach ($products as $product) {
            $this->deleteDoc($product);
        }
    }
}
