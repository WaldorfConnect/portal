<?php

namespace App\Controllers;

use App\Entities\CustomGroup;
use App\Entities\Group;
use App\Entities\UserStatus;
use App\Exceptions\LDAPException;
use CodeIgniter\CLI\CLI;
use LdapRecord\LdapRecordException;
use LdapRecord\Models\Model;
use LdapRecord\Models\OpenLDAP\User;
use LdapRecord\Query\Model\Builder;
use function App\Helpers\getGroupById;
use function App\Helpers\getGroups;
use function App\Helpers\getUserByUsername;
use function App\Helpers\getUserByUsernameAndPassword;
use function App\Helpers\getUsers;
use function App\Helpers\login;
use function App\Helpers\logout;
use function App\Helpers\openLDAPConnection;

class SynchronisationController extends BaseController
{
    public function index(): void
    {
        try {
            openLDAPConnection();

            CLI::write('Synchronizing LDAP users ...');
            $this->syncUsersLDAP();

            CLI::write(' ');
            CLI::write('Synchronizing LDAP groups ...');
            $this->syncGroupsLDAP();

            CLI::write('Finished!');
        } catch (LDAPException $e) {
            CLI::error('Error with ldap connection: ' . $e->getMessage());
        }
    }

    private function syncUsersLDAP(): void
    {
        foreach (getUsers() as $user) {
            // Do not sync user if accept is pending
            if ($user->getStatus() == UserStatus::PENDING_ACCEPT) continue;

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
}
