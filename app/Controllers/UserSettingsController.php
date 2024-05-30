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

class UserSettingsController extends BaseController
{
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
        } catch (Throwable $e) {
            return redirect('user/settings')->with('error', $e);
        }
    }
}
