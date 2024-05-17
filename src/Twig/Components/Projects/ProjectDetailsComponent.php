<?php

namespace App\Twig\Components\Projects;

use App\Entity\Product;
use App\Entity\Project;
use App\Entity\Todo;
use App\Entity\User;
use App\Form\Type\NewProjectType;
use App\Repository\ProductRepository;
use App\Repository\ProjectRepository;
use App\Repository\SalesRepository;
use EasyCorp\Bundle\EasyAdminBundle\Exception\ForbiddenActionException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsLiveComponent('project_detail', template: 'app/projects/details_component.html.twig')]
class ProjectDetailsComponent
{
    use DefaultActionTrait;
    use ComponentToolsTrait;

    #[LiveProp]
    public Project $project;


    #[LiveProp]
    public string $locale;



    #[LiveProp]
    public Product|null $toDelete = null;

    public function __construct(private ProjectRepository $projectRepository, private ProductRepository $productRepository)
    {
    }

    #[LiveAction]
    public function delete(#[LiveArg] int $id): void
    {
        $this->toDelete = $this->productRepository->find($id);
        $this->dispatchBrowserEvent('modal:open');
    }
    #[LiveAction]
    public function deleteConfirm(): void
    {
        $this->productRepository->remove($this->toDelete, true);
        $this->toDelete = null;
        $this->dispatchBrowserEvent('modal:close');
    }
}