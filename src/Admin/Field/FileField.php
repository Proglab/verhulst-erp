<?php

declare(strict_types=1);

namespace App\Admin\Field;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\FieldTrait;
use EasyCorp\Bundle\EasyAdminBundle\Form\Type\FileUploadType;

final class FileField implements FieldInterface
{
    use FieldTrait;

    public static function new(string $propertyName, string $label = null, array $fieldsConfig = []): self
    {
        return (new self())
            ->setProperty($propertyName)
            ->setLabel($label)
            ->setFormType(FileUploadType::class)
            ->setFormTypeOptions(
                $fieldsConfig
            );
    }
}
