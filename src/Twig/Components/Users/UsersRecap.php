<?php

declare(strict_types=1);

namespace App\Twig\Components\Users;

use App\Entity\BaseSales;
use App\Entity\Project;
use App\Entity\User;
use App\Form\Type\SalesRecapFilters;
use App\Form\Type\SalesRecapUsersFilterType;
use App\Repository\BaseSalesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('users_recap', template: 'app/dashboard/recap/component.html.twig')]
class UsersRecap extends AbstractController
{
    use DefaultActionTrait;
    use ComponentWithFormTrait;
    #[LiveProp(writable: true, url: true)]
    public int $page = 1;

    #[LiveProp(writable: true, format: 'Y-m-d')]
    public ?\DateTime $date_begin = null;

    #[LiveProp(writable: true, format: 'Y-m-d')]
    public ?\DateTime $date_end = null;

    #[LiveProp(writable: true)]
    public ?Project $project = null;

    #[LiveProp(writable: true)]
    public ?string $type = null;

    #[LiveProp(writable: true)]
    public ?User $user = null;


    public function __construct(private BaseSalesRepository $salesRepository)
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

    public function getSales()
    {
        return $this->salesRepository->getSalesByMonthByUser($this->date_begin, $this->date_end, $this->user, $this->project, $this->type);
    }

    protected function instantiateForm(): FormInterface
    {
        $sale = new BaseSales();
        $sale->setUser($this->getUser());
        return $this->createForm(SalesRecapUsersFilterType::class, $sale);
    }
}
