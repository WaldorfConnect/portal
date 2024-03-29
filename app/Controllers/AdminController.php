<?php

namespace App\Controllers;

use CodeIgniter\HTTP\RedirectResponse;
use DateTime;
use Exception;
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
            return redirect('admin/users')->with('error', 'Dieser Nutzer wurde bereits akzeptiert.');
        }

        $user->setAcceptDate(new DateTime());
        $user->setActive(true);

        try {
            saveUser($user);
            queueMail($user->getId(), 'Konto freigegeben', view('mail/AccountAccepted', ['user' => $user]));
        } catch (Exception $e) {
            return redirect('admin/users')->with('error', $e);
        }

        return redirect('admin/users')->with('success', $user->getName() . ' erfolgreich freigegeben!');
    }

    public function activateUser(): RedirectResponse
    {
        $userId = $this->request->getPost('id');
        $user = getUserById($userId);

        if ($user->isActive()) {
            return redirect('admin/users')->with('error', 'Dieser Nutzer ist bereits aktiv.');
        }

        $user->setActive(true);
        return redirect('admin/users')->with('success', $user->getName() . ' erfolgreich aktiviert!');
    }

    public function deactivateUser(): RedirectResponse
    {
        $userId = $this->request->getPost('id');
        $user = getUserById($userId);

        if (!$user->isActive()) {
            return redirect('admin/users')->with('error', 'Dieser Nutzer ist bereits deaktiviert.');
        }

        $user->setActive(false);
        return redirect('admin/users')->with('success', $user->getName() . ' erfolgreich deaktiviert!');
    }

    public function users(): string
    {
        return $this->render('admin/user/UsersView');
    }

    public function editUser(int $userId): string|RedirectResponse
    {
        $self = getCurrentUser();
        $user = getUserById($userId);

        if (!$user) {
            return redirect('admin/users')->with('error', 'Unbekannter Benutzer.');
        }

        return $this->render('admin/user/UserEditView', ['user' => $user]);
    }

    public function handleEditUser(): RedirectResponse
    {
        $self = getCurrentUser();
        $userId = $this->request->getPost('id');
        $user = getUserById($userId);

        if (!$user) {
            return redirect('admin/users')->with('error', 'Unbekannter Benutzer.');
        }

        $firstName = $this->request->getPost('firstName');
        $lastName = $this->request->getPost('lastName');
        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');
        $confirmedPassword = $this->request->getPost('confirmedPassword');

        $user->setFirstName($firstName);
        $user->setLastName($lastName);
        $user->setEmail($email);

        // Check if user wants to change password
        if (strlen($password) > 0) {
            // Ensure matching
            if ($password != $confirmedPassword) {
                return redirect()->to('admin/user/edit/' . $userId)->with('error', 'Passwörter stimmen nicht überein.');
            }

            $user->setPassword(hashSSHA($password));
        }

        try {
            saveUser($user);
            return redirect('admin/users')->with('success', 'Benutzer bearbeitet.');
        } catch (Exception $e) {
            return redirect('admin/users')->with('error', $e);
        }
    }

    public function handleDeleteUser(): RedirectResponse
    {
        $self = getCurrentUser();
        $userId = $this->request->getPost('id');
        $user = getUserById($userId);

        if (!$user) {
            return redirect('admin/users')->with('error', 'Unbekannter Benutzer.');
        }

        try {
            deleteUser($userId);
            return redirect('admin/users')->with('success', 'Benutzer gelöscht.');
        } catch (Exception $e) {
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
        $name = $this->request->getPost('name');
        $shortName = $this->request->getPost('shortName');
        $websiteUrl = $this->request->getPost('websiteUrl');
        $regionId = $this->request->getPost('region');
        $region = getRegionById($regionId);

        if (!$region) {
            return redirect('admin/organisations')->with('error', 'Unbekannte Region.');
        }

        $organisation = createOrganisation($name, $shortName, $regionId);
        $organisation->setWebsiteUrl($websiteUrl);

        try {
            // 1. Prevent a logo/image from being uploaded that is not image or bigger than 1/2MB
            if (!$this->validate(createImageValidationRule('logo', 1000, true))) {
                return redirect('admin/organisations')->with('error', $this->validator->getErrors());
            }
            if (!$this->validate(createImageValidationRule('image'))) {
                return redirect('admin/organisations')->with('error', $this->validator->getErrors());
            }

            $logoFile = $this->request->getFile('logo');
            $logoAuthor = $this->request->getPost('logoAuthor');

            $imageFile = $this->request->getFile('image');
            $imageAuthor = $this->request->getPost('imageAuthor');

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

            return redirect('admin/organisations')->with('success', 'Gruppe erstellt.');
        } catch (Exception $e) {
            return redirect('admin/organisations')->with('error', $e);
        }
    }

    public function handleDeleteOrganisation(): RedirectResponse
    {
        $self = getCurrentUser();
        $organisationId = $this->request->getPost('id');
        $organisation = getOrganisationById($organisationId);

        if (!$organisation) {
            return redirect('admin/organisations')->with('error', 'Unbekannte Organisation.');
        }

        if (!$organisation->isManageableBy($self)) {
            return redirect('admin/organisations')->with('error', 'Du darfst diese Organisation nicht löschen.');
        }

        try {
            deleteOrganisation($organisationId);
            return redirect('admin/organisations')->with('success', 'Organisation gelöscht.');
        } catch (Exception $e) {
            return redirect('admin/organisations')->with('error', $e);
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
        $name = $this->request->getPost('name');

        try {
            createAndInsertRegion($name);

            return redirect('admin/regions')->with('success', 'Region erstellt.');
        } catch (Exception $e) {
            return redirect('admin/regions')->with('error', $e);
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
        } catch (Exception $e) {
            return redirect('admin/regions')->with('error', $e);
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

        $name = $this->request->getPost('name');
        $iso = $this->request->getPost('iso');

        $region->setName($name);
        $region->setIsoCode($iso);

        try {
            saveRegion($region);
            return redirect('admin/regions')->with('success', 'Region bearbeitet.');
        } catch (Exception $e) {
            return redirect('admin/regions')->with('error', $e);
        }
    }
}
