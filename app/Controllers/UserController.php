<?php

namespace App\Controllers;

use App\Entities\UserRole;
use App\Entities\UserStatus;
use CodeIgniter\HTTP\RedirectResponse;
use Exception;
use Ramsey\Uuid\Uuid;
use function App\Helpers\getCurrentUser;
use function App\Helpers\getUserByEmail;
use function App\Helpers\getUserById;
use function App\Helpers\getUserByToken;
use function App\Helpers\getUserByUsername;
use function App\Helpers\getUserByUsernameAndEmail;
use function App\Helpers\getUserByUsernameAndPassword;
use function App\Helpers\getUsers;
use function App\Helpers\hashSSHA;
use function App\Helpers\login;
use function App\Helpers\logout;
use function App\Helpers\saveUser;
use function App\Helpers\sendMail;

class UserController extends BaseController
{
    public function profile(): string
    {
        return $this->render('user/EditProfileView', ['user' => getCurrentUser()]);
    }

    public function handleProfile(): RedirectResponse
    {
        $editor = getCurrentUser();

        $id = $this->request->getPost('id');
        $user = getUserById($id);

        $name = trim($this->request->getPost('name'));
        $email = mb_strtolower(trim($this->request->getPost('email')));
        $schoolId = $this->request->getPost('school');
        $role = $this->request->getPost('role');

        $password = trim($this->request->getPost('password'));
        $confirmedPassword = trim($this->request->getPost('confirmedPassword'));

        $redirectUrl = $editor->getId() == $user->getId() ? 'user/profile' : 'admin/user/edit/' . $id;

        if (!$editor->mayManage($user)) {
            return redirect('admin/users')->with('error', 'Du darfst diesen Benutzer nicht bearbeiten.');
        }

        try {
            // If email changed set status accordingly
            if ($user->getEmail() != $email) {
                if (getUserByEmail($email)) {
                    return redirect()->to($redirectUrl)->with('error', 'Diese E-Mail wird bereits verwendet.');
                }

                // Admins may change email without confirmation
                if ($editor->getRole()->isAdmin()) {
                    $token = Uuid::uuid4()->toString();
                    $user->setToken($token);
                    $user->setStatus(UserStatus::PENDING_EMAIL);
                    sendMail($user->getEmail(), 'E-Mail bestätigen', view('mail/ConfirmEmail', ['user' => $user]));
                }
            }

            $user->setName($name);
            $user->setEmail($email);

            // Only apply school and role change if editor is admin
            if ($editor->getRole()->isAdmin()) {
                $user->setSchoolId($schoolId);
                $user->setRole(UserRole::from($role));
            }

            // Check if user wants to change password
            if (strlen($password) > 0) {
                // Ensure matching
                if ($password != $confirmedPassword) {
                    return redirect()->to($redirectUrl)->with('error', 'Passwörter stimmen nicht überein.');
                }

                $user->setPassword(hashSSHA($password));
            }

            saveUser($user);
        } catch (Exception $e) {
            return redirect()->to($redirectUrl)->with('error', 'Fehler beim Speichern: ' . $e->getMessage());
        }

        return redirect()->to($redirectUrl)->with('success', 1);
    }

    public function resetPassword(): string
    {
        // Clicked link in the mail
        $token = $this->request->getGet('token');
        if ($token) {
            $user = getUserByToken($token);

            // Does password still need resetting
            if ($user->getStatus() == UserStatus::PENDING_PWRESET) {
                return $this->render('user/PasswordSetView', ['user' => $user], false);
            }
        }

        return $this->render('user/PasswordResetView', [], false);
    }

    public function handleResetPassword(): string|RedirectResponse
    {
        $password = trim($this->request->getPost('password'));
        $confirmedPassword = trim($this->request->getPost('password'));
        $token = $this->request->getPost('token');

        // Are we performing a legitimate change?
        if ($password && strlen($password) > 0 && $confirmedPassword && $token) {
            $user = getUserByToken($token);

            if ($password != $confirmedPassword) {
                return $this->render('user/PasswordSetView', ['user' => $user], false);
            }

            if ($user->getStatus() == UserStatus::PENDING_PWRESET) {
                $user->setPassword(hashSSHA($password));
                $user->setStatus(UserStatus::OK);

                try {
                    saveUser($user);
                } catch (Exception $e) {
                    return $this->render('user/PasswordSetView', ['user' => $user, 'error' => 'Fehler beim Speichern: ' . $e->getMessage()], false);
                }

                return $this->render('user/PasswordSetView', ['success' => true], false);
            }
        }

        // We are just getting started
        $username = mb_strtolower(trim($this->request->getPost('username')));
        $email = mb_strtolower(trim($this->request->getPost('email')));
        $user = getUserByUsernameAndEmail($username, $email);
        if ($user) {
            $token = Uuid::uuid4()->toString();

            $user->setToken($token);
            $user->setStatus(UserStatus::PENDING_PWRESET);

            try {
                saveUser($user);
                sendMail($user->getEmail(), 'Passwort vergessen', view('mail/ResetPassword', ['user' => $user]));
            } catch (Exception $e) {
                return redirect('user/reset_password')->with('error', 'Fehler beim Speichern: ' . $e->getMessage());
            }
        }

        return redirect('user/reset_password')->with('success', 1);
    }

    public function handleConfirm(): string|RedirectResponse
    {
        $token = $this->request->getGet('token');
        $user = getUserByToken($token);
        if (!$user) {
            return redirect('login')->with('error', 'Ungültiger Verifikationstoken!');
        }

        // Needs user verify their email
        if ($user->getStatus() != UserStatus::PENDING_REGISTER && $user->getStatus() != UserStatus::PENDING_EMAIL) {
            return redirect('login')->with('error', 'Dein E-Mail-Adresse ist bereits verifiziert!');
        }

        // Is user a newbie or just changed his email
        $user->setStatus($user->getStatus() == UserStatus::PENDING_REGISTER ? UserStatus::PENDING_ACCEPT : UserStatus::OK);
        try {
            saveUser($user);
        } catch (Exception $e) {
            return redirect('login')->with('error', 'Verifikation fehlgeschlagen! (' . $e->getMessage() . ')');
        }

        // If newbie show accept information
        if ($user->getStatus() == UserStatus::PENDING_ACCEPT) {
            try {
                sendMail($user->getEmail(), 'Erwarte Freigabe', view('mail/PendingAcceptEmail', ['user' => $user]));

                // Send announcement to all admins
                foreach (getUsers() as $admin) {
                    if ($admin->getRole()->isAdmin() && $admin->mayManage($user)) {
                        sendMail($admin->getEmail(), 'Neuer Benutzer', view('mail/AnnounceRegistration', ['user' => $admin, 'target' => $user]));
                    }
                }
            } catch (Exception $e) {
                return redirect('login')->with('error', 'Verifikation fehlgeschlagen! (' . $e->getMessage() . ')');
            }
            return $this->render('user/ConfirmView', [], false);
        }

        // If known redirect to profile page
        return redirect('user/profile');
    }
}
