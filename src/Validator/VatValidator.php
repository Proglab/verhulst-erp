<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class VatValidator extends ConstraintValidator
{
    public function __construct(private readonly HttpClientInterface $client)
    {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (null === $value || '' === $value) {
            return;
        }

        $response = $this->client->request(
            'GET',
            'https://anyapi.io/api/v1/vat/validate?apiKey=ek75v4rketuajelspci85hu3mlt9jeo7r6grg1fcoal4t13vhrq8&vat_number=' . $value
        );

        $response = json_decode($response->getContent());

        if (true === $response->valid && true === $response->validFormat) {
            return;
        }

        /** @var Vat $const * */
        $const = $constraint;

        $this->context->buildViolation($const->message)
            ->setParameter('{{ value }}', $value)
            ->addViolation();
    }
}
