<?php

declare(strict_types=1);

namespace App\Validator;

use App\Entity\ProductPackageVip;
use App\Entity\ProductSponsoring;
use App\Entity\Sales;
use App\Repository\SalesRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class MaxProductSalesValidator extends ConstraintValidator
{
    public function __construct(private SalesRepository $salesRepository)
    {
    }

    /**
     * @param Sales $sales
     */
    public function validate($sales, Constraint $constraint): void
    {
        if (!$constraint instanceof MaxProductSales) {
            return;
        }

        if (!($sales->getProduct() instanceof ProductSponsoring) && !($sales->getProduct() instanceof ProductPackageVip)) {
            return;
        }

        /** @var ProductSponsoring|ProductPackageVip $product */
        $product = $sales->getProduct();

        if (null === $product->getQuantityMax() || $product->getQuantityMax() <= 0) {
            return;
        }

        $quantity_sales = $this->salesRepository->getQuantitySaleByProduct($product);
        if (null !== $product->getQuantityMax() && $quantity_sales + $sales->getQuantity() > $product->getQuantityMax()) {
            $this->context
                ->buildViolation(sprintf($constraint->maxSalesExceededMessage, $quantity_sales, $product->getQuantityMax()))
                ->atPath('Sales.quantity')
                ->addViolation();
        }
    }
}
