<?php

namespace App\Controllers;

use App\Entities\UserStatus;
use CodeIgniter\HTTP\RedirectResponse;
use Exception;
use Ramsey\Uuid\Uuid;
use function App\Helpers\getCurrentUser;
use function App\Helpers\getUserByEmail;
use function App\Helpers\getUserByToken;
use function App\Helpers\getUserByUsernameAndEmail;
use function App\Helpers\getUserByUsernameAndPassword;
use function App\Helpers\hashSSHA;
use function App\Helpers\login;
use function App\Helpers\logout;
use function App\Helpers\saveUser;
use function App\Helpers\sendMail;

class UserController extends BaseController
{
    public function profile(): string
    {
        return $this->render('user/EditProfileView');
    }

    public function handleProfile(): RedirectResponse
    {
        $name = trim($this->request->getPost('name'));
        $email = mb_strtolower(trim($this->request->getPost('email')));
        $schoolId = $this->request->getPost('school');

        $password = trim($this->request->getPost('password'));
        $confirmedPassword = trim($this->request->getPost('confirmedPassword'));

        $user = getCurrentUser();

        try {
            // If email changed set status accordingly
            if ($user->getEmail() != $email) {
                if (getUserByEmail($email)) {
                    return redirect('user/profile')->with('error', 'Diese E-Mail wird bereits verwendet.');
                }

                $token = Uuid::uuid4()->toString();
                $user->setToken($token);
                $user->setStatus(UserStatus::PENDING_EMAIL);
                sendMail($user->getEmail(), 'E-Mail bestätigen', view('mail/ConfirmEmail', ['user' => $user]));
            }

            $user->setName($name);
            $user->setEmail($email);
            $user->setSchoolId($schoolId);

            // Check if user wants to change password
            if (strlen($password) > 0) {
                // Ensure matching
                if ($password != $confirmedPassword) {
                    return redirect('user/profile')->with('error', 'Passwörter stimmen nicht überein.');
                }

                $user->setPassword(hashSSHA($password));
            }

            saveUser($user);
        } catch (Exception $e) {
            return redirect('user/profile')->with('error', 'Fehler beim Speichern: ' . $e->getMessage());
        }

        return redirect('user/profile')->with('success', 1);
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
            return $this->render('user/ConfirmView', [], false);
        }

        // If known redirect to profile page
        return redirect('user/profile');
    }
}
