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
    name: 'app:sync-campaign-monitor-unique',
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
        /**
        $output->writeln('<info>Récupération des contacts d\'Anthony</info>');

        if (($fp = fopen("./src/Command/contacts.csv", "r")) !== FALSE) {
            $fist = true;
            while (($row = fgetcsv($fp, 1000, ",")) !== FALSE) {
                if ($fist) {
                    $fist = false;
                    continue;
                }
                $this->contact[$row[1]] = new Subscriber($row[1], $row[0], [
                    new CustomFieldValue('Langue', $row[2]),
                    new CustomFieldValue('Genre', $row[3]),
                    new CustomFieldValue('Formule de politesse', $row[4]),
                    new CustomFieldValue('Sale name', "Anthony Delhauteur"),
                    new CustomFieldValue('Sale email', "anthony.delhauteur@thefriends.be"),
                    new CustomFieldValue('Sale phone', "+32 2 657 90 70"),
                ]);
            }
            fclose($fp);
        }

         */
        $this->output = $output;
        $this->client = new CurlHttpClient();
        $idList = "a691756ffce9a4c2fc7a124991f18b5c";
        $users = $this->userRepository->getCommercials();
        /** @var User $user */
        foreach($users as $user) {
            $contacts = $user->getCompanyContacts();
            $progressBar = new ProgressBar($output, $contacts->count());
            $output->writeln('<info>Traitement des contacts de '.$user->getFullname().'</info>');
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
                           new CustomFieldValue('Sale name', $user->getFirstName().' '.$user->getLastName()),
                           new CustomFieldValue('Sale email', $user->getEmail()),
                           new CustomFieldValue('Sale phone', $user->getPhone()),
                        ]);

                        $this->createContact($idList, $contact);


                        unset($this->contact[$companyContact->getEmail()]);

                        //$output->writeln('Création du contact '.$contact->EmailAddress);
                    }
                } else {
                    $contact = new Subscriber($r->EmailAddress, $companyContact->getFullName(), [
                        new CustomFieldValue('Langue', $companyContact->getLang()),
                        new CustomFieldValue('Genre', $companyContact->getSex()),
                        new CustomFieldValue('Formule de politesse', $companyContact->getGreeting()),
                        new CustomFieldValue('Sale name', $user->getFirstName().' '.$user->getLastName()),
                        new CustomFieldValue('Sale email', $user->getEmail()),
                        new CustomFieldValue('Sale phone', $user->getPhone()),
                    ], 'No');

                    unset($this->contact[$r->EmailAddress]);

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

        /*
        $progressBar = new ProgressBar($output, count($this->contact));
        /** @var Subscriber $companyContact //
        foreach($this->contact as $companyContact) {
            if (empty($companyContact->EmailAddress)) {
                continue;
            }

            $response = $this->client->request('GET', 'https://api.createsend.com/api/v3.3/subscribers/'.$idList.'.json?email='.urldecode($companyContact->EmailAddress).'&includetrackingpreference=false', [
                'auth_basic' => [$this->apiKey, 'the-password'],
            ]);

            $r = json_decode($response->getContent(false));
            if (isset($r->Code)) {
                if ($r->Code == 203) {
                    $this->createContact($idList, $companyContact);


                    //$output->writeln('Création du contact '.$contact->EmailAddress);
                }
            } else {


                $contact = json_decode($response->getContent());

                $i = 0;
                $update = false;
                foreach($contact->CustomFields as &$cf) {
                    if ($cf->Key === 'Sale email') {
                        switch ($cf->Value) {
                            case 'anthony@verhulst.be':
                                $contact->CustomFields[$i]->Value = "anthony.delhauteur@thefriends.be";
                                $update = true;
                                break;
                        }
                    }

                    $i++;
                }

                if ($update) {
                    $this->updateContact($idList, $companyContact);
                   // $output->writeln('Update du contact '.$companyContact->EmailAddress);
                }
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        */
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