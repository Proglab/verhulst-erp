<?php
namespace App\EventSubscriber;

use App\Entity\Product;
use App\Entity\Project;
use App\Repository\ProjectRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityDeletedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityDeletedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityUpdatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AdminProjectSubscriber implements EventSubscriberInterface
{
    public function __construct(private ProjectRepository $projectRepository, private EntityManagerInterface $entityManager)
    {

    }

    public static function getSubscribedEvents()
    {
        return [
            BeforeEntityDeletedEvent::class => ['beforeEntityDeletedEvent'],
        ];
    }

    public function beforeEntityDeletedEvent(BeforeEntityDeletedEvent $event)
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
     * @return void
     */
    private function deleteDoc($product)
    {
        if ($product->getDoc() !== null) {
            unlink($product->getUrl());
        }
    }

    /**
     * @param Collection $products
     * @return void
     */
    private function deleteDocs(Collection $products)
    {
        foreach($products as $product) {
            $this->deleteDoc($product);
        }
    }
}