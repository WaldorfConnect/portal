<?php

namespace App\Controllers;

use App\Entities\CustomGroup;
use App\Entities\Group;
use App\Exceptions\LDAPException;
use CodeIgniter\CLI\CLI;
use Composer\CaBundle\CaBundle;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\RequestOptions;
use LdapRecord\LdapRecordException;
use LdapRecord\Models\OpenLDAP\User;
use function App\Helpers\getGroupById;
use function App\Helpers\getGroups;
use function App\Helpers\getUserByUsername;
use function App\Helpers\getUsers;
use function App\Helpers\openLDAPConnection;
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
            $this->syncUsersLDAP();

            CLI::write('Synchronizing LDAP groups ...');
            $this->syncGroupsLDAP();
        } catch (LDAPException $e) {
            CLI::error("Error synchronizing with LDAP: {$e->getMessage()}");
        }

        try {
            CLI::write('Synchronizing Nextcloud group folders ...');
            $this->syncNextcloudGroupFolders();
        } catch (Exception $e) {
            CLI::error("Error synchronizing with Nextcloud: {$e->getMessage()}");
        }
    }

    private function syncUsersLDAP(): void
    {
        foreach (getUsers() as $user) {
            // Skip users in a non-synchronizable state
            if (!$user->getStatus()->isSynchronizable())
                continue;

            try {
                $this->updateOrCreateLDAPUser($user);
            } catch (LdapRecordException $e) {
                CLI::error('Error synchronizing user ' . $user->getUsername() . ': ' . $e->getMessage());
            }
        }

        $ldapUsers = User::query()->in(getenv('ldap.usersDN'))->paginate();
        foreach ($ldapUsers as $ldapUser) {
            if (is_null(getUserByUsername($ldapUser->uid[0]))) {
                $ldapUser->delete();
                CLI::write('Deleted user ' . $ldapUser->uid[0]);
            }
        }
    }

    /**
     * @throws LdapRecordException
     */
    private function updateOrCreateLDAPUser(\App\Entities\User $user): void
    {
        $ldapUser = User::query()->findBy('uid', $user->getUsername());
        if (!$ldapUser) {
            $ldapUser = new User([
                'uid' => $user->getUsername()
            ]);
            $ldapUser->inside(getenv('ldap.usersDN'));
            $ldapUser->setDn('uid=' . $user->getUsername() . ',' . getenv('ldap.usersDN'));
        }

        $ldapUser->userPassword = $user->getPassword();
        $ldapUser->cn = $user->getName();
        $ldapUser->sn = $user->getLastName();
        $ldapUser->givenName = $user->getFirstName();
        // TODO $ldapUser->jpegPhoto
        $ldapUser->mail = $user->getEmail();
        $ldapUser->o = $user->getSchool()->getName();
        $ldapUser->save();
        CLI::write('Successfully synced user ' . $user->getUsername());
    }

    /**
     * @param string $username
     * @return ?User
     */
    private function getLDAPUserByUsername(string $username): ?object
    {
        return User::query()->findBy('uid', $username);
    }

    private function syncGroupsLDAP(): void
    {
        foreach (getGroups() as $group) {
            try {
                $this->updateOrCreateLDAPGroups($group);
            } catch (LdapRecordException $e) {
                CLI::error('Error synchronizing group ' . $group->getName() . ': ' . $e->getMessage());
            }
        }

        $ldapGroups = CustomGroup::query()->in(getenv('ldap.groupsDN'))->paginate();
        foreach ($ldapGroups as $ldapGroup) {
            if (is_null(getGroupById($ldapGroup->uid[0]))) {
                $ldapGroup->delete();
                CLI::write('Deleted group ' . $ldapGroup->cn[0]);
            }
        }
    }

    /**
     * @throws LdapRecordException
     */
    private function updateOrCreateLDAPGroups(Group $group): void
    {
        $memberships = $group->getMemberships();
        if (count($memberships) == 0) {
            CLI::write('Skipping empty group ' . $group->getName());
            return;
        }

        $ldapGroup = CustomGroup::query()->in(getenv('ldap.groupsDN'))->findBy('uid', $group->getId());
        if (!$ldapGroup) {
            $ldapGroup = new CustomGroup([
                'uid' => $group->getId(),
                'uniquemember' => "uid={$memberships[0]->getUser()->getUsername()}," . getenv('ldap.usersDN')
            ]);
            $ldapGroup->inside(getenv('ldap.groupsDN'));
            $ldapGroup->setDn('uid=' . $group->getId() . ',' . getenv('ldap.groupsDN'));
        }

        $ldapGroup->cn = $group->getName();

        // Remove former users from the ldap group
        // TODO possible improvements: don't query each users' username
        foreach ($ldapGroup->members()->get() as $member) {
            $isMember = false;
            foreach ($memberships as $membership) {
                if ($member->uid[0] == $membership->getUser()->getUsername()) {
                    $isMember = true;
                    break;
                }
            }

            // Remove user from group
            if (!$isMember) {
                $ldapGroup->members()->detach($member);
                CLI::write('Removed ' . $member->uid[0] . ' from group ' . $group->getName());
            }
        }

        // Add new users to the group
        // TODO possible improvements: don't query each users' username
        foreach ($memberships as $membership) {
            $user = $membership->getUser();
            $ldapUser = $this->getLDAPUserByUsername($user->getUsername());
            if (!is_null($ldapUser) && !$ldapGroup->members()->contains($ldapUser)) {
                $ldapGroup->members()->attach($ldapUser);
                CLI::write('Added ' . $user->getUsername() . ' to group ' . $group->getName());
            }
        }

        $ldapGroup->save();
        CLI::write('Successfully synced group ' . $group->getName());
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
        $groups = getGroups();

        // Check if folder for group exists
        foreach ($groups as $group) {
            $this->updateOrCreateGroupFolder($client, $folders, $group);
        }

        // Check if group for folder exists
        foreach ($folders as $folder) {

        }
    }

    private function updateOrCreateGroupFolder(Client $client, array $folders, Group $group): void
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