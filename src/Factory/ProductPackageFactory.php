<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\ProductPackageVip;
use App\Entity\Project;
use App\Repository\ProductPackageVipRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<ProductPackageVip>
 *
 * @method static ProductPackageVip|Proxy                     createOne(array $attributes = [])
 * @method static ProductPackageVip[]|Proxy[]                 createMany(int $number, array|callable $attributes = [])
 * @method static ProductPackageVip|Proxy                     find(object|array|mixed $criteria)
 * @method static ProductPackageVip|Proxy                     findOrCreate(array $attributes)
 * @method static ProductPackageVip|Proxy                     first(string $sortedField = 'id')
 * @method static ProductPackageVip|Proxy                     last(string $sortedField = 'id')
 * @method static ProductPackageVip|Proxy                     random(array $attributes = [])
 * @method static ProductPackageVip|Proxy                     randomOrCreate(array $attributes = [])
 * @method static ProductPackageVip[]|Proxy[]                 all()
 * @method static ProductPackageVip[]|Proxy[]                 findBy(array $attributes)
 * @method static ProductPackageVip[]|Proxy[]                 randomSet(int $number, array $attributes = [])
 * @method static ProductPackageVip[]|Proxy[]                 randomRange(int $min, int $max, array $attributes = [])
 * @method static ProductPackageVipRepository|RepositoryProxy repository()
 * @method        ProductPackageVip|Proxy                     create(array|callable $attributes = [])
 */
final class ProductPackageFactory extends ModelFactory
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
            'ca' => self::faker()->randomFloat(2, 100, 1000000),
            'percent_vr' => random_int(15, 40),
        ];
    }

    protected function initialize(): self
    {
        // see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
        return $this
            ->afterInstantiate(function (ProductPackageVip $productEvent) {
                $productEvent->setName($this->clean($productEvent->getName()));
            });
    }

    protected static function getClass(): string
    {
        return ProductPackageVip::class;
    }

    private function clean(string $string): string
    {
        return preg_replace('/[^A-Za-z0-9\- ]/', '', $string); // Removes special chars.
    }
}
