<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Entity\ResetPasswordRequest;
use App\Repository\ResetPasswordRequestRepository;
use App\Repository\UserRepository;

/**
 * @group integration
 */
class ResetPasswordRequestRepositoryTest extends AbstractRepositoryTest
{
    private ResetPasswordRequestRepository $resetPasswordRequestRepository;
    private UserRepository $userRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userRepository = self::getContainer()->get(UserRepository::class);
        $this->resetPasswordRequestRepository = self::getContainer()->get(ResetPasswordRequestRepository::class);
    }

    public function testCreateResetPasswordRequest(): void
    {
        self::assertInstanceOf(
            ResetPasswordRequest::class,
            $this->resetPasswordRequestRepository->createResetPasswordRequest(
                $this->userRepository->find(1),
                new \DateTimeImmutable(),
                'selector',
                'hashed token'
            )
        );
    }
}
