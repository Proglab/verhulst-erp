<?php

declare(strict_types=1);

namespace App\Tests\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @group integration
 */
class RemoveExpiredResetConfirmationEmailRequestsCommandTest extends KernelTestCase
{
    public function testExecute(): void
    {
        $kernel = self::createKernel();
        $application = new Application($kernel);

        $command = $application->find('app:remove-expired-reset-confirmation');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => $command->getName()]);

        $output = $commandTester->getDisplay();
        self::assertStringContainsString('Garbage collection successful.', $output);
    }
}
