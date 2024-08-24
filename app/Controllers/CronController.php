<?php

namespace App\Controllers;

use Throwable;
use function App\Helpers\openLDAPConnection;
use function App\Helpers\queueNotificationMails;
use function App\Helpers\syncLDAPGroups;
use function App\Helpers\syncLDAPUsers;
use function App\Helpers\syncGroupFolders;
use function App\Helpers\workMailQueue;

class CronController extends BaseController
{
    public function mail(): void
    {
        if (!$this->acquireLock('mail')) {
            return;
        }

        try {
            log_message('info', 'Working mail queue ...');
            workMailQueue();
        } catch (Throwable $e) {
            log_message('error', 'Error working mail queue: {exception}', ['exception' => $e]);
        } finally {
            $this->releaseLock('mail');
        }
    }

    public function notifications(): void
    {
        if (!$this->acquireLock('notifications')) {
            return;
        }

        try {
            log_message('info', 'Queueing notifications mails ...');
            queueNotificationMails();
        } catch (Throwable $e) {
            log_message('error', 'Error queueing notifications mails: {exception}', ['exception' => $e]);
        } finally {
            $this->releaseLock('notifications');
        }
    }

    public function ldap(): void
    {
        if (!$this->acquireLock('ldap')) {
            return;
        }

        try {
            $count = 0;
            while ($this->isAcquired('nextcloud')) {
                sleep(1);
                log_message('info', "Waiting for Nextcloud sync to finish ... [$count]");

                if ($count++ == 30) {
                    log_message('error', 'Timed out waiting for Nextcloud. Aborting!');
                    return;
                }
            }

            log_message('info', 'Opening LDAP connection ...');
            openLDAPConnection();

            log_message('info', 'Synchronizing LDAP users ...');
            syncLDAPUsers();

            log_message('info', 'Synchronizing LDAP groups ...');
            syncLDAPGroups();
        } catch (Throwable $e) {
            log_message('error', 'Error synchronizing with LDAP: {exception}', ['exception' => $e]);
        } finally {
            $this->releaseLock('ldap');
        }
    }

    public function nextcloud(): void
    {
        if (!$this->acquireLock('nextcloud')) {
            return;
        }

        try {
            $count = 0;
            while ($this->isAcquired('ldap')) {
                sleep(1);
                log_message('info', "Waiting for LDAP sync to finish ... [$count]");

                if ($count++ == 10) {
                    log_message('error', 'Timed out waiting for LDAP. Aborting!');
                    return;
                }
            }

            log_message('info', 'Synchronizing Nextcloud folders and chats ...');
            syncGroupFolders();
            // syncOrganisationChats();
        } catch (Throwable $e) {
            log_message('error', 'Error synchronizing cloud folders: {exception}', ['exception' => $e]);
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

        log_message('info', "Lock '{$name}' acquired");
        return true;
    }

    function isAcquired(string $name): bool
    {
        $path = WRITEPATH . '.lock_' . $name;
        return file_exists($path);
    }

    function releaseLock(string $name): void
    {
        log_message('info', "Lock '{$name}' released");
        unlink(WRITEPATH . '.lock_' . $name);
    }
}
