<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Repository\ResendConfirmationEmailRequestRepository;

/**
 * @group integration
 */
class ResendConfirmationEmailRequestRepositoryTest extends AbstractRepositoryTest
{
    private ResendConfirmationEmailRequestRepository $resendConfirmationEmailRequestRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resendConfirmationEmailRequestRepository = self::getContainer()->get(ResendConfirmationEmailRequestRepository::class);
    }

    public function testFindToken(): void
    {
        $this->resendConfirmationEmailRequestRepository->findToken('token');
        $this->expectNotToPerformAssertions();
    }

    public function testRemoveExpiredResetConfirmationEmailRequests(): void
    {
        $this->resendConfirmationEmailRequestRepository->removeExpiredResetConfirmationEmailRequests();
        $this->expectNotToPerformAssertions();
    }
}
