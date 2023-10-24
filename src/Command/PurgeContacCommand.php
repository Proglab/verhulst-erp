<?php

declare(strict_types=1);

namespace App\Command;

use App\Command\Dto\CustomField;
use App\Command\Dto\CustomFieldValue;
use App\Command\Dto\Listing;
use App\Command\Dto\Rule;
use App\Command\Dto\Rules;
use App\Command\Dto\Segment;
use App\Command\Dto\Subscriber;
use App\Entity\User;
use App\Repository\CompanyContactRepository;
use App\Repository\TempCompanyContactRepository;
use App\Repository\UserRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpClient\CurlHttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

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

        $contacts = $this->tempCompanyContactRepository->findAll();
        $progressBar = new ProgressBar($output, count($contacts));
        foreach ($contacts as $contact) {
            $c = $this->companyContactRepository->findOneBy(['email' => $contact->getEmail()]);
            if (!empty($c)) {
                $this->tempCompanyContactRepository->remove($contact, true);
            }
            $progressBar->advance();
        }
        $progressBar->finish();
        return Command::SUCCESS;
    }
}
