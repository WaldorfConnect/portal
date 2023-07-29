<?php

namespace App\Controllers;

use App\Entities\UserStatus;
use CodeIgniter\HTTP\RedirectResponse;
use Exception;
use function App\Helpers\getUserByToken;
use function App\Helpers\getUserByUsernameAndPassword;
use function App\Helpers\login;
use function App\Helpers\logout;
use function App\Helpers\saveUser;

class UserController extends BaseController
{
    public function profile(): string
    {
        return $this->render('user/EditProfileView');
    }

    public function handleProfile(): RedirectResponse
    {

    }

    public function resetPassword(): string
    {
        return $this->render('user/PasswortResetView', [], false);
    }

    public function handleResetPassword(): RedirectResponse
    {

    }

    public function handleConfirm(): string|RedirectResponse
    {
        $token = $this->request->getGet('token');
        $user = getUserByToken($token);
        if (!$user) {
            return redirect('login')->with('error', 'Ungültiger Verifikationstoken!');
        }

        if ($user->getStatus() != UserStatus::PENDING_EMAIL) {
            return redirect('login')->with('error', 'Dein Account wurde bereits verifiziert!');
        }

        $user->setStatus(UserStatus::PENDING_ACCEPT);
        try {
            saveUser($user);
        } catch (Exception $e) {
            return redirect('login')->with('error', 'Verifikation fehlgeschlagen! (' . $e->getMessage() . ')');
        }

        return $this->render('user/ConfirmView', [], false);
    }
}
