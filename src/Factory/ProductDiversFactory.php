<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\ProductDivers;
use App\Entity\Project;
use App\Repository\ProductDiversRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<ProductDivers>
 *
 * @method static ProductDivers|Proxy                     createOne(array $attributes = [])
 * @method static ProductDivers[]|Proxy[]                 createMany(int $number, array|callable $attributes = [])
 * @method static ProductDivers|Proxy                     find(object|array|mixed $criteria)
 * @method static ProductDivers|Proxy                     findOrCreate(array $attributes)
 * @method static ProductDivers|Proxy                     first(string $sortedField = 'id')
 * @method static ProductDivers|Proxy                     last(string $sortedField = 'id')
 * @method static ProductDivers|Proxy                     random(array $attributes = [])
 * @method static ProductDivers|Proxy                     randomOrCreate(array $attributes = [])
 * @method static ProductDivers[]|Proxy[]                 all()
 * @method static ProductDivers[]|Proxy[]                 findBy(array $attributes)
 * @method static ProductDivers[]|Proxy[]                 randomSet(int $number, array $attributes = [])
 * @method static ProductDivers[]|Proxy[]                 randomRange(int $min, int $max, array $attributes = [])
 * @method static ProductDiversRepository|RepositoryProxy repository()
 * @method        ProductDivers|Proxy                     create(array|callable $attributes = [])
 */
final class ProductDiversFactory extends ModelFactory
{
    public function __construct()
    {
        parent::__construct();
    }

    public function project(Project $project): ModelFactory
    {
        return $this->addState([
            'project' => $project,
        ]);
    }

    protected function getDefaults(): array
    {
        return [
            'name' => self::faker()->sentence(random_int(1, 3)),
            'date_begin' => new \DateTime(self::faker()->date()),
            'date_end' => new \DateTime(self::faker()->date()),
            'percent_vr' => random_int(15, 40),
        ];
    }

    protected function initialize(): self
    {
        // see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
        return $this
            ->afterInstantiate(function (ProductDivers $productEvent) {
                $productEvent->setName($this->clean($productEvent->getName()));
            });
    }

    protected static function getClass(): string
    {
        return ProductDivers::class;
    }

    private function clean(string $string): string
    {
        return preg_replace('/[^A-Za-z0-9\- ]/', '', $string); // Removes special chars.
    }
}
