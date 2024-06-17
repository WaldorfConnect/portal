<?php

namespace App\Controllers\User;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\RedirectResponse;
use Throwable;
use function App\Helpers\createImageValidationRule;
use function App\Helpers\createNotification;
use function App\Helpers\deleteImage;
use function App\Helpers\getCurrentUser;
use function App\Helpers\getUserByEmail;
use function App\Helpers\getUserById;
use function App\Helpers\getUserByToken;
use function App\Helpers\getUsers;
use function App\Helpers\queueMail;
use function App\Helpers\saveImage;
use function App\Helpers\saveUser;

class UserProfileController extends BaseController
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

        return redirect('user/profile')->with('success', 'Profil gespeichert.');
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

        return redirect('user/profile')->with('success', 'Profil gespeichert.')->with('resendSuccess', 1);
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
        } catch (Throwable $e) {
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
            } catch (Throwable $e) {
                return redirect('login')->with('error', $e);
            }
            return $this->render('user/ConfirmView', [], false);
        }

        // If known redirect to profile page
        return redirect('user/profile');
    }
}
