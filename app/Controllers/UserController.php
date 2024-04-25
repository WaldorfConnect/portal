<?php

namespace App\Controllers;

use CodeIgniter\HTTP\RedirectResponse;
use Exception;
use lfkeitel\phptotp\Base32;
use lfkeitel\phptotp\Totp;
use Ramsey\Uuid\Uuid;
use Throwable;
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

class UserController extends BaseController
{
    public function profile(): string
    {
        return $this->render('user/EditProfileView', ['user' => getCurrentUser()]);
    }

    public function handleProfile(): RedirectResponse
    {
        $user = getCurrentUser();
        $firstName = trim($this->request->getPost('firstName'));
        $lastName = trim($this->request->getPost('lastName'));
        $email = mb_strtolower(trim($this->request->getPost('email')));

        if (!$this->validate(createImageValidationRule('image'))) {
            return redirect('user/profile')->with('error', join(" ", $this->validator->getErrors()));
        }

        $image = $this->request->getFile('image');

        try {
            // If email changed set status accordingly
            if ($user->getEmail() != $email) {
                if (getUserByEmail($email)) {
                    return redirect('user/profile')->with('error', 'Diese E-Mail wird bereits verwendet.');
                }

                $user->setEmailConfirmed(false);
                queueMail($user->getId(), 'E-Mail bestätigen', view('mail/ConfirmEmail', ['user' => $user]));
            }

            $user->setFirstName($firstName);
            $user->setLastName($lastName);
            $user->setEmail($email);

            if ($image->isValid()) {
                $oldImageId = $user->getImageId();
                $imageId = saveImage($image, '', 100, 400, 400);
                $user->setImageId($imageId);

                // Ensure old image deleted AFTER new one has been set
                if ($oldImageId) {
                    deleteImage($oldImageId);
                }
            }

            saveUser($user);
        } catch (Throwable $e) {
            return redirect('user/profile')->with('error', $e);
        }

        return redirect('user/profile')->with('success', 1);
    }

    public function handleProfileResendConfirmationEmail(): RedirectResponse
    {
        $userId = $this->request->getPost('userId');
        $user = getUserById($userId);
        try {
            queueMail($userId, 'E-Mail bestätigen', view('mail/ConfirmEmail', ['user' => $user]));
        } catch (Throwable $e) {
            return redirect('user/profile')->with('error', $e);
        }
        return redirect('user/profile')->with('success', 1)->with('resendSuccess', 1);
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
                } catch (Exception $e) {
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
            } catch (Exception $e) {
                return redirect('user/reset_password')->with('error', $e);
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
        if ($user->isEmailConfirmed()) {
            return redirect('login')->with('error', 'Dein E-Mail-Adresse ist bereits verifiziert!');
        }

        // Is user a newbie or just changed his email
        $user->setEmailConfirmed(true);
        try {
            saveUser($user);
        } catch (Exception $e) {
            return redirect('login')->with('error', $e);
        }

        // If newbie show accept information
        if (!$user->isAccepted()) {
            try {
                queueMail($user->getId(), 'Erwarte Freigabe', view('mail/PendingAcceptEmail', ['user' => $user]));

                // Send announcement to admins
                foreach (getUsers() as $target) {
                    if ($target->isAdmin()) {
                        createNotification($target->getId(), 'Neuer Benutzer', $user->getName() . ' hat sich erfolgreich registriert und wartet auf Freigabe.');
                    }
                }
            } catch (Exception $e) {
                return redirect('login')->with('error', $e);
            }
            return $this->render('user/ConfirmView', [], false);
        }

        // If known redirect to profile page
        return redirect('user/profile');
    }

    public function settings(): string
    {
        return $this->render('user/SettingsView', ['user' => getCurrentUser()]);
    }

    public function handleSettings(): RedirectResponse
    {
        $self = getCurrentUser();

        $emailNotification = $this->request->getPost('emailNotification');
        $self->setEmailNotification(!is_null($emailNotification));

        $emailNewsletter = $this->request->getPost('emailNewsletter');
        $self->setEmailNewsletter(!is_null($emailNewsletter));

        try {
            saveUser($self);
            return redirect('user/settings')->with('success', 'Einstellungen gespeichert.');
        } catch (Exception $e) {
            return redirect('user/settings')->with('error', $e);
        }
    }

    public function security(): string
    {
        return $this->render('user/SecurityView', ['user' => getCurrentUser()]);
    }

    public function handleSecurity(): string|RedirectResponse
    {
        $user = getCurrentUser();

        $password = trim($this->request->getPost('password'));
        $confirmedPassword = trim($this->request->getPost('confirmedPassword'));
        $totp = $this->request->getPost('totp');

        try {
            // Check if user wants to change password
            if (strlen($password) > 0) {
                // Ensure matching
                if ($password != $confirmedPassword) {
                    return redirect('user/security')->with('error', 'Passwörter stimmen nicht überein.');
                }

                $user->setPassword(hashSSHA($password));
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
}
