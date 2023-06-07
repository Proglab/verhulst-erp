<?php

declare(strict_types=1);

namespace App\Controller\Admin\Filter;

use App\Form\Type\AddedByFilterType;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Filter\FilterInterface;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FieldDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FilterDataDto;
use EasyCorp\Bundle\EasyAdminBundle\Filter\FilterTrait;

class AddedByFilter implements FilterInterface
{
    use FilterTrait;

    public static function new(string $propertyName, string $label = null): self
    {
        return (new self())
            ->setFilterFqcn(__CLASS__)
            ->setProperty($propertyName)
            ->setLabel($label)
            ->setFormType(AddedByFilterType::class);
    }

    public function apply(QueryBuilder $queryBuilder, FilterDataDto $filterDataDto, ?FieldDto $fieldDto, EntityDto $entityDto): void
    {
        if (empty($filterDataDto->getValue()->getAddedBy())) {
            $queryBuilder->andWhere('entity.added_by IS NULL');
        } else {
            $queryBuilder->andWhere('entity.added_by = :added_by')->setParameter('added_by', $filterDataDto->getValue()->getAddedBy());
        }
    }
}
