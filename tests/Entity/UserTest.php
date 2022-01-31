<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\User;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @group unit
 */
class UserTest extends TestCase
{
    public function testId(): void
    {
        $entity = new User();
        self::assertNull($entity->getId());
        $reflection = new ReflectionClass($entity);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($entity, 2);
        self::assertSame(2, $entity->getId());
    }

    public function testCreatedAt(): void
    {
        $entity = new User();
        $date = new DateTimeImmutable();
        self::assertInstanceOf(DateTimeImmutable::class, $entity->getCreatedAt());
        $entity->setCreatedAt($date);
        self::assertSame($date, $entity->getCreatedAt());
    }

    public function testEnabled(): void
    {
        $entity = new User();
        self::assertFalse($entity->getEnabled());
        $entity->setEnabled(true);
        self::assertTrue($entity->getEnabled());
    }

    public function testEmail(): void
    {
        $entity = new User();
        self::assertNull($entity->getEmail());
        $entity->setEmail('email');
        self::assertSame('email', $entity->getEmail());
    }

    public function testRoles(): void
    {
        $entity = new User();
        self::assertCount(1, $entity->getRoles());
        $entity->addRole('role');
        self::assertCount(2, $entity->getRoles());
        $entity->addRole('role');
        self::assertCount(2, $entity->getRoles());
    }

    public function testPassword(): void
    {
        $entity = new User();
        $entity->setPassword('password');
        self::assertSame('password', $entity->getPassword());
    }

    public function testLastName(): void
    {
        $entity = new User();
        self::assertNull($entity->getLastName());
        $entity->setLastName('last name');
        self::assertSame('last name', $entity->getLastName());
    }

    public function testFirstName(): void
    {
        $entity = new User();
        self::assertNull($entity->getFirstName());
        $entity->setFirstName('first name');
        self::assertSame('first name', $entity->getFirstName());
    }

    public function testSlug(): void
    {
        $entity = new User();
        self::assertNull($entity->getSlug());
        $entity->setSlug('slug');
        self::assertSame('slug', $entity->getSlug());
    }

    public function testIsVerified(): void
    {
        $entity = new User();
        self::assertFalse($entity->getVerified());
        $entity->setVerified(true);
        self::assertTrue($entity->getVerified());
    }
}
