<?php

namespace App\Controllers;

use CodeIgniter\CLI\CLI;
use LdapRecord\LdapRecordException;
use LdapRecord\Models\OpenLDAP\CustomGroup;
use LdapRecord\Models\OpenLDAP\User;
use function App\Helpers\getGroups;
use function App\Helpers\getSchools;
use function App\Helpers\getUserByUsernameAndPassword;
use function App\Helpers\getUsers;
use function App\Helpers\login;
use function App\Helpers\logout;

class SynchronisationController extends BaseController
{
    public function index(): void
    {
        CLI::write('Synchronizing users with LDAP ...');
        $this->syncUsersLDAP();

        CLI::write('Synchronizing groups with LDAP ...');
        $this->syncGroupsLDAP();

        CLI::write('Synchronizing schools with LDAP ...');
        $this->syncSchoolsLDAP();

        CLI::write('Finished!');
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
        $ldapUser = User::query()->findBy('uid', $user->getUsername())->get();
        if (!$ldapUser) {
            $ldapUser = (new User())->inside(getenv('ldap.usersDN'));
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
        $ldapGroup = CustomGroup::query()->findBy('uid', $group->getId());
        if (!$ldapGroup) {
            $ldapGroup = (new CustomGroup())->inside(getenv('ldap.groupsDN'));
            $ldapGroup->uid = $group->getId();
        }

        $ldapGroup->cn = $group->getName();
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
    private function updateOrCreateLDAPSchool(\App\Entities\School $school)
    {
        $ldapGroup = CustomGroup::query()->findBy('uid', $school->getId());
        if (!$ldapGroup) {
            $ldapGroup = (new CustomGroup())->inside(getenv('ldap.schoolsDN'));
            $ldapGroup->uid = $school->getId();
        }

        $ldapGroup->cn = $school->getName();
        // TODO add new members
        // TODO remove old members
        $ldapGroup->save();
    }
}
