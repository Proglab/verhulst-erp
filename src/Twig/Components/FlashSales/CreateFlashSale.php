<?php

declare(strict_types=1);

namespace App\Twig\Components\FlashSales;

use App\Entity\CompanyContact;
use App\Entity\FastSales;
use App\Form\Type\NewFlashSaleType;
use App\Repository\FastSalesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('create_flash_sale', template: 'app/sales/flash/create_flash_sale.html.twig')]
class CreateFlashSale extends AbstractController
{
    use DefaultActionTrait;
    use ComponentWithFormTrait;

    #[LiveProp]
    public ?CompanyContact $contact = null;
    public function __construct(private FastSalesRepository $fastSalesRepository)
    {
    }

    #[LiveAction]
    public function save()
    {
        $this->formValues['contact'] = $this->contact->getId();
        $this->formValues['user'] = $this->getUser()->getId();

        $this->submitForm();
        $flashSaleData = $this->getForm()->getData();

        $flashSale = new FastSales();
        $flashSale->setPercentCom($this->form->get('percent_com')->getData() * 100);
        $flashSale->setName($this->form->get('name')->getData());
        $flashSale->setPo($this->form->get('po')->getData());
        $flashSale->setUser($this->form->get('user')->getData());
        $flashSale->setContact($this->form->get('contact')->getData());
        $flashSale->setDate($this->form->get('date')->getData());

        $price = null;
        $forecastPrice = null;

        if ($this->form->get('type_vente')->getData() === '1') {
            $flashSale->setPrice($this->form->get('price')->getData());
            $price = $this->form->get('price')->getData();
        } else {
            $flashSale->setForecastPrice($this->form->get('forecast_price')->getData());
            $forecastPrice = $this->form->get('forecast_price')->getData();
        }


        if ($this->form->get('type_com')->getData() === 'percent') {
            $flashSale->setPercentVr($this->form->get('com1')->getData() * 100);
            if (!empty($price)) {
                $flashSale->setPa($price - ($price * $this->form->get('com1')->getData()));
            } else {
                $flashSale->setPa($forecastPrice - ($forecastPrice * $this->form->get('com1')->getData()));
            }
        } else {
            $flashSale->setPa($this->form->get('com1')->getData());
            if (!empty($price)) {
                $flashSale->setPercentVr(($price - $this->form->get('com1')->getData()) / $price * 100);
            } else {
                $flashSale->setPercentVr(($forecastPrice - $this->form->get('com1')->getData()) / $forecastPrice * 100);
            }
        }

        if ($this->form->has('validate')) {
            $flashSale->setValidate($this->form->get('validate')->getData());
        }

        $this->fastSalesRepository->save($flashSale, true);
        return $this->redirectToRoute('sales_flash_index');


    }

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(NewFlashSaleType::class);
    }
}
