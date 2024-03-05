<?php

namespace App\Controllers;

use CodeIgniter\HTTP\RedirectResponse;
use Exception;
use Ramsey\Uuid\Uuid;
use function App\Helpers\createImageValidationRule;
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
        $password = trim($this->request->getPost('password'));
        $confirmedPassword = trim($this->request->getPost('confirmedPassword'));

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

    public function handleProfileResendConfirmationEmail(): RedirectResponse
    {
        $userId = $this->request->getPost('userId');
        $user = getUserById($userId);
        try {
            queueMail($userId, 'E-Mail bestätigen', view('mail/ConfirmEmail', ['user' => $user]));
        } catch (Exception $e) {
            return redirect('user/profile')->with('error', 'Fehler beim erneuten Versenden der E-Mail: ' . $e->getMessage());
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
        $confirmedPassword = trim($this->request->getPost('password'));
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
            $user->setPasswordReset(true);

            try {
                saveUser($user);
                queueMail($user->getId(), 'Passwort vergessen', view('mail/ResetPassword', ['user' => $user]));
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
        if ($user->isEmailConfirmed()) {
            return redirect('login')->with('error', 'Dein E-Mail-Adresse ist bereits verifiziert!');
        }

        // Is user a newbie or just changed his email
        $user->setEmailConfirmed(true);
        try {
            saveUser($user);
        } catch (Exception $e) {
            return redirect('login')->with('error', 'Verifikation fehlgeschlagen! (' . $e->getMessage() . ')');
        }

        // If newbie show accept information
        if (!$user->isAccepted()) {
            try {
                queueMail($user->getId(), 'Erwarte Freigabe', view('mail/PendingAcceptEmail', ['user' => $user]));

                // Send announcement to responsible admins
                foreach (getUsers() as $target) {
                    if ($target->mayAccept($user)) {
                        queueMail($target->getId(), 'Neuer Benutzer', view('mail/AnnounceRegistration', ['user' => $target, 'target' => $user]));
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
