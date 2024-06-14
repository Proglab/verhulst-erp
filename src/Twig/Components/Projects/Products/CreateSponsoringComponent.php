<?php

declare(strict_types=1);

namespace App\Twig\Components\Projects\Products;

use App\Entity\ProductSponsoring;
use App\Entity\Project;
use App\Form\Type\NewProductSponsoringType;
use App\Repository\ProductSponsoringRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\LiveComponent\LiveCollectionTrait;

#[AsLiveComponent('create_sponsoring_component', template: 'app/projects/products/components/create_sponsoring_component.html.twig')]
class CreateSponsoringComponent extends AbstractController
{
    use ComponentWithFormTrait;
    use DefaultActionTrait;
    use LiveCollectionTrait;

    #[LiveProp]
    public Project $project;

    public function __construct(private ProductSponsoringRepository $productSponsoringRepository)
    {
    }

    #[LiveAction]
    public function save(): RedirectResponse
    {
        $this->submitForm();

        $form = $this->getForm();
        $typeDAte = $form->get('type_date')->getData();

        $events = [];

        if ('date' === $typeDAte) {
            if (true === $form->get('dates')->get('create_all_date')->getData()) {
                for ($date = $form->get('dates')->get('date_begin')->getData(); $form->get('dates')->get('date_end')->getData() >= $date; $date->modify('+1 day')) {
                    $event = $this->getNewProductEvent();
                    $event->setDateBegin(new \DateTime($date->format('Y-m-d')));
                    $event->setDateEnd(new \DateTime($date->format('Y-m-d')));
                    $events[] = $event;
                }
            } else {
                $event = $this->getNewProductEvent();
                $event->setDateBegin($form->get('dates')->get('date_begin')->getData());
                $event->setDateEnd($form->get('dates')->get('date_end')->getData());
                $events[] = $event;
            }
        } else {
            foreach ($form->get('dates2') as $date) {
                $event = $this->getNewProductEvent();
                $event->setDateBegin($date->getData());
                $event->setDateEnd($date->getData());
                $events[] = $event;
            }
        }

        foreach ($events as $event) {
            // quantity max
            $event->setQuantityMax($form->get('quantityMax')->getData());
            // percents commerciaux
            if ('other' === $form->get('percentFreelance')->getData()) {
                $event->setPercentFreelance((string) $form->get('percentFreelanceCustom')->getData());
            } else {
                $event->setPercentFreelance((string) $form->get('percentFreelance')->getData());
            }
            if ('other' === $form->get('percentSalarie')->getData()) {
                $event->setPercentSalarie((string) $form->get('percentSalarieCustom')->getData());
            } else {
                $event->setPercentSalarie((string) $form->get('percentSalarie')->getData());
            }
            if ('other' === $form->get('percentTv')->getData()) {
                $event->setPercentTv((string) $form->get('percentTvCustom')->getData());
            } else {
                $event->setPercentTv((string) $form->get('percentTv')->getData());
            }

            // prices
            if ('percent' === $form->get('type_com')->getData()) {
                $event->setPercentVr($form->get('com1')->get('percent_vr')->getData() * 100);
                $event->setCa($form->get('com1')->get('pv')->getData());
                $event->setPa($form->get('com1')->get('pv')->getData() - $form->get('com1')->get('pv')->getData() * $form->get('com1')->get('percent_vr')->getData());
            } else {
                $event->setPercentVr(($form->get('com2')->get('pv')->getData() - $form->get('com2')->get('pa')->getData()) / $form->get('com2')->get('pa')->getData() * 100);
                $event->setCa($form->get('com2')->get('pv')->getData());
                $event->setPa($form->get('com2')->get('pa')->getData());
            }

            $this->productSponsoringRepository->save($event, true);
        }
        $this->addFlash('success', 'Sponsoring créé avec succès !');

        return $this->redirect(
            $this->generateUrl('project_details', ['project' => $this->project->getId()]));
    }

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(NewProductSponsoringType::class);
    }

    private function getNewProductEvent(): ProductSponsoring
    {
        $event = new ProductSponsoring();
        $event->setProject($this->project);
        $event->setName($this->form->get('name')->getData());
        $event->setDescription($this->form->get('description')->getData());

        return $event;
    }
}
