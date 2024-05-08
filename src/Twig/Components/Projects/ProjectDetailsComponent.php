<?php

namespace App\Twig\Components\Projects;

use App\Entity\Product;
use App\Entity\ProductEvent;
use App\Entity\ProductPackageVip;
use App\Entity\Project;
use App\Entity\Todo;
use App\Entity\User;
use App\Form\Type\NewProjectType;
use App\Repository\ProductDiversRepository;
use App\Repository\ProductEventRepository;
use App\Repository\ProductPackageVipRepository;
use App\Repository\ProductRepository;
use App\Repository\ProductSponsoringRepository;
use App\Repository\ProjectRepository;
use App\Repository\SalesRepository;
use EasyCorp\Bundle\EasyAdminBundle\Exception\ForbiddenActionException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Asset\Package;
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

    #[LiveProp(writable: true)]
    public ?string $queryEvent = null;
    #[LiveProp(writable: true)]
    public ?string $queryPackage = null;
    #[LiveProp(writable: true)]
    public ?string $querySponsoring = null;
    #[LiveProp(writable: true)]
    public ?string $queryDivers = null;

    #[LiveProp]
    public Product|null $toDelete = null;

    public function __construct(private ProjectRepository $projectRepository, private ProductRepository $productRepository, private ProductEventRepository $productEventRepository, private ProductPackageVipRepository $productPackageRepository, private ProductSponsoringRepository $productSponsoringRepository, private ProductDiversRepository $productDiversRepository)
    {
    }

    public function getEvents(): array
    {
        return $this->productEventRepository->searchEventsByProject($this->project, $this->queryEvent);
    }

    public function getPackages(): array
    {
        return $this->productPackageRepository->searchEventsByProject($this->project, $this->queryPackage);
    }

    public function getSponsorings(): array
    {
        return $this->productSponsoringRepository->searchEventsByProject($this->project, $this->querySponsoring);
    }

    public function getDivers(): array
    {
        return $this->productDiversRepository->searchEventsByProject($this->project, $this->queryDivers);
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