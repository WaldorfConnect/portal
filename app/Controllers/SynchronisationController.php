<?php

namespace App\Controllers;

use App\Entities\CustomGroup;
use App\Entities\School;
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

    private function syncUsersLDAP()
    {
        foreach (getUsers() as $user) {
            try {
                $this->updateOrCreateLDAPUser($user);
            } catch (LdapRecordException $e) {
                CLI::error('Error synchronizing user ' . $user->getFirstName() . ': ' . $e->getMessage());
            }
        }
    }

    /**
     * @throws LdapRecordException
     */
    private function updateOrCreateLDAPUser(\App\Entities\User $user)
    {
        $ldapUser = User::query()->findBy('uid', $user->getUsername());
        if (!$ldapUser) {
            $ldapUser = User::create()->inside(getenv('ldap.usersDN'));
        }

        $ldapUser->uid = $user->getUsername();
        $ldapUser->userPassword = $user->getPassword();
        $ldapUser->cn = $user->getName();
        $ldapUser->sn = $user->getLastName();
        $ldapUser->givenName = $user->getFirstName();
        // TODO $ldapUser->jpegPhoto
        $ldapUser->mail = $user->getEmail();
        $ldapUser->o = $user->getSchool()->getName();
        $ldapUser->save();
    }


    private function syncGroupsLDAP()
    {
        foreach (getGroups() as $group) {
            try {
                $this->updateOrCreateLDAPGroup($group);
            } catch (LdapRecordException $e) {
                CLI::error('Error synchronizing group ' . $group->getName() . ': ' . $e->getMessage());
            }
        }
    }

    /**
     * @throws LdapRecordException
     */
    private function updateOrCreateLDAPGroup(\App\Entities\Group $group)
    {
        $ldapGroup = (new CustomGroup)->in(getenv('ldap.groupsDN'))->findBy('uid', $group->getId())->get();
        if (!$ldapGroup) {
            $ldapGroup = (new CustomGroup)->inside(getenv('ldap.groupsDN'))->get();
        }

        $ldapGroup->cn = $group->getName();
        $ldapGroup->members()->attach(User::query()->findBy('uid', 'lgroschke'));

        // TODO add new members
        // TODO remove old members
        $ldapGroup->save();
    }

    private function syncSchoolsLDAP()
    {
        foreach (getSchools() as $school) {
            try {
                $this->updateOrCreateLDAPSchool($school);
            } catch (LdapRecordException $e) {
                CLI::error('Error synchronizing school ' . $school->getName() . ': ' . $e->getMessage());
            }
        }
    }

    /**
     * @throws LdapRecordException
     */
    private function updateOrCreateLDAPSchool(School $school)
    {
        $ldapGroup = (new CustomGroup)->in(getenv('ldap.schoolsDN'))->findBy('uid', $group->getId())->get();
        if (!$ldapGroup) {
            $ldapGroup = (new CustomGroup)->inside(getenv('ldap.schoolsDN'))->get();
        }

        $ldapGroup->cn = $school->getName();
        $ldapGroup->members()->attach(User::query()->findBy('uid', 'lgroschke'));

        // TODO add new members
        // TODO remove old members
        $ldapGroup->save();
    }
}
