<?php

declare(strict_types=1);

namespace App\Twig\Components\FlashSales;

use App\Entity\CompanyContact;
use App\Entity\FastSales;
use App\Entity\User;
use App\Form\Type\NewFlashSaleType;
use App\Repository\FastSalesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('create_flash_sale', template: 'app/sales/flash/create_flash_sale.html.twig')]
class CreateFlashSale extends AbstractController
{
    use ComponentWithFormTrait;
    use DefaultActionTrait;

    #[LiveProp]
    public ?CompanyContact $contact = null;

    public function __construct(private FastSalesRepository $fastSalesRepository)
    {
    }

    #[LiveAction]
    public function save(): RedirectResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $this->formValues['contact'] = $this->contact->getId();
        $this->formValues['user'] = $user->getId();

        $this->submitForm();
        /** @var FastSales $flashSaleData */
        $flashSaleData = $this->getForm()->getData();
        $flashSaleData->setPercentVr($flashSaleData->getPercentVr() * 100);
        $flashSaleData->setPercentCom($flashSaleData->getPercentCom() * 100);

        if ('fixed' === $flashSaleData->getPercentVrType()) {
            $flashSaleData->setPercentVrEur((string) ($flashSaleData->getPrice() - $flashSaleData->getPa()));
        }

        $this->fastSalesRepository->save($flashSaleData, true);

        return $this->redirectToRoute('sales_flash_index');
    }

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(NewFlashSaleType::class);
    }
}
