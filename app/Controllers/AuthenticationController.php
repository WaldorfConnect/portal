<?php

namespace App\Controllers;

use App\Entities\UserStatus;
use CodeIgniter\HTTP\RedirectResponse;
use Exception;
use InvalidArgumentException;
use RuntimeException;
use function App\Helpers\checkSSHA;
use function App\Helpers\createGroupMembershipRequest;
use function App\Helpers\createUser;
use function App\Helpers\generateUsername;
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
            return redirect('login')->with('name', $username)->with('error', 'Benutzername ungültig!');
        }

        if ($user->getStatus() == UserStatus::PENDING_EMAIL) {
            return redirect('login')->with('name', $username)->with('error', 'Bitte bestätige zunächst deine E-Mail-Adresse!');
        }

        if ($user->getStatus() == UserStatus::PENDING_ACCEPT) {
            return redirect('login')->with('name', $username)->with('error', 'Dein Account wurde noch nicht von einem Administrator bestätigt.');
        }

        if (!checkSSHA($password, $user->getPassword())) {
            return redirect('login')->with('name', $username)->with('error', 'Passwort ungültig!');
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
        } catch (InvalidArgumentException) {
            return redirect('register')->with('error', 'Vor- und Nachname ungültig!');
        }

        if ($password != $confirmedPassword) {
            return redirect('register')->with('error', 'Die Passwörter stimmen nicht überein!');
        }

        $hashedPassword = hashSSHA($password);
        $user = createUser($username, $name, $email, $hashedPassword, $schoolId);

        try {
            $id = saveUser($user);
            if ($id == 0)
                throw new RuntimeException();

            foreach ($groupIds as $groupId) {
                createGroupMembershipRequest($id, $groupId);
            }

            sendMail($user->getEmail(), 'Registrierung', 'Demonachricht! Link zur Registrierung: https://portal.waldorfconnect.de/user/confirm?token=' . $user->getToken());

        } catch (Exception $e) {
            return redirect('register')->with('error', $e->getMessage());
        }

        return redirect('register')->with('success', 1);
    }

    public function logout(): RedirectResponse
    {
        session()->remove('user_id');
        return redirect('login');
    }
}
