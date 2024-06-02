<?php

namespace App\Controllers\user;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\RedirectResponse;
use Throwable;
use function App\Helpers\getCurrentUser;
use function App\Helpers\saveUser;

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
