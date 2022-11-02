<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\ResendConfirmationEmailRequestRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:remove-expired-reset-confirmation',
    description: 'Deletion of email confirmation requests that have not been confirmed for 1 week'
)]
class RemoveExpiredResetConfirmationEmailRequestsCommand extends AbstractCommand
{
    public function __construct(private readonly ResendConfirmationEmailRequestRepository $repository)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Removing expired reset confirmation email requests...');

        $intRemoved = $this->repository->removeExpiredResetConfirmationEmailRequests();

        $output->writeln(sprintf('Garbage collection successful. Removed %s reset confirmation email request object(s).', $intRemoved));

        return Command::SUCCESS;
    }
}
