<?php

namespace App\Controllers;

use App\Entities\CustomGroup;
use App\Entities\Group;
use App\Entities\School;
use App\Entities\UserStatus;
use App\Exceptions\LDAPException;
use CodeIgniter\CLI\CLI;
use LdapRecord\LdapRecordException;
use LdapRecord\Models\OpenLDAP\User;
use function App\Helpers\getGroups;
use function App\Helpers\getSchools;
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

            CLI::write('Synchronizing users with LDAP ...');
            $this->syncUsersLDAP();

            CLI::write('Synchronizing groups with LDAP ...');
            $this->syncGroupsLDAP();

            CLI::write('Synchronizing schools with LDAP ...');
            $this->syncSchoolsLDAP();

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
                CLI::write('Successfully synced user ' . $user->getUsername());
            } catch (LdapRecordException $e) {
                CLI::error('Error synchronizing user ' . $user->getUsername() . ': ' . $e->getMessage());
            }
        }

        // TODO Remove old users
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
    }

    private function syncGroupsLDAP(): void
    {
        foreach (getGroups() as $group) {
            try {
                $this->updateOrCreateLDAPGroup($group);
                CLI::write('Successfully synced group ' . $group->getName());
            } catch (LdapRecordException $e) {
                CLI::error('Error synchronizing group ' . $group->getName() . ': ' . $e->getMessage());
            }
        }

        // TODO Remove old groups
    }

    /**
     * @throws LdapRecordException
     */
    private function updateOrCreateLDAPGroup(Group $group): void
    {
        $ldapGroup = CustomGroup::query()->in(getenv('ldap.groupsDN'))->findBy('uid', $group->getId());
        if (!$ldapGroup) {
            $ldapGroup = new CustomGroup([
                'uid' => $group->getId(),
                'uniquemember' => 'uid=lgroschke,ou=users,dc=waldorfconnect,dc=de'
            ]);
            $ldapGroup->inside(getenv('ldap.groupsDN'));
            $ldapGroup->setDn('uid=' . $group->getId() . ',' . getenv('ldap.groupsDN'));
        }

        $ldapGroup->cn = $group->getName();

        // TODO add new members
        // TODO remove old members
        $ldapGroup->save();
    }

    private function syncSchoolsLDAP(): void
    {
        foreach (getSchools() as $school) {
            try {
                $this->updateOrCreateLDAPSchool($school);
                CLI::write('Successfully synced school ' . $school->getName());
            } catch (LdapRecordException $e) {
                CLI::error('Error synchronizing school ' . $school->getName() . ': ' . $e->getMessage());
            }
        }
    }

    /**
     * @throws LdapRecordException
     */
    private function updateOrCreateLDAPSchool(School $school): void
    {
        $ldapGroup = CustomGroup::query()->in(getenv('ldap.schoolsDN'))->findBy('uid', $school->getId());
        if (!$ldapGroup) {
            $ldapGroup = new CustomGroup([
                'uid' => $school->getId(),
                'uniquemember' => 'uid=lgroschke,ou=users,dc=waldorfconnect,dc=de'
            ]);
            $ldapGroup->inside(getenv('ldap.schoolsDN'));
            $ldapGroup->setDn('uid=' . $school->getId() . ',' . getenv('ldap.schoolsDN'));
        }

        $ldapGroup->cn = $school->getName();

        // TODO add new members
        // TODO remove old members
        $ldapGroup->save();
    }
}
