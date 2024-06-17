<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\RedirectResponse;
use Throwable;
use function App\Helpers\deleteUser;
use function App\Helpers\getCurrentUser;
use function App\Helpers\getUserById;
use function App\Helpers\hashSSHA;
use function App\Helpers\queueMail;
use function App\Helpers\saveUser;

class AdminUserController extends BaseController
{
    public function users(): string
    {
        return $this->render('admin/user/UsersView');
    }

    public function editUser(int $userId): string|RedirectResponse
    {
        $user = getUserById($userId);
        if (!$user) {
            log_message('warn', getCurrentUser()->getUsername() . ' tried to edit invalid user ' . $userId);
            return redirect('admin/users')->with('error', 'Unbekannter Benutzer.');
        }

        return $this->render('admin/user/UserEditView', ['user' => $user]);
    }

    public function handleEditUser(): RedirectResponse
    {
        $userId = $this->request->getPost('id');
        $user = getUserById($userId);

        if (!$user) {
            log_message('warn', getCurrentUser()->getUsername() . ' tried to edit invalid user ' . $userId);
            return redirect('admin/users')->with('error', 'Unbekannter Benutzer.');
        }

        $firstName = trim($this->request->getPost('firstName'));
        $lastName = trim($this->request->getPost('lastName'));
        $email = trim($this->request->getPost('email'));
        $password = trim($this->request->getPost('password'));
        $confirmedPassword = trim($this->request->getPost('confirmedPassword'));

        $user->setFirstName($firstName);
        $user->setLastName($lastName);
        $user->setEmail($email);

        // Check if user wants to change password
        if (strlen($password) > 0) {
            // Ensure matching
            if ($password != $confirmedPassword) {
                log_message('warn', getCurrentUser()->getUsername() . ' entered unequal passwords when trying to edit ' . $user->getUsername());
                return redirect()->to('admin/user/edit/' . $userId)->with('error', 'Passwörter stimmen nicht überein.');
            }

            $user->setPassword(hashSSHA($password));
        }

        try {
            saveUser($user);
            log_message('info', getCurrentUser()->getUsername() . ' edited ' . $user->getUsername());
            return redirect('admin/users')->with('success', 'Benutzer bearbeitet.');
        } catch (Throwable $e) {
            log_message('error', 'Unable to edit user ' . $user->getUsername() . ': {exception}', ['exception' => $e]);
            return redirect('admin/users')->with('error', $e);
        }
    }

    public function handleDeleteUser(): RedirectResponse
    {
        $userId = $this->request->getPost('id');
        $user = getUserById($userId);
        if (!$user) {
            log_message('warn', getCurrentUser()->getUsername() . ' tried to delete invalid user ' . $userId);
            return redirect('admin/users')->with('error', 'Unbekannter Benutzer.');
        }

        try {
            deleteUser($userId);
            log_message('info', getCurrentUser()->getUsername() . ' deleted ' . $user->getUsername());
            return redirect('admin/users')->with('success', 'Benutzer gelöscht.');
        } catch (Throwable $e) {
            log_message('error', 'Unable to delete user ' . $user->getUsername() . ': {exception}', ['exception' => $e]);
            return redirect('admin/users')->with('error', $e);
        }
    }

    public function acceptUser(): RedirectResponse
    {
        $userId = $this->request->getPost('id');
        $user = getUserById($userId);

        if ($user->isAccepted()) {
            log_message('warn', getCurrentUser()->getUsername() . ' tried to accept already accepted user ' . $user->getUsername());
            return redirect('admin/users')->with('error', 'Dieser Nutzer wurde bereits akzeptiert.');
        }

        $user->setAcceptDate(new DateTime());
        $user->setActive(true);

        try {
            saveUser($user);
            queueMail($user->getId(), 'Konto freigegeben', view('mail/AccountAccepted', ['user' => $user]));
        } catch (Throwable $e) {
            log_message('error', 'Unable to accept user ' . $user->getUsername() . ': {exception}', ['exception' => $e]);
            return redirect('admin/users')->with('error', $e);
        }

        return redirect('admin/users')->with('success', $user->getName() . ' erfolgreich freigegeben!');
    }

    public function activateUser(): RedirectResponse
    {
        $userId = $this->request->getPost('id');
        $user = getUserById($userId);

        if ($user->isActive()) {
            log_message('warn', getCurrentUser()->getUsername() . ' tried to activate already active user ' . $user->getUsername());
            return redirect('admin/users')->with('error', 'Dieser Nutzer ist bereits aktiv.');
        }

        $user->setActive(true);

        try {
            saveUser($user);
        } catch (Throwable $e) {
            log_message('error', 'Unable to activate user ' . $user->getUsername() . ': {exception}', ['exception' => $e]);
            return redirect('admin/users')->with('error', $e);
        }

        return redirect('admin/users')->with('success', $user->getName() . ' erfolgreich aktiviert!');
    }

    public function deactivateUser(): RedirectResponse
    {
        $userId = $this->request->getPost('id');
        $user = getUserById($userId);
        if (!$user->isActive()) {
            log_message('warn', getCurrentUser()->getUsername() . ' tried to deactivate already inactive user ' . $user->getUsername());
            return redirect('admin/users')->with('error', 'Dieser Nutzer ist bereits deaktiviert.');
        }

        $user->setActive(false);

        try {
            saveUser($user);
        } catch (Throwable $e) {
            log_message('error', 'Unable to deactivate user ' . $user->getUsername() . ': {exception}', ['exception' => $e]);
            return redirect('admin/users')->with('error', $e);
        }

        return redirect('admin/users')->with('success', $user->getName() . ' erfolgreich deaktiviert!');
    }
}