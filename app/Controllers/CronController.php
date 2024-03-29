<?php

namespace App\Controllers;

use CodeIgniter\CLI\CLI;
use Exception;
use function App\Helpers\openLDAPConnection;
use function App\Helpers\syncLDAPOrganisations;
use function App\Helpers\syncLDAPUsers;
use function App\Helpers\syncOrganisationFolders;
use function App\Helpers\workMailQueue;

class CronController extends BaseController
{
    public function mail(): void
    {
        if (!$this->acquireLock('mail')) {
            return;
        }

        $this->printTimestamp();

        try {
            CLI::write('Working mail queue ...');
            workMailQueue();
        } catch (Exception $e) {
            CLI::error("Error working mail queue: {$e}");
        } finally {
            $this->releaseLock('mail');
        }
    }

    public function ldap(): void
    {
        if (!$this->acquireLock('ldap')) {
            return;
        }

        $this->printTimestamp();

        try {
            $count = 0;
            while ($this->isAcquired('nextcloud')) {
                sleep(1);
                CLI::write('Waiting for Nextcloud sync to finish ... [' . $count . ']');

                if ($count++ == 30) {
                    CLI::error('Timed out waiting for Nextcloud. Quitting...');
                    return;
                }
            }

            CLI::write('Opening LDAP connection ...');
            openLDAPConnection();

            CLI::write('Synchronizing LDAP users ...');
            syncLDAPUsers();

            CLI::write('Synchronizing LDAP organisations ...');
            syncLDAPOrganisations();
        } catch (Exception $e) {
            CLI::error("Error synchronizing with LDAP: {$e}");
        } finally {
            $this->releaseLock('ldap');
        }
    }

    public function nextcloud(): void
    {
        if (!$this->acquireLock('nextcloud')) {
            return;
        }

        $this->printTimestamp();

        try {
            $count = 0;
            while ($this->isAcquired('ldap')) {
                sleep(1);
                CLI::write('Waiting for LDAP sync to finish ... [' . $count . ']');

                if ($count++ == 10) {
                    CLI::error('Timed out waiting for LDAP. Quitting...');
                    return;
                }
            }

            CLI::write('Synchronizing Nextcloud folders ...');
            syncOrganisationFolders();
        } catch (Exception $e) {
            CLI::error("Error synchronizing folders: {$e}");
        } finally {
            $this->releaseLock('nextcloud');
        }
    }

    function acquireLock(string $name): bool
    {
        $path = WRITEPATH . '.lock_' . $name;
        if (file_exists($path)) {
            return false;
        }

        $file = fopen($path, 'w');
        fclose($file);

        return true;
    }

    function isAcquired(string $name): bool
    {
        $path = WRITEPATH . '.lock_' . $name;
        return file_exists($path);
    }

    function releaseLock(string $name): void
    {
        unlink(WRITEPATH . '.lock_' . $name);
    }

    function printTimestamp(): void
    {
        CLI::write(date("d.m.Y H:i:s"));
    }
}
