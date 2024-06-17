<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\RedirectResponse;
use Throwable;
use function App\Helpers\createImageValidationRule;
use function App\Helpers\createOrganisation;
use function App\Helpers\deleteOrganisation;
use function App\Helpers\deleteUser;
use function App\Helpers\getCurrentUser;
use function App\Helpers\getOrganisationById;
use function App\Helpers\getRegionById;
use function App\Helpers\getUserById;
use function App\Helpers\hashSSHA;
use function App\Helpers\insertOrganisation;
use function App\Helpers\queueMail;
use function App\Helpers\saveImage;
use function App\Helpers\saveUser;

class AdminOrganisationController extends BaseController
{
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
            log_message('error', 'Unable to delete organisation ' . $organisation->getDisplayName() . ': {exception}', ['exception' => $e]);
            return redirect('admin/organisations')->with('error', $e);
        }
    }
}