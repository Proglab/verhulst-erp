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
use App\Repository\UserRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpClient\CurlHttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'app:sync-campaign-monitor',
    description: 'Synchronisation avec le campaign monitor'
)]
class SyncCampainMonitorUnique extends AbstractCommand
{
    protected array $contact = [];
    private $apiKey = '90KiwmAmAm9Dvzu0PhknclRWp5ZX3PF7ZI5rxdA3zsETqWepaN9FttPVrU9xn7p2FtOrga6m2KPAyTzs+UEFhfJBA5d7sb5SzrUXr1edgG30QWo8BBg/E2TktFhNrKk2f14v4TehfxoBDAkEI+QpJw==';

    private OutputInterface $output;


    public function __construct(private HttpClientInterface $client, private UserRepository $userRepository)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->output = $output;
        $this->client = new CurlHttpClient();

        $output->writeln('<info>Récupération des listes</info>');
        $response = $this->client->request('GET', 'https://api.createsend.com/api/v3.3/clients/7f3135d3d93a3cee8e6931e3c0256c55/lists.json', [
            'auth_basic' => [$this->apiKey, 'the-password'],
        ]);

        $list = [];
        $listsData = json_decode($response->getContent());

        foreach($listsData as $listData) {
            $list[$listData->ListID] = $listData->Name;
        }
        $output->writeln('<info>Traitement des listes</info>');
        $users = $this->userRepository->getCommercials();
        /** @var User $user */
        foreach($users as $user) {
            if (!in_array($user->getFullname(), $list)) {
                $idList = $this->createList($user);
            } else {
                $idList = array_search($user->getFullname(), $list);
                $output->writeln('<info>Liste déjà crée pour '.$user->getFullname().'. Numéro de la liste '.$idList.'</info>');
            }
            $contacts = $user->getCompanyContacts();
            $progressBar = new ProgressBar($output, $contacts->count());
            $output->writeln('<info>Traitement des contacts</info>');
            foreach ($contacts as $companyContact) {
                if (empty($companyContact->getEmail())) {
                    continue;
                }

                $response = $this->client->request('GET', 'https://api.createsend.com/api/v3.3/subscribers/'.$idList.'.json?email='.urldecode($companyContact->getEmail()).'&includetrackingpreference=false', [
                    'auth_basic' => [$this->apiKey, 'the-password'],
                ]);

                $r = json_decode($response->getContent(false));
                if (isset($r->Code)) {
                    if ($r->Code == 203) {
                        $contact = new Subscriber($companyContact->getEmail(), $companyContact->getFullName(), [
                           new CustomFieldValue('Langue', $companyContact->getLang()),
                           new CustomFieldValue('Genre', $companyContact->getSex()),
                           new CustomFieldValue('Formule de politesse', $companyContact->getGreeting()),
                        ]);

                        $this->createContact($idList, $contact);


                        //$output->writeln('Création du contact '.$contact->EmailAddress);
                    }
                } else {
                    $contact = new Subscriber($r->EmailAddress, $companyContact->getFullName(), [
                        new CustomFieldValue('Langue', $companyContact->getLang()),
                        new CustomFieldValue('Genre', $companyContact->getSex()),
                        new CustomFieldValue('Formule de politesse', $companyContact->getGreeting()),
                    ], 'No');

                    $this->updateContact($idList, $contact);

                    //$output->writeln('Update du contact '.$contact->EmailAddress);
                }
                $progressBar->advance();

            }
            $output->writeln('');
            $output->writeln('<question>OK</question>');
            $progressBar->finish();
            $this->client = new CurlHttpClient();
        }

        return Command::SUCCESS;
    }


    protected function createList(User $user)
    {
        $l = new Listing($user->getFullname());

        $this->output->writeln('<info>Création de la liste '.$user->getFullname().'</info>');
        $response = $this->client->request('POST', 'https://api.createsend.com/api/v3.3/lists/7f3135d3d93a3cee8e6931e3c0256c55.json', [
            'auth_basic' => [$this->apiKey, 'the-password'],
            'body' => json_encode($l),
        ]);

        $idList = json_decode($response->getContent());
        $list[$idList] = $user->getFullname();

        $this->output->writeln('<info>Création des fields custom</info>');
        $this->output->writeln('<comment>Langue</comment>');

        $customField = new CustomField("Langue", "Text");
        $response = $this->client->request('POST', "https://api.createsend.com/api/v3.3/lists/".$idList."/customfields.json", [
            'auth_basic' => [$this->apiKey, 'the-password'],
            'body' => json_encode($customField),
        ]);
        $this->output->writeln('<comment>Genre</comment>');

        $customField = new CustomField("Genre", "Text");
        $response = $this->client->request('POST', "https://api.createsend.com/api/v3.3/lists/".$idList."/customfields.json", [
            'auth_basic' => [$this->apiKey, 'the-password'],
            'body' => json_encode($customField),
        ]);

        $this->output->writeln('<comment>Formule de politesse</comment>');


        $customField = new CustomField("Formule de politesse", "Text");
        $response = $this->client->request('POST', "https://api.createsend.com/api/v3.3/lists/".$idList."/customfields.json", [
            'auth_basic' => [$this->apiKey, 'the-password'],
            'body' => json_encode($customField),
        ]);


        $this->output->writeln('<comment>Création des segments</comment>');
        $segment = new Segment("FR", [
            new Rules([
                new Rule('[Langue]', 'EQUALS fr')
            ])
        ]);
        $response = $this->client->request('POST', "https://api.createsend.com/api/v3.3/segments/".$idList.".json", [
            'auth_basic' => [$this->apiKey, 'the-password'],
            'body' => json_encode($segment),
        ]);


        $segment = new Segment("NL", [
            new Rules([
                new Rule('[Langue]', 'EQUALS nl')
            ])
        ]);

        $response = $this->client->request('POST', "https://api.createsend.com/api/v3.3/segments/".$idList.".json", [
            'auth_basic' => [$this->apiKey, 'the-password'],
            'body' => json_encode($segment),
        ]);

        return $idList;

    }

    private function createContact(string $idList, Subscriber $contact)
    {
        $response = $this->client->request('POST', 'https://api.createsend.com/api/v3.3/subscribers/'.$idList.'.json', [
            'auth_basic' => [$this->apiKey, 'the-password'],
            'body' => json_encode($contact),
        ]);

    }

    private function updateContact(string $idList, Subscriber $contact)
    {
        $response = $this->client->request('PUT', "https://api.createsend.com/api/v3.3/subscribers/".$idList.".json?email=".urldecode($contact->EmailAddress), [
            'auth_basic' => [$this->apiKey, 'the-password'],
            'body' => json_encode($contact),
        ]);

    }
}