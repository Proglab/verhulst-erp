<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\Commission;
use App\Entity\Product;
use App\Entity\Project;
use App\Entity\User;
use App\Repository\CommissionRepository;
use App\Repository\ProjectRepository;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\Collection;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityDeletedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityUpdatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AdminProjectSubscriber implements EventSubscriberInterface
{
    public function __construct(private UserRepository $userRepository, private CommissionRepository $commissionRepository, private ProjectRepository $projectRepository)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            BeforeEntityDeletedEvent::class => ['beforeEntityDeletedEvent'],
            AfterEntityPersistedEvent::class => ['afterEntityPersistedEvent'],
            BeforeEntityUpdatedEvent::class => ['beforeEntityUpdatedEvent'],
        ];
    }

    public function createCom(User $user, Product $product, bool $flush = false): void
    {
        $com = new Commission();
        $com->setUser($user);
        $com->setProduct($product);
        switch ($user->getCom()) {
            case 'freelance':
                $com->setPercentCom($product->getPercentFreelance());
                break;
            case 'salarie':
                $com->setPercentCom($product->getPercentSalarie());
                break;
            case 'tv':
                $com->setPercentCom($product->getPercentTv());
                break;
        }
        $this->commissionRepository->save($com, $flush);
    }

    public function beforeEntityUpdatedEvent(BeforeEntityUpdatedEvent $event): void
    {
        $entity = $event->getEntityInstance();
        if (!($entity instanceof Project)) {
            return;
        }

        $users = $this->userRepository->getCommercials();
        /** @var User $user */
        foreach ($users as $user) {
            foreach ($entity->getProductEvent() as $event) {
                if (null === $event->getId()) {
                    $this->createCom($user, $event);
                }
            }
            foreach ($entity->getProductPackage() as $event) {
                if (null === $event->getId()) {
                    $this->createCom($user, $event);
                }
            }
            foreach ($entity->getProductSponsoring() as $event) {
                if (null === $event->getId()) {
                    $this->createCom($user, $event);
                }
            }
            foreach ($entity->getProductDivers() as $event) {
                if (null === $event->getId()) {
                    $this->createCom($user, $event);
                }
            }
        }

        $this->projectRepository->save($entity, true);
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
                $this->createCom($user, $event, true);
            }
            foreach ($entity->getProductPackage() as $event) {
                $this->createCom($user, $event, true);
            }
            foreach ($entity->getProductSponsoring() as $event) {
                $this->createCom($user, $event, true);
            }
            foreach ($entity->getProductDivers() as $event) {
                $this->createCom($user, $event, true);
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
            if (file_exists($product->getUrl())) {
                unlink($product->getUrl());
            }
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
