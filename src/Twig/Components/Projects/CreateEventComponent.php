<?php

namespace App\Twig\Components\Projects;

use App\Entity\Budget\Event;
use App\Entity\ProductEvent;
use App\Entity\Project;
use App\Entity\Todo;
use App\Entity\User;
use App\Form\Type\NewProductEventType;
use App\Form\Type\NewProjectType;
use App\Repository\ProductEventRepository;
use App\Repository\ProjectRepository;
use App\Repository\SalesRepository;
use EasyCorp\Bundle\EasyAdminBundle\Exception\ForbiddenActionException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\LiveComponent\LiveCollectionTrait;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsLiveComponent('create_event_component', template: 'admin/project/create_event_component.html.twig')]
class CreateEventComponent extends AbstractController
{
    use DefaultActionTrait;
    use ComponentWithFormTrait;
    use LiveCollectionTrait;


    #[LiveProp]
    public Project $project;

    public function __construct(private ProductEventRepository $productEventRepository, private RequestStack $requestStack)
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

        if ($typeDAte === 'date') {
            if ($form->get('dates')->get('create_all_date')->getData() === true) {
                for ($date = $form->get('dates')->get('date_begin')->getData(); $form->get('dates')->get('date_end')->getData() >= $date; $date->modify('+1 day')) {
                    /** @var ProductEvent $event */
                    $event = $this->getNewProductEvent();
                    $event->setDateBegin(new \DateTime($date->format('Y-m-d')));
                    $event->setDateEnd(new \DateTime($date->format('Y-m-d')));
                    $events[] = $event;
                }
            } else {
                /** @var ProductEvent $event */
                $event = $this->getNewProductEvent();
                $event->setProject($this->project);
                $event->setDateBegin($form->get('dates')->get('date_begin')->getData());
                $event->setDateEnd($form->get('dates')->get('date_end')->getData());
                $events[] = $event;
            }
        } else {
            foreach ($form->get('dates2') as $date) {
                /** @var ProductEvent $event */
                $event = $this->getNewProductEvent();
                $event->setProject($this->project);
                $event->setDateBegin($date->getData());
                $event->setDateEnd($date->getData());
                $events[] = $event;
            }
        }

        foreach ($events as $event) {
            if($form->get('type_com')->getData() === 'percent') {
                $event->setPercentVr($form->get('com1')->getData() * 100);
            } else {
                $event->setPercentVr( ($form->get('com2')->get('pv')->getData() - $form->get('com2')->get('pa')->getData() )/ $form->get('com2')->get('pa')->getData() * 100);
            }
            $this->productEventRepository->save($event, true);
        }

        $this->addFlash('success', 'Evènement créé avec succès !');

        return $this->redirectToRoute('project_details', ['project' => $event->getProject()->getId()]);
    }

    private function getNewProductEvent(): ProductEvent
    {
        $event = new ProductEvent();
        $event->setProject($this->project);
        $event->setPercentFreelance($this->form->get('percentFreelance')->getData() * 100);
        $event->setPercentTv($this->form->get('percentTv')->getData() * 100);
        $event->setPercentSalarie($this->form->get('percentSalarie')->getData() * 100);
        $event->setName($this->form->get('name')->getData());
        $event->setDescription($this->form->get('description')->getData());
        return $event;
    }
}