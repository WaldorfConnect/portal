<?php

namespace App\Controllers;

use CodeIgniter\HTTP\RedirectResponse;
use DateTime;
use Exception;
use InvalidArgumentException;
use lfkeitel\phptotp\Base32;
use lfkeitel\phptotp\Totp;
use Throwable;
use function App\Helpers\checkSSHA;
use function App\Helpers\createMembershipRequest;
use function App\Helpers\createAndInsertUser;
use function App\Helpers\generateUsername;
use function App\Helpers\getUserByEmail;
use function App\Helpers\getUserById;
use function App\Helpers\getUserByUsername;
use function App\Helpers\hashSSHA;
use function App\Helpers\insertUser;
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

    public function handleLogin(): string|RedirectResponse
    {
        $username = trim($this->request->getPost('username'));
        $password = trim($this->request->getPost('password'));
        $totp = trim($this->request->getPost('totp'));

        $user = getUserByUsername($username);

        $returnUrl = trim($this->request->getPost('return'));
        $loginUrl = site_url('login');
        if ($returnUrl) {
            $loginUrl = $loginUrl . '?return=' . urlencode($returnUrl);
        }

        $redirect = redirect()->to($loginUrl)->withInput()->with('name', $username);

        // Check if user exists
        if (!$user) {
            return $redirect->with('error', 'Benutzername ungültig!');
        }

        // Check if password is correct
        if (!checkSSHA($password, $user->getPassword())) {
            return $redirect->with('error', 'Passwort ungültig!');
        }

        if ($user->getTOTPSecret()) {
            if (!$totp) {
                return $this->render('auth/TOTPView', ['username' => $username, 'password' => $password, 'return' => $returnUrl], false);
            }

            $currentKey = (new Totp())->GenerateToken(Base32::decode($user->getTOTPSecret()));
            if ($currentKey != $totp) {
                return $this->render('auth/TOTPView', ['username' => $username, 'password' => $password, 'return' => $returnUrl, 'error' => 1], false);
            }
        }

        // Check if logging is blocked by current status
        if (!$user->isActive()) {
            return $redirect->with('error', 'Benutzer ist nicht aktiv.');
        }

        // Remove pending password reset if login was successful
        if ($user->isPasswordReset()) {
            $user->setPasswordReset(false);
        }

        try {
            $user->setLastLoginDate(new DateTime());
            saveUser($user);
        } catch (Throwable $e) {
            return $redirect->with('error', $e);
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

        try {
            $user = createAndInsertUser($username, $firstName, $lastName, $email, $hashedPassword);
            foreach ($organisationIds as $organisationId) {
                createMembershipRequest($user->getId(), $organisationId);
            }
            queueMail($user->getId(), 'E-Mail bestätigen', view('mail/ConfirmEmail', ['user' => $user]));
        } catch (Throwable $e) {
            return redirect('register')->withInput()->with('error', $e);
        }

        return redirect('register')->with('success', 1)->with('userId', $id)->with('email', $email)->with('username', $username);
    }

    public function handleRegisterResendConfirmationEmail(): string|RedirectResponse
    {
        $userId = $this->request->getPost('userId');
        $user = getUserById($userId);

        try {
            queueMail($userId, 'E-Mail bestätigen', view('mail/ConfirmEmail', ['user' => $user]));
        } catch (Throwable $e) {
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
