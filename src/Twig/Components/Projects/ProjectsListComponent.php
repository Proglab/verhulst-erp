<?php

declare(strict_types=1);

namespace App\Twig\Components\Projects;

use App\Entity\Product;
use App\Entity\Project;
use App\Form\Type\ProjectSearchFilterType;
use App\Repository\ProductPackageVipRepository;
use App\Repository\ProductRepository;
use App\Repository\ProductSponsoringRepository;
use App\Repository\ProjectRepository;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Panther\DomCrawler\Field\FormFieldTrait;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('projects_list', template: 'app/projects/components/list_component.html.twig')]
class ProjectsListComponent extends AbstractController
{
    use ComponentToolsTrait;
    use DefaultActionTrait;
    use ComponentWithFormTrait;

    #[LiveProp(writable: true)]
    public ?string $query = null;

    #[LiveProp(writable: true, format: 'Y-m-d')]
    public ?\DateTime $from = null;

    #[LiveProp(writable: true, format: 'Y-m-d')]
    public ?\DateTime $to = null;

    #[LiveProp(writable: true)]
    public bool $archived = false;

    #[LiveProp(writable: true)]
    public int $page = 1;

    public function __construct(private readonly ProjectRepository $projectRepository, private PaginatorInterface $paginator)
    {
    }

    #[LiveAction]
    public function previousPage(): void
    {
        --$this->page;
    }

    #[LiveAction]
    public function nextPage(): void
    {
        ++$this->page;
    }

    #[LiveAction]
    public function gotoPage(#[LiveArg] int $page): void
    {
        $this->page = $page;
    }

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(ProjectSearchFilterType::class);
    }

    public function getProjects(): PaginationInterface
    {
        $qb = $this->projectRepository->findProjectsQb($this->query, $this->from, $this->to, $this->archived);

        return $this->paginator->paginate($qb, $this->page, 10);
    }

    #[LiveAction]
    public function resetForm(): void
    {
        $this->resetForm();
    }

    #[LiveAction]
    public function archive(#[LiveArg] int $id): void
    {
        $project = $this->projectRepository->find($id)->setArchive(true);
        $this->projectRepository->save($project, true);
    }

    #[LiveAction]
    public function unarchive(#[LiveArg] int $id): void
    {
        $project = $this->projectRepository->find($id)->setArchive(false);
        $this->projectRepository->save($project, true);
    }
}
