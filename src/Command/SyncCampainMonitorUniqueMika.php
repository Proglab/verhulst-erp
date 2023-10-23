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
use App\Entity\TempCompanyContact;
use App\Entity\User;
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
    name: 'app:sync-campaign-monitor-unique-mika',
    description: 'Synchronisation avec le campaign monitor'
)]
class SyncCampainMonitorUniqueMika extends AbstractCommand
{
    protected array $contact = [];
    private $apiKey = '90KiwmAmAm9Dvzu0PhknclRWp5ZX3PF7ZI5rxdA3zsETqWepaN9FttPVrU9xn7p2FtOrga6m2KPAyTzs+UEFhfJBA5d7sb5SzrUXr1edgG30QWo8BBg/E2TktFhNrKk2f14v4TehfxoBDAkEI+QpJw==';

    private OutputInterface $output;


    public function __construct(private HttpClientInterface $client, private UserRepository $userRepository, private TempCompanyContactRepository $tempCompanyContactRepository)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<info>Récupération des contacts de Mika</info>');

        $this->output = $output;
        $this->client = new CurlHttpClient();

        $output->writeln('<info>Récupération des listes</info>');
        $idList = "a691756ffce9a4c2fc7a124991f18b5c";

        $user = $this->userRepository->findOneBy(['email' => 'michael.veys@thefriends.be']);

        if (($handle = fopen(__DIR__. "/mika.csv", "r")) !== FALSE) {
            $i = true;
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                if ($i === true) {
                    $i = false;
                    continue;
                }

                $contact = $this->tempCompanyContactRepository->findOneBy(['email' => $data[1]]);
                if (!empty($contact)) {
                    if (empty($contact->getAddedBy())) {
                        $contact->setAddedBy($user);
                        $this->tempCompanyContactRepository->save($contact, true);
                        $output->writeln($contact->getEmail().' imported');
                    } else {
                        if (in_array($contact->getEmail(), ['aurelie.paulus@bdl.lu', 'valerie.devos@allenovery.com', 'virginie.vanesch@ca-indosuez.be'])) {
                            $contact->setAddedBy($user);
                            $this->tempCompanyContactRepository->save($contact, true);
                            $output->writeln($contact->getEmail().' imported');
                        }
                    }
                }
            }
            fclose($handle);
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