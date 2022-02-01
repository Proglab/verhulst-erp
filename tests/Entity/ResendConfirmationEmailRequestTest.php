<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\ResendConfirmationEmailRequest;
use App\Entity\User;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @group unit
 */
class ResendConfirmationEmailRequestTest extends TestCase
{
    public function testId(): void
    {
        $entity = new ResendConfirmationEmailRequest();
        self::assertNull($entity->getId());
        $reflection = new ReflectionClass($entity);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($entity, 2);
        self::assertSame(2, $entity->getId());
    }

    public function testHashedToken(): void
    {
        $entity = new ResendConfirmationEmailRequest();
        self::assertNull($entity->getHashedToken());
        $entity->setHashedToken('hashed token');
        self::assertSame('hashed token', $entity->getHashedToken());
    }

    public function testRequestAt(): void
    {
        $entity = new ResendConfirmationEmailRequest();
        self::assertNotNull($entity->getRequestedAt());
        self::assertInstanceOf(\DateTimeImmutable::class, $entity->getRequestedAt());
        $requestAt = new \DateTimeImmutable('2021-01-15 00:00:00');
        $entity->setRequestedAt($requestAt);
        self::assertSame($requestAt, $entity->getRequestedAt());
    }

    public function testExpiresAt(): void
    {
        $entity = new ResendConfirmationEmailRequest();
        self::assertNull($entity->getExpiresAt());
        $requestAt = new \DateTimeImmutable('2021-01-16 00:00:00');
        $entity->setExpiresAt($requestAt);
        self::assertSame($requestAt, $entity->getExpiresAt());
    }

    public function testUser(): void
    {
        $entity = new ResendConfirmationEmailRequest();
        self::assertNull($entity->getUser());
        $user = new User();
        $entity->setUser($user);
        self::assertSame($user, $entity->getUser());
    }

    public function testIsExpired(): void
    {
        $entity = new ResendConfirmationEmailRequest();
        $entity->setExpiresAt(new \DateTimeImmutable('1970-01-01 00:00:00'));
        self::assertTrue($entity->isExpired());

        $entity->setExpiresAt(new \DateTimeImmutable('2999-01-01 00:00:00'));
        self::assertFalse($entity->isExpired());
    }
}
