<?php

namespace App\Controllers;

use CodeIgniter\CLI\CLI;
use function App\Helpers\getUserByUsernameAndPassword;
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

    public function syncUsersLDAP()
    {

    }

    public function syncGroupsLDAP()
    {

    }

    public function syncSchoolsLDAP()
    {

    }
}
