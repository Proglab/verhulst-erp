<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\ProductSponsoring;
use App\Entity\Project;
use App\Repository\ProductSponsoringRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<ProductSponsoring>
 *
 * @method static ProductSponsoring|Proxy                     createOne(array $attributes = [])
 * @method static ProductSponsoring[]|Proxy[]                 createMany(int $number, array|callable $attributes = [])
 * @method static ProductSponsoring|Proxy                     find(object|array|mixed $criteria)
 * @method static ProductSponsoring|Proxy                     findOrCreate(array $attributes)
 * @method static ProductSponsoring|Proxy                     first(string $sortedField = 'id')
 * @method static ProductSponsoring|Proxy                     last(string $sortedField = 'id')
 * @method static ProductSponsoring|Proxy                     random(array $attributes = [])
 * @method static ProductSponsoring|Proxy                     randomOrCreate(array $attributes = [])
 * @method static ProductSponsoring[]|Proxy[]                 all()
 * @method static ProductSponsoring[]|Proxy[]                 findBy(array $attributes)
 * @method static ProductSponsoring[]|Proxy[]                 randomSet(int $number, array $attributes = [])
 * @method static ProductSponsoring[]|Proxy[]                 randomRange(int $min, int $max, array $attributes = [])
 * @method static ProductSponsoringRepository|RepositoryProxy repository()
 * @method        ProductSponsoring|Proxy                     create(array|callable $attributes = [])
 */
final class ProductSponsorFactory extends ModelFactory
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
            'percent_vr' => random_int(15, 40),
            'date_begin' => new \DateTime(self::faker()->date()),
            'date_end' => new \DateTime(self::faker()->date()),
        ];
    }

    protected function initialize(): self
    {
        // see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
        return $this
            ->afterInstantiate(function (ProductSponsoring $product) {
                $product->setName($this->clean($product->getName()));
            });
    }

    protected static function getClass(): string
    {
        return ProductSponsoring::class;
    }

    private function clean(string $string): string
    {
        return preg_replace('/[^A-Za-z0-9\- ]/', '', $string); // Removes special chars.
    }
}
