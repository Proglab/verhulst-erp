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
use App\Entity\CompanyContact;
use App\Entity\User;
use App\Repository\CompanyContactRepository;
use App\Repository\UserRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpClient\CurlHttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'app:sync-campaign-monitor-unique-updated',
    description: 'Synchronisation avec le campaign monitor'
)]
class SyncCampainMonitorUniqueUpdated extends AbstractCommand
{
    protected array $contact = [];
    private string $apiKey = '90KiwmAmAm9Dvzu0PhknclRWp5ZX3PF7ZI5rxdA3zsETqWepaN9FttPVrU9xn7p2FtOrga6m2KPAyTzs+UEFhfJBA5d7sb5SzrUXr1edgG30QWo8BBg/E2TktFhNrKk2f14v4TehfxoBDAkEI+QpJw==';

    private OutputInterface $output;

    public function __construct(private HttpClientInterface $client, private UserRepository $userRepository, private CompanyContactRepository $companyContactRepository)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /***
         * Récupération des contacts validés
         */

        $this->output = $output;
        $this->client = new CurlHttpClient();
        $idList = 'a691756ffce9a4c2fc7a124991f18b5c';

        $users = $this->userRepository->getCommercials();

        /** @var User $user */
        foreach ($users as $user) {
            $contacts = $this->companyContactRepository->getUpdatedContact($user);

            if (0 === \count($contacts)) {
                continue;
            }
            $progressBar = new ProgressBar($output, \count($contacts));

            $output->writeln('');
            $output->writeln('<info>Traitement des contacts validés de ' . $user->getFullname() . '</info>');
            /** @var CompanyContact $companyContact */
            foreach ($contacts as $companyContact) {
                if (empty($companyContact->getEmail())) {
                    continue;
                }
                if (!$this->checkContactExist($idList, $companyContact->getEmail())) {
                    $contact = new Subscriber($companyContact->getEmail(), $companyContact->getFullName(), [
                        new CustomFieldValue('Langue', $companyContact->getLang()),
                        new CustomFieldValue('Genre', $companyContact->getSex()),
                        new CustomFieldValue('Formule de politesse', $companyContact->getGreeting()),
                        new CustomFieldValue('Sale name', $user->getFirstName() . ' ' . $user->getLastName()),
                        new CustomFieldValue('Sale email', $user->getEmail()),
                        new CustomFieldValue('Sale phone', $user->getPhone()),
                    ]);

                    $this->createContact($idList, $contact);

                    unset($this->contact[$companyContact->getEmail()]);

                    $companyContact->setUpdatedDt(null);
                    $this->companyContactRepository->save($companyContact, true);

                    // $output->writeln('Création du contact '.$contact->EmailAddress);
                } else {
                    $contact = new Subscriber($companyContact->getEmail(), $companyContact->getFullName(), [
                        new CustomFieldValue('Langue', $companyContact->getLang()),
                        new CustomFieldValue('Genre', $companyContact->getSex()),
                        new CustomFieldValue('Formule de politesse', $companyContact->getGreeting()),
                        new CustomFieldValue('Sale name', $user->getFirstName() . ' ' . $user->getLastName()),
                        new CustomFieldValue('Sale email', $user->getEmail()),
                        new CustomFieldValue('Sale phone', $user->getPhone()),
                    ], 'No');

                    $this->updateContact($idList, $contact);
                    $companyContact->setUpdatedDt(null);
                    $this->companyContactRepository->save($companyContact, true);
                }

                if (!$companyContact->isMailing()) {
                    $this->unsubscribeUser($idList, $companyContact);
                }

                unset($this->contact[$companyContact->getEmail()]);
                $progressBar->advance();
            }

            $output->writeln('');
            $output->writeln('<question>OK</question>');
            $progressBar->finish();
        }

        return Command::SUCCESS;
    }

    protected function createList(User $user): string
    {
        $l = new Listing($user->getFullname());

        $this->output->writeln('<info>Création de la liste ' . $user->getFullname() . '</info>');
        $response = $this->client->request('POST', 'https://api.createsend.com/api/v3.3/lists/7f3135d3d93a3cee8e6931e3c0256c55.json', [
            'auth_basic' => [$this->apiKey, 'the-password'],
            'body' => json_encode($l),
        ]);

        $idList = json_decode($response->getContent());
        $list[$idList] = $user->getFullname();

        $this->output->writeln('<info>Création des fields custom</info>');
        $this->output->writeln('<comment>Langue</comment>');

        $customField = new CustomField('Langue', 'Text');
        $response = $this->client->request('POST', 'https://api.createsend.com/api/v3.3/lists/' . $idList . '/customfields.json', [
            'auth_basic' => [$this->apiKey, 'the-password'],
            'body' => json_encode($customField),
        ]);
        $this->output->writeln('<comment>Genre</comment>');

        $customField = new CustomField('Genre', 'Text');
        $response = $this->client->request('POST', 'https://api.createsend.com/api/v3.3/lists/' . $idList . '/customfields.json', [
            'auth_basic' => [$this->apiKey, 'the-password'],
            'body' => json_encode($customField),
        ]);

        $this->output->writeln('<comment>Formule de politesse</comment>');

        $customField = new CustomField('Formule de politesse', 'Text');
        $response = $this->client->request('POST', 'https://api.createsend.com/api/v3.3/lists/' . $idList . '/customfields.json', [
            'auth_basic' => [$this->apiKey, 'the-password'],
            'body' => json_encode($customField),
        ]);

        $this->output->writeln('<comment>Création des segments</comment>');
        $segment = new Segment('FR', [
            new Rules([
                new Rule('[Langue]', 'EQUALS fr'),
            ]),
        ]);
        $response = $this->client->request('POST', 'https://api.createsend.com/api/v3.3/segments/' . $idList . '.json', [
            'auth_basic' => [$this->apiKey, 'the-password'],
            'body' => json_encode($segment),
        ]);

        $segment = new Segment('NL', [
            new Rules([
                new Rule('[Langue]', 'EQUALS nl'),
            ]),
        ]);

        $response = $this->client->request('POST', 'https://api.createsend.com/api/v3.3/segments/' . $idList . '.json', [
            'auth_basic' => [$this->apiKey, 'the-password'],
            'body' => json_encode($segment),
        ]);

        return $idList;
    }

    private function createContact(string $idList, Subscriber $contact): void
    {
        try {
            $this->client->request('POST', 'https://api.createsend.com/api/v3.3/subscribers/' . $idList . '.json', [
                'auth_basic' => [$this->apiKey, 'the-password'],
                'body' => json_encode($contact),
            ]);
        } catch (\Exception $e) {
            $this->output->writeln('');
            $this->output->writeln('<error>ERROR Contact created</error>');
            $this->output->writeln('<error>' . json_encode($e->getMessage()) . '</error>');
            $this->output->writeln('');
        }
    }

    private function updateContact(string $idList, Subscriber $contact): void
    {
        $this->client->request('PUT', 'https://api.createsend.com/api/v3.3/subscribers/' . $idList . '.json?email=' . urldecode($contact->EmailAddress), [
            'auth_basic' => [$this->apiKey, 'the-password'],
            'body' => json_encode($contact),
        ]);
    }

    private function checkContactExist(string $idList, string $email): bool
    {
        $response = $this->client->request('GET', 'https://api.createsend.com/api/v3.3/subscribers/' . $idList . '.json?email=' . urldecode($email) . '&includetrackingpreference=false', [
            'auth_basic' => [$this->apiKey, 'the-password'],
        ]);

        $r = json_decode($response->getContent(false));
        if (isset($r->Code)) {
            if (203 === $r->Code) {
                return false;
            }
        }

        return true;
    }

    private function getContact(string $idList, string $email): \stdClass
    {
        $response = $this->client->request('GET', 'https://api.createsend.com/api/v3.3/subscribers/' . $idList . '.json?email=' . urldecode($email) . '&includetrackingpreference=false', [
            'auth_basic' => [$this->apiKey, 'the-password'],
        ]);

        return json_decode($response->getContent(false));
    }

    private function unsubscribeUser(string $idList, CompanyContact $user): void
    {
        $data = new \stdClass();
        $data->EmailAddress = $user->getEmail();
        $contact = $this->getContact($idList, $user->getEmail());
        if ('Active' === $contact->State) {
            $response = $this->client->request('POST', 'https://api.createsend.com/api/v3.3/subscribers/' . $idList . '/unsubscribe.json', [
                'auth_basic' => [$this->apiKey, 'the-password'],
                'body' => json_encode($data),
            ]);
        }
    }
}
