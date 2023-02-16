<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\Product;
use App\Entity\Project;
use App\Repository\ProjectRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityDeletedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AdminProjectSubscriber implements EventSubscriberInterface
{
    public function __construct()
    {
    }

    public static function getSubscribedEvents()
    {
        return [
            BeforeEntityDeletedEvent::class => ['beforeEntityDeletedEvent'],
        ];
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
