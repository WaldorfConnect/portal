<?php

namespace App\Controllers;

use Cassandra\Date;
use CodeIgniter\HTTP\RedirectResponse;
use DateTime;
use Exception;
use InvalidArgumentException;
use function App\Helpers\checkSSHA;
use function App\Helpers\createMembershipRequest;
use function App\Helpers\createUser;
use function App\Helpers\generateUsername;
use function App\Helpers\getUserByEmail;
use function App\Helpers\getUserById;
use function App\Helpers\getUserByUsername;
use function App\Helpers\hashSSHA;
use function App\Helpers\saveUser;
use function App\Helpers\queueMail;

class AuthenticationController extends BaseController
{
    public function login(): string|RedirectResponse
    {
        $returnUrl = $this->request->getGet('return');
        if ($returnUrl) {
            return $this->render('auth/LoginView', ['return' => $returnUrl], false);
        }

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

        $returnUrl = $this->request->getPost('return');
        $loginUrl = site_url('login');
        if ($returnUrl) {
            $loginUrl = $loginUrl . '?return=' . urlencode($returnUrl);
        }

        // Check if user exists
        if (!$user) {
            return redirect()->to($loginUrl)->withInput()->with('name', $username)->with('error', 'Benutzername ungültig!');
        }

        // Check if password is correct
        if (!checkSSHA($password, $user->getPassword())) {
            return redirect()->to($loginUrl)->withInput()->with('name', $username)->with('error', 'Passwort ungültig!');
        }

        // Check if logging is blocked by current status
        if (!$user->isActive()) {
            return redirect()->to($loginUrl)->withInput()->with('name', $username)->with('error', 'Benutzer ist nicht aktiv.');
        }

        // Remove pending password reset if login was successful
        if ($user->isPasswordReset()) {
            $user->setPasswordReset(false);
        }

        try {
            $user->setLastLoginDate(new DateTime());
            saveUser($user);
        } catch (Exception $e) {
            return redirect()->to($loginUrl)->withInput()->with('name', $username)->with('error', 'Fehler beim Speichern: ' . $e->getMessage());
        }

        // Everything worked - welcome!
        session()->set('user_id', $user->getId());

        // Check if user is returning and redirect
        return redirect()->to($returnUrl ?: '/');
    }

    public function handleRegister(): string|RedirectResponse
    {
        $firstName = trim($this->request->getPost('firstName'));
        $lastName = trim($this->request->getPost('lastName'));
        $email = trim($this->request->getPost('email'));
        $password = trim($this->request->getPost('password'));
        $confirmedPassword = trim($this->request->getPost('confirmedPassword'));
        $organisationIds = $this->request->getPost('organisations');

        try {
            $username = generateUsername($firstName, $lastName);
            $user = getUserByUsername($username);

            // If name is already taken add a number
            if ($user) {
                $id = 2;
                $newUsername = $username . $id;

                // Increment number till name is no longer taken
                while (getUserByUsername($newUsername)) {
                    $newUsername = $username . ++$id;
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
        $user = createUser($username, $firstName, $lastName, $email, $hashedPassword);

        try {
            $id = saveUser($user);
            foreach ($organisationIds as $organisationId) {
                createMembershipRequest($id, $organisationId);
            }
            queueMail($id, 'E-Mail bestätigen', view('mail/ConfirmEmail', ['user' => $user]));
        } catch (Exception $e) {
            return redirect('register')->withInput()->with('error', 'Fehler beim Speichern: ' . $e->getMessage());
        }

        return redirect('register')->with('success', 1)->with('userId', $id)->with('email', $email)->with('username', $username);
    }

    public function handleRegisterResendConfirmationEmail(): string|RedirectResponse
    {
        $userId = $this->request->getPost('userId');
        $user = getUserById($userId);
        try {
            queueMail($userId, 'E-Mail bestätigen', view('mail/ConfirmEmail', ['user' => $user]));
        } catch (Exception $e) {
            return redirect('register')->with('success', 1)->with('resend', 'failure')->with('userId', $userId)->with('email', $user->getEmail());
        }
        return redirect('register')->with('success', 1)->with('resend', 'success')->with('userId', $userId)->with('email', $user->getEmail());
    }

    public function logout(): RedirectResponse
    {
        session()->remove('user_id');
        return redirect('login');
    }
}
