<?php

namespace App\Controllers;

use App\Entities\UserStatus;
use CodeIgniter\HTTP\RedirectResponse;
use Exception;
use function App\Helpers\getUserById;
use function App\Helpers\getUserByUsernameAndPassword;
use function App\Helpers\login;
use function App\Helpers\logout;
use function App\Helpers\saveUser;
use function App\Helpers\sendMail;

class AdminController extends BaseController
{
    public function index(): string
    {
        return $this->render('admin/IndexView');
    }

    public function accept(): string
    {
        return $this->render('admin/AcceptView');
    }

    public function acceptUser(): RedirectResponse
    {
        $userId = $this->request->getPost('id');
        $user = getUserById($userId);

        // User awaiting acceptance
        if ($user->getStatus() != UserStatus::PENDING_ACCEPT) {
            return redirect('admin/users')->with('error', 'Dieser Nutzer wurde bereits akzeptiert.');
        }

        $user->setStatus(UserStatus::OK);
        try {
            saveUser($user);
            sendMail($user->getEmail(), 'Konto freigegeben', view('mail/AccountAccepted', ['user' => $user]));
        } catch (Exception $e) {
            return redirect('admin/users')->with('error', 'Fehler beim Speichern: ' . $e->getMessage());
        }

        return redirect('admin/users')->with('success', $user->getName() . ' erfolgreich freigegeben!');
    }

    public function denyUser(): RedirectResponse
    {
        $userId = $this->request->getPost('id');
        $user = getUserById($userId);

        // User awaiting acceptance
        if ($user->getStatus() != UserStatus::PENDING_ACCEPT) {
            return redirect('admin/users')->with('error', 'Dieser Nutzer wurde bereits abgelehnt.');
        }

        $user->setStatus(UserStatus::DENIED);
        try {
            saveUser($user);
            sendMail($user->getEmail(), 'Kontoerstellung abgelehnt', view('mail/AccountDenied', ['user' => $user]));
        } catch (Exception $e) {
            return redirect('admin/users')->with('error', 'Fehler beim Speichern: ' . $e->getMessage());
        }

        return redirect('admin/users')->with('success', $user->getName() . ' erfolgreich abgelehnt!');
    }

    public function users(): string
    {
        return $this->render('admin/user/UsersView');
    }

    public function groups(): string
    {
        return $this->render('admin/group/GroupsView');
    }

    public function schools(): string
    {
        return $this->render('admin/school/SchoolsView');
    }
}
