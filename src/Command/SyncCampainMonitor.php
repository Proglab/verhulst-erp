<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\User;
use App\Repository\ResendConfirmationEmailRequestRepository;
use App\Repository\UserRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Config\FrameworkConfig;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'app:sync-campaign-monitor',
    description: 'Synchronisation avec le campaign monitor'
)]
class SyncCampainMonitor extends AbstractCommand
{
    public function __construct(private HttpClientInterface $client, private UserRepository $userRepository)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $response = $this->client->request('GET', 'https://api.createsend.com/api/v3.3/clients/7f3135d3d93a3cee8e6931e3c0256c55/lists.json', [
            'auth_basic' => ['90KiwmAmAm9Dvzu0PhknclRWp5ZX3PF7ZI5rxdA3zsETqWepaN9FttPVrU9xn7p2FtOrga6m2KPAyTzs+UEFhfJBA5d7sb5SzrUXr1edgG30QWo8BBg/E2TktFhNrKk2f14v4TehfxoBDAkEI+QpJw==', 'the-password'],
        ]);

        $list = [];
        $listsData = json_decode($response->getContent());

        foreach($listsData as $listData) {
            $list[$listData->ListID] = $listData->Name;
        }

        $users = $this->userRepository->getCommercials();
        /** @var User $user */
        foreach($users as $user) {
            if (!in_array($user->getFullname(), $list)) {
                $l = new \stdClass();
                $l->title = $user->getFullname();

                $output->writeln('Création de la liste '.$user->getFullname());
                $response = $this->client->request('POST', 'https://api.createsend.com/api/v3.3/lists/7f3135d3d93a3cee8e6931e3c0256c55.json', [
                    'auth_basic' => ['90KiwmAmAm9Dvzu0PhknclRWp5ZX3PF7ZI5rxdA3zsETqWepaN9FttPVrU9xn7p2FtOrga6m2KPAyTzs+UEFhfJBA5d7sb5SzrUXr1edgG30QWo8BBg/E2TktFhNrKk2f14v4TehfxoBDAkEI+QpJw==', 'the-password'],
                    'body' => json_encode($l),
                ]);

                $idList = json_decode($response->getContent());
                $list[$idList] = $user->getFullname();

                $output->writeln('Création des fields custom');
                $output->writeln('Langue');

                $customField = new \stdClass();
                $customField->FieldName = "Langue";
                $customField->DataType = "Text";
                $response = $this->client->request('POST', "https://api.createsend.com/api/v3.3/lists/".$idList."/customfields.json", [
                    'auth_basic' => ['90KiwmAmAm9Dvzu0PhknclRWp5ZX3PF7ZI5rxdA3zsETqWepaN9FttPVrU9xn7p2FtOrga6m2KPAyTzs+UEFhfJBA5d7sb5SzrUXr1edgG30QWo8BBg/E2TktFhNrKk2f14v4TehfxoBDAkEI+QpJw==', 'the-password'],
                    'body' => json_encode($customField),
                ]);
                $output->writeln('Genre');

                $customField = new \stdClass();
                $customField->FieldName = "Genre";
                $customField->DataType = "Text";
                $response = $this->client->request('POST', "https://api.createsend.com/api/v3.3/lists/".$idList."/customfields.json", [
                    'auth_basic' => ['90KiwmAmAm9Dvzu0PhknclRWp5ZX3PF7ZI5rxdA3zsETqWepaN9FttPVrU9xn7p2FtOrga6m2KPAyTzs+UEFhfJBA5d7sb5SzrUXr1edgG30QWo8BBg/E2TktFhNrKk2f14v4TehfxoBDAkEI+QpJw==', 'the-password'],
                    'body' => json_encode($customField),
                ]);

                $output->writeln('Formule de politesse');

                $customField = new \stdClass();
                $customField->FieldName = "Formule de politesse";
                $customField->DataType = "Text";
                $response = $this->client->request('POST', "https://api.createsend.com/api/v3.3/lists/".$idList."/customfields.json", [
                    'auth_basic' => ['90KiwmAmAm9Dvzu0PhknclRWp5ZX3PF7ZI5rxdA3zsETqWepaN9FttPVrU9xn7p2FtOrga6m2KPAyTzs+UEFhfJBA5d7sb5SzrUXr1edgG30QWo8BBg/E2TktFhNrKk2f14v4TehfxoBDAkEI+QpJw==', 'the-password'],
                    'body' => json_encode($customField),
                ]);


            } else {
                $idList = array_search($user->getFullname(), $list);
            }

            foreach ($user->getCompanyContacts() as $companyContact) {
                $response = $this->client->request('GET', 'https://api.createsend.com/api/v3.3/subscribers/'.$idList.'.json?email='.urldecode($companyContact->getEmail()).'&includetrackingpreference=false', [
                    'auth_basic' => ['90KiwmAmAm9Dvzu0PhknclRWp5ZX3PF7ZI5rxdA3zsETqWepaN9FttPVrU9xn7p2FtOrga6m2KPAyTzs+UEFhfJBA5d7sb5SzrUXr1edgG30QWo8BBg/E2TktFhNrKk2f14v4TehfxoBDAkEI+QpJw==', 'the-password'],
                ]);

                $r = json_decode($response->getContent(false));
                if (isset($r->Code)) {
                    if ($r->Code == 203) {
                        $contact = new \stdClass();
                        $contact->EmailAddress = $companyContact->getEmail();
                        $contact->Name = $companyContact->getFullName();
                        $contact->ConsentToTrack = "Yes";
                        $contact->CustomFields = [];
                        $customeFields = new \stdClass();
                        $customeFields->Key = 'Langue';
                        $customeFields->Value = $companyContact->getLang();
                        $contact->CustomFields[] = $customeFields;
                        $customeFields = new \stdClass();
                        $customeFields->Key = 'Genre';
                        $customeFields->Value = $companyContact->getSex();
                        $contact->CustomFields[] = $customeFields;
                        $customeFields = new \stdClass();
                        $customeFields->Key = 'Formule de politesse';
                        $customeFields->Value = $companyContact->getGreeting();
                        $contact->CustomFields[] = $customeFields;
                        $response = $this->client->request('POST', 'https://api.createsend.com/api/v3.3/subscribers/'.$idList.'.json', [
                            'auth_basic' => ['90KiwmAmAm9Dvzu0PhknclRWp5ZX3PF7ZI5rxdA3zsETqWepaN9FttPVrU9xn7p2FtOrga6m2KPAyTzs+UEFhfJBA5d7sb5SzrUXr1edgG30QWo8BBg/E2TktFhNrKk2f14v4TehfxoBDAkEI+QpJw==', 'the-password'],
                            'body' => json_encode($contact),
                        ]);
                    }
                } else {
                    $contact = new \stdClass();
                    $contact->EmailAddress = $r->EmailAddress;
                    $contact->Name = $companyContact->getFullName();
                    $customeFields = new \stdClass();
                    $customeFields->Key = 'Langue';
                    $customeFields->Value = $companyContact->getLang();
                    $contact->CustomFields[] = $customeFields;
                    $customeFields = new \stdClass();
                    $customeFields->Key = 'Genre';
                    $customeFields->Value = $companyContact->getSex();
                    $contact->CustomFields[] = $customeFields;
                    $customeFields = new \stdClass();
                    $customeFields->Key = 'Formule de politesse';
                    $customeFields->Value = $companyContact->getGreeting();
                    $contact->CustomFields[] = $customeFields;
                    $contact->ConsentToTrack = "No";
                    $response = $this->client->request('PUT', "https://api.createsend.com/api/v3.3/subscribers/".$idList.".json?email=".urldecode($r->EmailAddress), [
                        'auth_basic' => ['90KiwmAmAm9Dvzu0PhknclRWp5ZX3PF7ZI5rxdA3zsETqWepaN9FttPVrU9xn7p2FtOrga6m2KPAyTzs+UEFhfJBA5d7sb5SzrUXr1edgG30QWo8BBg/E2TktFhNrKk2f14v4TehfxoBDAkEI+QpJw==', 'the-password'],
                        'body' => json_encode($contact),
                    ]);
                }
            }
        }

        return 0;
    }
}