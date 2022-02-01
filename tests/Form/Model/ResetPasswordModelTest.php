<?php

declare(strict_types=1);

namespace App\Tests\Form\Model;

use App\Form\Model\ResetPasswordModel;
use PHPUnit\Framework\TestCase;

/**
 * @group unit
 */
class ResetPasswordModelTest extends TestCase
{
    public function testPlainPassword(): void
    {
        $model = new ResetPasswordModel();
        self::assertNull($model->plainPassword);
        $model->plainPassword = 'plain password';
        self::assertSame('plain password', $model->plainPassword);
    }
}
