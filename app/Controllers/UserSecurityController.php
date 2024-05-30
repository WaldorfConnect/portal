<?php

namespace App\Controllers;

use CodeIgniter\HTTP\RedirectResponse;
use Exception;
use lfkeitel\phptotp\Base32;
use lfkeitel\phptotp\Totp;
use Ramsey\Uuid\Uuid;
use Throwable;
use function App\Helpers\checkSSHA;
use function App\Helpers\createImageValidationRule;
use function App\Helpers\createNotification;
use function App\Helpers\deleteImage;
use function App\Helpers\getCurrentUser;
use function App\Helpers\getUserByEmail;
use function App\Helpers\getUserById;
use function App\Helpers\getUserByToken;
use function App\Helpers\getUserByUsernameAndEmail;
use function App\Helpers\getUsers;
use function App\Helpers\hashSSHA;
use function App\Helpers\saveImage;
use function App\Helpers\saveUser;
use function App\Helpers\queueMail;

class UserSecurityController extends BaseController
{
    public function security(): string
    {
        return $this->render('user/SecurityView', ['user' => getCurrentUser()]);
    }

    public function handleSecurity(): string|RedirectResponse
    {
        $user = getCurrentUser();

        $currentPassword = trim($this->request->getPost('currentPassword'));
        $newPassword = trim($this->request->getPost('newPassword'));
        $confirmedNewPassword = trim($this->request->getPost('confirmedNewPassword'));

        $totp = trim($this->request->getPost('totp'));

        try {
            // Check if user wants to change password
            if (strlen($currentPassword) > 0 && strlen($newPassword) > 0 && strlen($confirmedNewPassword) > 0) {
                // Check if current password is correct
                if (!checkSSHA($currentPassword, $user->getPassword())) {
                    return redirect('user/security')->with('error', 'Aktuelles Passwort ungültig.');
                }

                // Ensure that confirmation matches
                if ($newPassword != $confirmedNewPassword) {
                    return redirect('user/security')->with('error', 'Passwörter stimmen nicht überein.');
                }

                $user->setPassword(hashSSHA($newPassword));
                queueMail($user->getId(), 'Passwort geändert', view('mail/PasswordChanged', ['user' => $user]));
            }

            if ($totp && !$user->getTOTPSecret()) {
                $secret = Totp::GenerateSecret();

                return $this->render('user/SecurityTOTPView', ['user' => getCurrentUser(), 'secret' => Base32::encode($secret)]);
            } else if (!$totp && $user->getTOTPSecret()) {
                $user->setTOTPSecret(null);
            }

            saveUser($user);
        } catch (Throwable $t) {
            return redirect('user/security')->with('error', $t);
        }

        return redirect('user/security')->with('success', 'Einstellungen gespeichert.');
    }

    public function handleTOTPEnable(): RedirectResponse
    {
        $user = getCurrentUser();

        $secret = $this->request->getPost('secret');
        $key = $this->request->getPost('key');

        $currentKey = (new Totp())->GenerateToken(Base32::decode($secret));

        if ($currentKey == $key) {
            try {
                $user->setTOTPSecret($secret);
                saveUser($user);

                return redirect('user/security')->with('success', 'Einstellungen gespeichert.');
            } catch (Throwable $t) {
                return redirect('user/security')->with('error', $t);
            }
        }

        return redirect('user/security')->with('error', 'Authentifizierungscode ungültig.');
    }

    public function resetPassword(): string
    {
        // Clicked link in the mail
        $token = $this->request->getGet('token');
        if ($token) {
            $user = getUserByToken($token);

            // Does password still need resetting
            if ($user->isPasswordReset()) {
                return $this->render('user/PasswordSetView', ['user' => $user], false);
            }
        }

        return $this->render('user/PasswordResetView', [], false);
    }

    public function handleResetPassword(): string|RedirectResponse
    {
        $password = trim($this->request->getPost('password'));
        $confirmedPassword = trim($this->request->getPost('confirmedPassword'));
        $token = $this->request->getPost('token');

        // Are we performing a legitimate change?
        if ($password && strlen($password) > 0 && $confirmedPassword && $token) {
            $user = getUserByToken($token);

            if ($password != $confirmedPassword) {
                return $this->render('user/PasswordSetView', ['user' => $user], false);
            }

            if ($user->isPasswordReset()) {
                $user->setPassword(hashSSHA($password));
                $user->setPasswordReset(false);

                try {
                    saveUser($user);
                    queueMail($user->getId(), 'Passwort geändert', view('mail/PasswordChanged', ['user' => $user]));
                } catch (Throwable $e) {
                    return $this->render('user/PasswordSetView', ['user' => $user, 'error' => $e], false);
                }

                return $this->render('user/PasswordSetView', ['success' => true], false);
            }
        }

        // We are just getting started
        $username = mb_strtolower(trim($this->request->getPost('username')));
        $email = mb_strtolower(trim($this->request->getPost('email')));
        $user = getUserByUsernameAndEmail($username, $email);
        if ($user) {
            $user->setPasswordReset(true);

            try {
                saveUser($user);
                queueMail($user->getId(), 'Passwort vergessen', view('mail/ResetPassword', ['user' => $user]));
            } catch (Throwable $e) {
                return redirect('user/reset_password')->with('error', $e);
            }
        }

        return redirect('user/reset_password')->with('success', 'Passwort zurückgesetzt.');
    }
}
