<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Company;
use App\Entity\CompanyContact;
use App\Repository\CompanyRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<Company>
 *
 * @method static Company|Proxy                     createOne(array $attributes = [])
 * @method static Company[]|Proxy[]                 createMany(int $number, array|callable $attributes = [])
 * @method static Company|Proxy                     find(object|array|mixed $criteria)
 * @method static Company|Proxy                     findOrCreate(array $attributes)
 * @method static Company|Proxy                     first(string $sortedField = 'id')
 * @method static Company|Proxy                     last(string $sortedField = 'id')
 * @method static Company|Proxy                     random(array $attributes = [])
 * @method static Company|Proxy                     randomOrCreate(array $attributes = [])
 * @method static Company[]|Proxy[]                 all()
 * @method static Company[]|Proxy[]                 findBy(array $attributes)
 * @method static Company[]|Proxy[]                 randomSet(int $number, array $attributes = [])
 * @method static Company[]|Proxy[]                 randomRange(int $min, int $max, array $attributes = [])
 * @method static CompanyRepository|RepositoryProxy repository()
 * @method        Company|Proxy                     create(array|callable $attributes = [])
 */
final class CompanyFactory extends ModelFactory
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function getDefaults(): array
    {
        return [
            'name' => self::faker()->company(),
            'street' => self::faker()->streetAddress(),
            'pc' => self::faker()->postcode(),
            'city' => self::faker()->city(),
            'country' => 'BE',
        ];
    }

    protected function initialize(): self
    {
        // see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
        return $this
            ->afterInstantiate(function (Company $company) {
                for ($i = 0; $i < random_int(1, 5); ++$i) {
                    /* @var CompanyContact $contact */
                    CompanyContactFactory::new()
                        ->company($company)
                        ->create();
                }
            })
        ;
    }

    protected static function getClass(): string
    {
        return Company::class;
    }
}
