<?php

declare(strict_types=1);

namespace App\Tests\Form\Model;

use App\Form\Model\PasswordUpdate;
use PHPUnit\Framework\TestCase;

/**
 * @group unit
 */
class PasswordUpdateTest extends TestCase
{
    public function testOldPassword(): void
    {
        $model = new PasswordUpdate();
        self::assertNull($model->oldPassword);
        $model->oldPassword = 'old password';
        self::assertSame('old password', $model->oldPassword);
    }

    public function testNewPassword(): void
    {
        $model = new PasswordUpdate();
        self::assertNull($model->newPassword);
        $model->newPassword = 'new password';
        self::assertSame('new password', $model->newPassword);
    }
}
