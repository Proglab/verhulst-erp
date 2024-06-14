<?php

declare(strict_types=1);

namespace App\Controller\Admin\Filter;

use App\Form\Type\ProjectFilterType;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Filter\FilterInterface;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FieldDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FilterDataDto;
use EasyCorp\Bundle\EasyAdminBundle\Filter\FilterTrait;

class ProjectFilter implements FilterInterface
{
    use FilterTrait;

    public static function new(string $propertyName, ?string $label = null): self
    {
        return (new self())
            ->setFilterFqcn(__CLASS__)
            ->setProperty($propertyName)
            ->setLabel($label)
            ->setFormType(ProjectFilterType::class);
    }

    public function apply(QueryBuilder $queryBuilder, FilterDataDto $filterDataDto, ?FieldDto $fieldDto, EntityDto $entityDto): void
    {
        if (!empty($filterDataDto->getValue()->getName())) {
            $queryBuilder
                ->leftJoin('entity.product', 'p')
                ->leftJoin('p.project', 'pr')
                ->andWhere('pr.name = :name')->setParameter('name', $filterDataDto->getValue()->getName());
        }
    }
}
