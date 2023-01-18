<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Commission;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class CommissionCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Commission::class;
    }
}
