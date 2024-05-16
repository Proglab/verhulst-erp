<?php

namespace App\Twig\Components\Projects\Products;

use App\Entity\ProductEvent;
use App\Entity\Project;
use App\Form\Type\NewProductEventType;
use App\Repository\ProductEventRepository;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\LiveComponent\LiveCollectionTrait;

#[AsLiveComponent('create_event_component', template: 'app/projects/products/components/create_event_component.html.twig')]
class CreateEventComponent extends AbstractController
{
    use DefaultActionTrait;
    use ComponentWithFormTrait;
    use LiveCollectionTrait;

    #[LiveProp]
    public Project $project;

    public function __construct(private ProductEventRepository $productEventRepository, private RequestStack $requestStack, private AdminUrlGenerator $adminUrlGenerator)
    {
    }

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(NewProductEventType::class);
    }

    #[LiveAction]
    public function save()
    {
        $this->submitForm();

        $form = $this->getForm();
        $typeDAte = $form->get('type_date')->getData();

        $events = [];

        // dates
        if ($typeDAte === 'date') {
            if ($form->get('dates')->get('create_all_date')->getData() === true) {
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
            // percents commerciaux
            if ($form->get('percentFreelance')->getData() === 'other') {
                $event->setPercentFreelance($form->get('percentFreelanceCustom')->getData() * 100);
            } else {
                $event->setPercentFreelance($form->get('percentFreelance')->getData() * 100);
            }
            if ($form->get('percentSalarie')->getData() === 'other') {
                $event->setPercentSalarie($form->get('percentSalarieCustom')->getData() * 100);
            } else {
                $event->setPercentSalarie($form->get('percentSalarie')->getData() * 100);
            }
            if ($form->get('percentTv')->getData() === 'other') {
                $event->setPercentTv($form->get('percentTvCustom')->getData() * 100);
            } else {
                $event->setPercentTv($form->get('percentTv')->getData() * 100);
            }

            // prices
            if($form->get('type_com')->getData() === 'percent') {
                $event->setPercentVr($form->get('com1')->get('percent_vr')->getData() * 100);
                $event->setCa($form->get('com1')->get('pv')->getData());
                $event->setPa($form->get('com1')->get('pv')->getData() - $form->get('com1')->get('pv')->getData() * $form->get('com1')->get('percent_vr')->getData());
            } else {
                $event->setPercentVr( ($form->get('com2')->get('pv')->getData() - $form->get('com2')->get('pa')->getData() )/ $form->get('com2')->get('pa')->getData() * 100);
                $event->setCa($form->get('com2')->get('pv')->getData());
                $event->setPa($form->get('com2')->get('pa')->getData());
            }

            $this->productEventRepository->save($event, true);
        }

        $this->addFlash('success', 'Evènement créé avec succès !');


        return $this->redirect(
            $this->generateUrl('project_details', ['project' => $this->project->getId()]));
    }

    private function getNewProductEvent(): ProductEvent
    {
        $event = new ProductEvent();
        $event->setProject($this->project);
        $event->setName($this->form->get('name')->getData());
        $event->setDescription($this->form->get('description')->getData());
        return $event;
    }
}