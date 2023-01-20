<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\ProductEvent;
use App\Entity\Project;
use App\Repository\ProductEventRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<ProductEvent>
 *
 * @method static ProductEvent|Proxy                     createOne(array $attributes = [])
 * @method static ProductEvent[]|Proxy[]                 createMany(int $number, array|callable $attributes = [])
 * @method static ProductEvent|Proxy                     find(object|array|mixed $criteria)
 * @method static ProductEvent|Proxy                     findOrCreate(array $attributes)
 * @method static ProductEvent|Proxy                     first(string $sortedField = 'id')
 * @method static ProductEvent|Proxy                     last(string $sortedField = 'id')
 * @method static ProductEvent|Proxy                     random(array $attributes = [])
 * @method static ProductEvent|Proxy                     randomOrCreate(array $attributes = [])
 * @method static ProductEvent[]|Proxy[]                 all()
 * @method static ProductEvent[]|Proxy[]                 findBy(array $attributes)
 * @method static ProductEvent[]|Proxy[]                 randomSet(int $number, array $attributes = [])
 * @method static ProductEvent[]|Proxy[]                 randomRange(int $min, int $max, array $attributes = [])
 * @method static ProductEventRepository|RepositoryProxy repository()
 * @method        ProductEvent|Proxy                     create(array|callable $attributes = [])
 */
final class ProductEventFactory extends ModelFactory
{
    public function __construct()
    {
        parent::__construct();
    }

    public function project(Project $project)
    {
        return $this->addState([
            'project' => $project,
        ]);
    }

    protected function getDefaults(): array
    {
        return [
            'name' => self::faker()->sentence(random_int(1, 3)),
            'date' => new \DateTime(self::faker()->date()),
            'percent_vr' => random_int(15, 40),
        ];
    }

    protected function initialize(): self
    {
        // see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
        return $this
            ->afterInstantiate(function (ProductEvent $productEvent) {
                $productEvent->setName($this->clean($productEvent->getName()));
            });
    }

    protected static function getClass(): string
    {
        return ProductEvent::class;
    }

    private function clean(string $string): string
    {
        return preg_replace('/[^A-Za-z0-9\- ]/', '', $string); // Removes special chars.
    }
}
