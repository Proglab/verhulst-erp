<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\BaseSalesRepository;

class SalesService
{
    public function __construct(
        private BaseSalesRepository $baseSalesRepository,
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
