<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\BaseSales;
use App\Entity\Project;
use App\Entity\User;
use App\Repository\BaseSalesRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;
use Symfony\Contracts\Translation\TranslatorInterface;

class SalesService
{
    public function __construct(
       private  BaseSalesRepository $baseSalesRepository,
    ) {
    }


    public function validate(int $sale_id): void
    {
        $sale = $this->baseSalesRepository->find($sale_id);
        $sale->setValidate(true);
        $this->baseSalesRepository->save($sale, true);

    }

    public function delete(int $sale_id): void
    {
        $sale = $this->baseSalesRepository->find($sale_id);
        $this->baseSalesRepository->remove($sale, true);
    }
}
