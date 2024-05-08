<?php

namespace App\Twig\Components\Projects;

use App\Controller\Admin\DashboardController;
use App\Controller\Admin\ProjectCrudController;
use App\Entity\ProductPackageVip;
use App\Entity\Project;
use App\Form\Type\NewProductPackageType;
use App\Repository\ProductPackageVipRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
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

#[AsLiveComponent('create_package_component', template: 'admin/project/create_package_component.html.twig')]
class CreatePackageComponent extends AbstractController
{
    use DefaultActionTrait;
    use ComponentWithFormTrait;
    use LiveCollectionTrait;

    #[LiveProp]
    public Project $project;

    public function __construct(private ProductPackageVipRepository $productEventRepository, private RequestStack $requestStack, private AdminUrlGenerator $adminUrlGenerator)
    {
    }

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(NewProductPackageType::class);
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
            if($form->get('type_com')->getData() === 'percent') {
                $event->setPercentVr($form->get('com1')->getData() * 100);
            } else {
                $event->setPercentVr( ($form->get('com2')->get('pv')->getData() - $form->get('com2')->get('pa')->getData() )/ $form->get('com2')->get('pa')->getData() * 100);
            }
            $this->productEventRepository->save($event, true);
        }

        $this->addFlash('success', 'Evènement créé avec succès !');

        return $this->redirect(
            $this->adminUrlGenerator->setDashboard(DashboardController::class)->setController(ProjectCrudController::class)->setAction(Action::DETAIL)->setEntityId($this->project->getId())->generateUrl())
        ;
    }

    private function getNewProductEvent(): ProductPackageVip
    {
        $event = new ProductPackageVip();
        $event->setProject($this->project);
        $event->setPercentFreelance($this->form->get('percentFreelance')->getData() * 100);
        $event->setPercentTv($this->form->get('percentTv')->getData() * 100);
        $event->setPercentSalarie($this->form->get('percentSalarie')->getData() * 100);
        $event->setName($this->form->get('name')->getData());
        $event->setDescription($this->form->get('description')->getData());
        return $event;
    }
}