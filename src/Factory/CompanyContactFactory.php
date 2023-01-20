<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Company;
use App\Entity\CompanyContact;
use App\Repository\CompanyContactRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<>
 *
 * @method static CompanyContact|Proxy                     createOne(array $attributes = [])
 * @method static CompanyContact[]|Proxy[]                 createMany(int $number, array|callable $attributes = [])
 * @method static CompanyContact|Proxy                     find(object|array|mixed $criteria)
 * @method static CompanyContact|Proxy                     findOrCreate(array $attributes)
 * @method static CompanyContact|Proxy                     first(string $sortedField = 'id')
 * @method static CompanyContact|Proxy                     last(string $sortedField = 'id')
 * @method static CompanyContact|Proxy                     random(array $attributes = [])
 * @method static CompanyContact|Proxy                     randomOrCreate(array $attributes = [])
 * @method static CompanyContact[]|Proxy[]                 all()
 * @method static CompanyContact[]|Proxy[]                 findBy(array $attributes)
 * @method static CompanyContact[]|Proxy[]                 randomSet(int $number, array $attributes = [])
 * @method static CompanyContact[]|Proxy[]                 randomRange(int $min, int $max, array $attributes = [])
 * @method static CompanyContactRepository|RepositoryProxy repository()
 * @method        CompanyContact|Proxy                     create(array|callable $attributes = [])
 */
final class CompanyContactFactory extends ModelFactory
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function getDefaults(): array
    {
        return [
            'firstname' => self::faker()->firstName(),
            'lastname' => self::faker()->lastName(),
            'lang' => self::faker()->randomElement(['fr', 'nl', 'en']),
            'email' => self::faker()->email(),
            'phone' => self::faker()->phoneNumber(),
            'company' => null
        ];
    }

    public function company(Company $company): self
    {
        return $this->addState([
            'company' => $company,
        ]);
    }


    protected static function getClass(): string
    {
        return CompanyContact::class;
    }

    private function clean(string $string): string
    {
        return preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
    }
}
