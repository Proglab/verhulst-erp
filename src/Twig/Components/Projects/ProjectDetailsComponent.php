<?php

declare(strict_types=1);

namespace App\Twig\Components\Projects;

use App\Entity\Product;
use App\Entity\Project;
use App\Repository\ProductPackageVipRepository;
use App\Repository\ProductRepository;
use App\Repository\ProductSponsoringRepository;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('project_detail', template: 'app/projects/components/details_component.html.twig')]
class ProjectDetailsComponent
{
    use ComponentToolsTrait;
    use DefaultActionTrait;

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
    public ?Product $toDelete = null;

    public function __construct(private ProductRepository $productRepository, private ProductPackageVipRepository $productPackageRepository, private ProductSponsoringRepository $productSponsoringRepository)
    {
    }

    public function getPackages(): array
    {
        return $this->productPackageRepository->searchEventsByProject($this->project, $this->queryPackage);
    }

    public function getSponsorings(): array
    {
        return $this->productSponsoringRepository->searchEventsByProject($this->project, $this->querySponsoring);
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
