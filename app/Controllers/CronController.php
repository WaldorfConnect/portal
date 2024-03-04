<?php

namespace App\Controllers;

use App\Entities\Organisation;
use App\Exceptions\LDAPException;
use CodeIgniter\CLI\CLI;
use Composer\CaBundle\CaBundle;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\RequestOptions;
use function App\Helpers\getOrganisations;
use function App\Helpers\openLDAPConnection;
use function App\Helpers\syncOrganisations;
use function App\Helpers\syncUsers;
use function App\Helpers\workMailQueue;

class CronController extends BaseController
{
    public function index(): void
    {
        CLI::write(date("d.m.Y H:i:s"));

        try {
            CLI::write('Working mail queue ...');
            workMailQueue();
        } catch (Exception $e) {
            CLI::error("Error working mail queue: {$e->getMessage()}");
        }

        try {
            openLDAPConnection();

            CLI::write('Synchronizing LDAP users ...');
            syncUsers();

            CLI::write('Synchronizing LDAP organisations ...');
            syncOrganisations();
        } catch (LDAPException $e) {
            CLI::error("Error synchronizing with LDAP: {$e->getMessage()}");
        }
    }

    /**
     * @throws Exception
     */
    private function syncNextcloudGroupFolders(): void
    {
        $client = new Client([
            RequestOptions::VERIFY => CaBundle::getSystemCaRootBundlePath(),
            RequestOptions::AUTH => [
                getenv('nextcloud.username'),
                getenv('nextcloud.password')
            ],
            RequestOptions::HEADERS => [
                'Accept' => 'application/json',
                'OCS-APIRequest' => 'true'
            ]
        ]);

        $folders = $this->getGroupFolders($client);
        $groups = getOrganisations();

        // Check if folder for group exists
        foreach ($groups as $group) {
            $this->updateOrCreateGroupFolder($client, $folders, $group);
        }

        // Check if group for folder exists
        foreach ($folders as $folder) {

        }
    }

    private function updateOrCreateGroupFolder(Client $client, array $folders, Organisation $group): void
    {

    }

    private function getGroupFolders(Client $client): array
    {
        $dataArray = [];
        try {
            $request = new Request('GET', GROUP_FOLDERS_API . '/folders');
            $response = $client->send($request);
            $decodedResponse = json_decode($response->getBody()->getContents());
            $data = $decodedResponse->ocs->data;

            $index = 1;
            while (true) {
                if (!property_exists($data, $index)) {
                    break;
                }

                $dataArray[] = $data->{$index};
                $index++;
            }
        } catch (GuzzleException $e) {
            CLI::error("Error while requesting folders: " . $e->getMessage());
        }
        return $dataArray;
    }
}
