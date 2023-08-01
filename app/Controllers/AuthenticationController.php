<?php

namespace App\Controllers;

use App\Entities\UserStatus;
use CodeIgniter\HTTP\RedirectResponse;
use Exception;
use InvalidArgumentException;
use function App\Helpers\checkSSHA;
use function App\Helpers\createGroupMembershipRequest;
use function App\Helpers\createUser;
use function App\Helpers\generateUsername;
use function App\Helpers\getUserByEmail;
use function App\Helpers\getUserByUsername;
use function App\Helpers\getUserByUsernameAndPassword;
use function App\Helpers\hashSSHA;
use function App\Helpers\login;
use function App\Helpers\logout;
use function App\Helpers\saveUser;
use function App\Helpers\sendMail;

class AuthenticationController extends BaseController
{
    public function login(): string|RedirectResponse
    {
        return $this->render('auth/LoginView', [], false);
    }

    public function register(): string
    {
        return $this->render('auth/RegisterView', [], false);
    }

    public function handleLogin(): RedirectResponse
    {
        $username = trim($this->request->getPost('username'));
        $password = trim($this->request->getPost('password'));
        $user = getUserByUsername($username);

        if (!$user) {
            return redirect('login')->withInput()->with('name', $username)->with('error', 'Benutzername ungültig!');
        }

        if ($user->getStatus() == UserStatus::PENDING_REGISTER) {
            return redirect('login')->withInput()->with('name', $username)->with('error', 'Bitte schließe deine Registrierung zunächst ab, indem du deine E-Mail-Adresse bestätigst!');
        }

        if ($user->getStatus() == UserStatus::PENDING_ACCEPT) {
            return redirect('login')->withInput()->with('name', $username)->with('error', 'Dein Konto wurde noch nicht von einem Administrator freigegeben.');
        }

        if (!checkSSHA($password, $user->getPassword())) {
            return redirect('login')->withInput()->with('name', $username)->with('error', 'Passwort ungültig!');
        }

        // Remove pending password reset if login was successful
        if ($user->getStatus() == UserStatus::PENDING_PWRESET) {
            $user->setStatus(UserStatus::OK);
            try {
                saveUser($user);
            } catch (Exception $e) {
                return redirect('login')->withInput()->with('name', $username)->with('error', 'Fehler beim Speichern: ' . $e->getMessage());
            }
        }

        session()->set('user_id', $user->getId());

        return redirect('/');
    }

    public function handleRegister(): string|RedirectResponse
    {
        $name = trim($this->request->getPost('name'));
        $email = trim($this->request->getPost('email'));
        $password = trim($this->request->getPost('password'));
        $confirmedPassword = trim($this->request->getPost('confirmedPassword'));
        $schoolId = trim($this->request->getPost('school'));
        $groupIds = $this->request->getPost('groups');

        try {
            $username = generateUsername($name);
            $user = getUserByUsername($username);

            // If name is already taken add a number
            if ($user) {
                $id = 2;
                $newUsername = $username;

                // Increment number till name is no longer taken
                while (getUserByUsername($newUsername)) {
                    $newUsername = $username . $id;
                    $id++;
                }

                $username = $newUsername;
            }
        } catch (InvalidArgumentException) {
            return redirect('register')->withInput()->with('error', 'Vor- und Nachname ungültig!');
        }

        if ($password != $confirmedPassword) {
            return redirect('register')->withInput()->with('error', 'Die Passwörter stimmen nicht überein!');
        }

        $user = getUserByEmail($email);
        if ($user) {
            return redirect('register')->withInput()->with('error', 'Diese E-Mail-Adresse wird bereits verwendet.');
        }

        $hashedPassword = hashSSHA($password);
        $user = createUser($username, $name, $email, $hashedPassword, $schoolId);

        try {
            $id = saveUser($user);
            foreach ($groupIds as $groupId) {
                createGroupMembershipRequest($id, $groupId);
            }
            sendMail($user->getEmail(), 'E-Mail bestätigen', view('mail/ConfirmEmail', ['user' => $user]));
        } catch (Exception $e) {
            return redirect('register')->withInput()->with('error', 'Fehler beim Speichern: ' . $e->getMessage());
        }

        return redirect('register')->with('success', 1);
    }

    public function logout(): RedirectResponse
    {
        session()->remove('user_id');
        return redirect('login');
    }
}
