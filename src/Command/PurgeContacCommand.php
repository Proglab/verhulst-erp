<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\CompanyContactRepository;
use App\Repository\TempCompanyContactRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:purge-contact',
    description: 'Purger les contacts'
)]
class PurgeContacCommand extends AbstractCommand
{
    public function __construct(private CompanyContactRepository $companyContactRepository, private TempCompanyContactRepository $tempCompanyContactRepository)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<info>Purge existing contact in import</info>');

        $contacts = $this->tempCompanyContactRepository->findAll();
        $progressBar = new ProgressBar($output, \count($contacts));
        foreach ($contacts as $contact) {
            $c = $this->companyContactRepository->findOneBy(['email' => $contact->getEmail()]);
            if (!empty($c)) {
                $this->tempCompanyContactRepository->remove($contact, true);
            }
            $progressBar->advance();
        }
        $progressBar->finish();
        $output->writeln('');
        $output->writeln('<info>DONE</info>');
        $output->writeln('');
        $output->writeln('');

        return Command::SUCCESS;
    }
}
