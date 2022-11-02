<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\ResetPasswordRequest;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

/**
 * @group unit
 */
class ResetPasswordRequestTest extends TestCase
{
    public function testId(): void
    {
        $entity = $this->getEntityInstance();

        self::assertNull($entity->getId());
        $reflection = new \ReflectionClass($entity);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($entity, 2);
        self::assertSame(2, $entity->getId());
    }

    public function testUser(): void
    {
        $entity = $this->getEntityInstance();
        self::assertNotNull($entity->getUser());
        self::assertInstanceOf(User::class, $entity->getUser());
    }

    public function testExpiresAt(): void
    {
        $entity = $this->getEntityInstance();
        self::assertNotNull($entity->getExpiresAt());
        self::assertInstanceOf(\DateTimeImmutable::class, $entity->getExpiresAt());
        self::assertSame(
            '2099-01-01 00:00',
            $entity->getExpiresAt()->format('Y-m-d H:i')
        );
    }

    public function testRequestedAt(): void
    {
        $entity = $this->getEntityInstance();
        self::assertNotNull($entity->getRequestedAt());
        self::assertInstanceOf(\DateTimeImmutable::class, $entity->getRequestedAt());
    }

    public function testHashedToken(): void
    {
        $entity = $this->getEntityInstance();
        self::assertNotNull($entity->getHashedToken());
        self::assertIsString($entity->getHashedToken());
    }

    public function getEntityInstance(): ResetPasswordRequest
    {
        $user = new User();
        $expiresAt = new \DateTimeImmutable('2099-01-01 00:00:00');

        return new ResetPasswordRequest(
            $user,
            $expiresAt,
            'selector',
            'hashed token'
        );
    }
}
