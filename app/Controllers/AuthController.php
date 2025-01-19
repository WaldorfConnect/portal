<?php

namespace App\Controllers;

use App\Exceptions\Auth\AuthCredentialsInvalidException;
use App\Exceptions\Auth\AuthTFAInvalidException;
use App\Exceptions\Auth\AuthTFARequiredException;
use App\Exceptions\User\UserInactiveException;
use CodeIgniter\HTTP\RedirectResponse;
use ReflectionException;
use Throwable;
use function App\Helpers\createMembershipRequest;
use function App\Helpers\createUser;
use function App\Helpers\getUserByEmail;
use function App\Helpers\getUserById;
use function App\Helpers\login;
use function App\Helpers\logout;
use function App\Helpers\sendConfirmationMail;

class AuthController extends BaseController
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
        $returnUrl = trim($this->request->getPost('return'));

        $loginUrl = site_url('login');
        if ($returnUrl) {
            $loginUrl = $loginUrl . '?return=' . urlencode($returnUrl);
        }

        $redirect = redirect()->to($loginUrl)->withInput()->with('name', $username);

        try {
            login($username, $password, $totp);
            return redirect()->to($returnUrl ?: '/');
        } catch (AuthCredentialsInvalidException $e) {
            return $redirect->with('error', 'Zugangsdaten ungültig!');
        } catch (AuthTFAInvalidException $e) {
            return $this->render('auth/TFAView', ['username' => $username, 'password' => $password, 'return' => $returnUrl, 'error' => 1], false);
        } catch (AuthTFARequiredException $e) {
            return $this->render('auth/TFAView', ['username' => $username, 'password' => $password, 'return' => $returnUrl], false);
        } catch (UserInactiveException $e) {
            return $redirect->with('error', 'Benutzer ist deaktiviert.');
        } catch (ReflectionException $e) {
            return $redirect->with('error', $e);
        }
    }

    public function handleRegister(): string|RedirectResponse
    {
        $firstName = trim($this->request->getPost('firstName'));
        $lastName = trim($this->request->getPost('lastName'));
        $email = trim($this->request->getPost('email'));
        $password = trim($this->request->getPost('password'));
        $confirmedPassword = trim($this->request->getPost('confirmedPassword'));

        // Check password match
        if ($password != $confirmedPassword) {
            log_message('error', "Registration failed(password mismatch): 'firstName={$firstName}, lastName={$lastName}, email={$email}'");
            return redirect('register')
                ->withInput()
                ->with('error', 'Die Passwörter stimmen nicht überein!');
        }

        // Check email validity
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            log_message('error', "Registration failed(invalid email): 'firstName={$firstName}, lastName={$lastName}, email={$email}'");
            return redirect('register')
                ->withInput()
                ->with('error', 'E-Mail-Adresse ist ungültig.');
        }

        // Check email uniqueness
        $user = getUserByEmail($email);
        if ($user) {
            log_message('error', "Registration failed(duplicate email): 'firstName={$firstName}, lastName={$lastName}, email={$email}'");
            return redirect('register')
                ->withInput()
                ->with('error', 'Diese E-Mail-Adresse wird bereits verwendet.');
        }

        try {
            // Create user
            $user = createUser($firstName, $lastName, $email, $password);

            // Inform user of successful registration and sent confirmation email
            return redirect('register')
                ->with('success', 1)
                ->with('userId', $user->getId())
                ->with('email', $email)
                ->with('username', $user->getUsername());
        } catch (Throwable $e) {
            return redirect('register')->withInput()->with('error', $e);
        }
    }

    public function handleRegisterResendConfirmationEmail(): string|RedirectResponse
    {
        $userId = $this->request->getPost('userId');
        $user = getUserById($userId);

        try {
            sendConfirmationMail($user);
        } catch (Throwable $e) {
            return redirect('register')->with('success', 1)->with('resend', 'failure')->with('userId', $userId)->with('email', $user->getEmail());
        }
        return redirect('register')->with('success', 1)->with('resend', 'success')->with('userId', $userId)->with('email', $user->getEmail());
    }

    public function logout(): RedirectResponse
    {
        logout(false);
        return redirect('login');
    }
}
