<?php

namespace App\Controllers;

use CodeIgniter\HTTP\RedirectResponse;
use DateTime;
use Exception;
use Throwable;
use function App\Helpers\insertOrganisation;
use function App\Helpers\saveImage;
use function App\Helpers\createOrganisation;
use function App\Helpers\createImageValidationRule;
use function App\Helpers\createAndInsertRegion;
use function App\Helpers\deleteOrganisation;
use function App\Helpers\deleteRegion;
use function App\Helpers\deleteUser;
use function App\Helpers\getCurrentUser;
use function App\Helpers\getOrganisationById;
use function App\Helpers\getRegionById;
use function App\Helpers\getUserById;
use function App\Helpers\hashSSHA;
use function App\Helpers\saveOrganisation;
use function App\Helpers\saveRegion;
use function App\Helpers\saveUser;
use function App\Helpers\queueMail;

class AdminController extends BaseController
{
    public function index(): string
    {
        return $this->render('admin/IndexView');
    }

    public function debug(): string
    {
        return $this->render('admin/DebugView');
    }

    public function accept(): string
    {
        return $this->render('admin/AcceptView');
    }

    public function acceptUser(): RedirectResponse
    {
        $userId = $this->request->getPost('id');
        $user = getUserById($userId);

        if ($user->isAccepted()) {
            log_message('warn', getCurrentUser()->getUsername() . ' tried to accept already accepted user ' . $user->getUsername());
            return redirect('admin/users')->with('error', 'Dieser Nutzer wurde bereits akzeptiert.');
        }

        $user->setAcceptDate(new DateTime());
        $user->setActive(true);

        try {
            saveUser($user);
            queueMail($user->getId(), 'Konto freigegeben', view('mail/AccountAccepted', ['user' => $user]));
        } catch (Throwable $e) {
            log_message('error', 'Unable to accept user ' . $user->getUsername() . ': {exception}', ['exception' => $e]);
            return redirect('admin/users')->with('error', $e);
        }

        return redirect('admin/users')->with('success', $user->getName() . ' erfolgreich freigegeben!');
    }

    public function activateUser(): RedirectResponse
    {
        $userId = $this->request->getPost('id');
        $user = getUserById($userId);

        if ($user->isActive()) {
            log_message('warn', getCurrentUser()->getUsername() . ' tried to activate already active user ' . $user->getUsername());
            return redirect('admin/users')->with('error', 'Dieser Nutzer ist bereits aktiv.');
        }

        $user->setActive(true);

        try {
            saveUser($user);
        } catch (Throwable $e) {
            log_message('error', 'Unable to activate user ' . $user->getUsername() . ': {exception}', ['exception' => $e]);
            return redirect('admin/users')->with('error', $e);
        }

        return redirect('admin/users')->with('success', $user->getName() . ' erfolgreich aktiviert!');
    }

    public function deactivateUser(): RedirectResponse
    {
        $userId = $this->request->getPost('id');
        $user = getUserById($userId);
        if (!$user->isActive()) {
            log_message('warn', getCurrentUser()->getUsername() . ' tried to deactivate already inactive user ' . $user->getUsername());
            return redirect('admin/users')->with('error', 'Dieser Nutzer ist bereits deaktiviert.');
        }

        $user->setActive(false);

        try {
            saveUser($user);
        } catch (Throwable $e) {
            log_message('error', 'Unable to deactivate user ' . $user->getUsername() . ': {exception}', ['exception' => $e]);
            return redirect('admin/users')->with('error', $e);
        }

        return redirect('admin/users')->with('success', $user->getName() . ' erfolgreich deaktiviert!');
    }

    public function users(): string
    {
        return $this->render('admin/user/UsersView');
    }

    public function editUser(int $userId): string|RedirectResponse
    {
        $user = getUserById($userId);
        if (!$user) {
            log_message('warn', getCurrentUser()->getUsername() . ' tried to edit invalid user ' . $userId);
            return redirect('admin/users')->with('error', 'Unbekannter Benutzer.');
        }

        return $this->render('admin/user/UserEditView', ['user' => $user]);
    }

    public function handleEditUser(): RedirectResponse
    {
        $userId = $this->request->getPost('id');
        $user = getUserById($userId);

        if (!$user) {
            log_message('warn', getCurrentUser()->getUsername() . ' tried to edit invalid user ' . $userId);
            return redirect('admin/users')->with('error', 'Unbekannter Benutzer.');
        }

        $firstName = trim($this->request->getPost('firstName'));
        $lastName = trim($this->request->getPost('lastName'));
        $email = trim($this->request->getPost('email'));
        $password = trim($this->request->getPost('password'));
        $confirmedPassword = trim($this->request->getPost('confirmedPassword'));

        $user->setFirstName($firstName);
        $user->setLastName($lastName);
        $user->setEmail($email);

        // Check if user wants to change password
        if (strlen($password) > 0) {
            // Ensure matching
            if ($password != $confirmedPassword) {
                log_message('warn', getCurrentUser()->getUsername() . ' entered unequal passwords upon updating ' . $user->getUsername());
                return redirect()->to('admin/user/edit/' . $userId)->with('error', 'Passwörter stimmen nicht überein.');
            }

            $user->setPassword(hashSSHA($password));
        }

        try {
            saveUser($user);

            log_message('info', getCurrentUser()->getUsername() . ' edited ' . $user->getUsername());
            return redirect('admin/users')->with('success', 'Benutzer bearbeitet.');
        } catch (Throwable $e) {
            log_message('error', 'Unable to edit user ' . $user->getUsername() . ': {exception}', ['exception' => $e]);
            return redirect('admin/users')->with('error', $e);
        }
    }

    public function handleDeleteUser(): RedirectResponse
    {
        $userId = $this->request->getPost('id');
        $user = getUserById($userId);
        if (!$user) {
            log_message('warn', getCurrentUser()->getUsername() . ' tried to delete invalid user ' . $userId);
            return redirect('admin/users')->with('error', 'Unbekannter Benutzer.');
        }

        try {
            deleteUser($userId);

            log_message('info', getCurrentUser()->getUsername() . ' deleted ' . $user->getUsername());
            return redirect('admin/users')->with('success', 'Benutzer gelöscht.');
        } catch (Throwable $e) {
            log_message('error', 'Unable to delete user ' . $user->getUsername() . ': {exception}', ['exception' => $e]);
            return redirect('admin/users')->with('error', $e);
        }
    }

    public function organisations(): string
    {
        return $this->render('admin/organisation/OrganisationsView');
    }

    public function createOrganisation(): string
    {
        return $this->render('admin/organisation/OrganisationCreateView');
    }

    public function handleCreateOrganisation(): RedirectResponse
    {
        $name = trim($this->request->getPost('name'));
        $shortName = trim($this->request->getPost('shortName'));
        $websiteUrl = trim($this->request->getPost('websiteUrl'));
        $regionId = $this->request->getPost('region');
        $region = getRegionById($regionId);

        if (str_contains($name, '/') || str_contains($shortName, '/')) {
            log_message('warn', getCurrentUser()->getUsername() . ' used invalid characters in organisation name');
            return redirect()->back()->withInput()->with('error', 'Ungültige Zeichen im Organisationsnamen.');
        }

        if (!$region) {
            log_message('warn', getCurrentUser()->getUsername() . ' tried to created organisation in invalid region ' . $regionId);
            return redirect()->back()->withInput()->with('error', 'Unbekannte Region.');
        }

        $organisation = createOrganisation($name, $shortName, $regionId);
        $organisation->setWebsite($websiteUrl);

        try {
            // 1. Prevent a logo/image from being uploaded that is not image or bigger than 1/2MB
            if (!$this->validate(createImageValidationRule('logo', 1000, true))) {
                return redirect()->back()->withInput()->with('error', $this->validator->getErrors());
            }
            if (!$this->validate(createImageValidationRule('image'))) {
                return redirect()->back()->withInput()->with('error', $this->validator->getErrors());
            }

            $logoFile = $this->request->getFile('logo');
            $logoAuthor = trim($this->request->getPost('logoAuthor'));

            $imageFile = $this->request->getFile('image');
            $imageAuthor = trim($this->request->getPost('imageAuthor'));

            // 2. If a logo/image was uploaded save it | Logos may be SVGs, all other formats are converted to WEBP
            if ($logoFile->isValid()) {
                $logoId = saveImage($logoFile, $logoAuthor);
                $organisation->setLogoId($logoId);
            }
            if ($imageFile->isValid()) {
                $imageId = saveImage($imageFile, $imageAuthor);
                $organisation->setImageId($imageId);
            }

            insertOrganisation($organisation);
            log_message('info', getCurrentUser()->getUsername() . ' created organisation ' . $organisation->getDisplayName());

            return redirect('admin/organisations')->with('success', 'Organisation erstellt.');
        } catch (Throwable $e) {
            log_message('error', 'Unable to create organisation ' . $organisation->getDisplayName() . ': {exception}', ['exception' => $e]);
            return redirect()->back()->withInput()->with('error', $e);
        }
    }

    public function handleDeleteOrganisation(): RedirectResponse
    {
        $self = getCurrentUser();
        $organisationId = $this->request->getPost('id');
        $organisation = getOrganisationById($organisationId);

        if (!$organisation) {
            log_message('warn', getCurrentUser()->getUsername() . ' tried to delete invalid organisation ' . $organisationId);
            return redirect('admin/organisations')->with('error', 'Unbekannte Organisation.');
        }

        if (!$organisation->isManageableBy($self)) {
            log_message('warn', getCurrentUser()->getUsername() . ' tried to delete foreign organisation ' . $organisation->getDisplayName());
            return redirect('admin/organisations')->with('error', 'Du darfst diese Organisation nicht löschen.');
        }

        try {
            deleteOrganisation($organisationId);

            log_message('info', getCurrentUser()->getUsername() . ' deleted organisation ' . $organisation->getDisplayName());
            return redirect('admin/organisations')->with('success', 'Organisation gelöscht.');
        } catch (Throwable $e) {
            return $this->handleException('Unable to delete organisation', $e, 'admin/organisations');
        }
    }

    public function regions(): string
    {
        return $this->render('admin/region/RegionsView');
    }

    public function createRegion(): string
    {
        return $this->render('admin/region/RegionCreateView');
    }

    public function handleCreateRegion(): RedirectResponse
    {
        $name = trim($this->request->getPost('name'));

        try {
            createAndInsertRegion($name);

            return redirect('admin/regions')->with('success', 'Region erstellt.');
        } catch (Throwable $e) {
            return $this->handleException('Unable to create region', $e, 'admin/regions');
        }
    }

    public function handleDeleteRegion(): RedirectResponse
    {
        $regionId = $this->request->getPost('id');
        $region = getRegionById($regionId);

        if (!$region) {
            return redirect('admin/regions')->with('error', 'Unbekannte Region.');
        }

        try {
            deleteRegion($regionId);
            return redirect('admin/regions')->with('success', 'Region gelöscht.');
        } catch (Throwable $e) {
            return $this->handleException('Unable to delete region', $e, 'admin/regions');
        }
    }

    public function editRegion(int $regionId): RedirectResponse|string
    {
        $region = getRegionById($regionId);
        if (!$region) {
            return redirect('admin/regions')->with('error', 'Unbekannte Region.');
        }

        return $this->render('admin/region/RegionEditView', ['region' => $region]);
    }

    public function handleEditRegion(): RedirectResponse
    {
        $regionId = $this->request->getPost('id');
        $region = getRegionById($regionId);
        if (!$region) {
            return redirect('admin/regions')->with('error', 'Unbekannte Region.');
        }

        $name = trim($this->request->getPost('name'));
        $region->setName($name);

        try {
            saveRegion($region);
            return redirect('admin/regions')->with('success', 'Region bearbeitet.');
        } catch (Throwable $e) {
            return $this->handleException('Unable to update region', $e, 'admin/regions');
        }
    }
}
