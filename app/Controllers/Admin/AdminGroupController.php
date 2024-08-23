<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\RedirectResponse;
use Throwable;
use function App\Helpers\createImageValidationRule;
use function App\Helpers\createGroup;
use function App\Helpers\deleteGroup;
use function App\Helpers\deleteUser;
use function App\Helpers\getCurrentUser;
use function App\Helpers\getGroupById;
use function App\Helpers\getRegionById;
use function App\Helpers\getUserById;
use function App\Helpers\hashSSHA;
use function App\Helpers\insertGroup;
use function App\Helpers\queueMail;
use function App\Helpers\saveImage;
use function App\Helpers\saveUser;

class AdminGroupController extends BaseController
{
    public function groups(): string
    {
        return $this->render('admin/group/GroupsView');
    }

    public function createGroup(): string
    {
        return $this->render('admin/group/GroupCreateView');
    }

    public function handleCreateGroup(): RedirectResponse
    {
        $name = trim($this->request->getPost('name'));
        $shortName = trim($this->request->getPost('shortName'));
        $websiteUrl = trim($this->request->getPost('websiteUrl'));
        $regionId = $this->request->getPost('region');
        $region = getRegionById($regionId);

        if (str_contains($name, '/') || str_contains($shortName, '/')) {
            log_message('warning', getCurrentUser()->getUsername() . ' used invalid characters in group name');
            return redirect()->back()->withInput()->with('error', 'Ungültige Zeichen im Gruppennamen.');
        }

        if (!$region) {
            log_message('warning', getCurrentUser()->getUsername() . ' tried to created group in invalid region ' . $regionId);
            return redirect()->back()->withInput()->with('error', 'Unbekannte Region.');
        }

        $group = createGroup($name, $shortName, $regionId);
        $group->setWebsite($websiteUrl);

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
                $group->setLogoId($logoId);
            }
            if ($imageFile->isValid()) {
                $imageId = saveImage($imageFile, $imageAuthor);
                $group->setImageId($imageId);
            }

            insertGroup($group);
            log_message('info', getCurrentUser()->getUsername() . ' created group ' . $group->getDisplayName());

            return redirect('admin/groups')->with('success', 'Gruppe erstellt.');
        } catch (Throwable $e) {
            log_message('error', 'Unable to create group ' . $group->getDisplayName() . ': {exception}', ['exception' => $e]);
            return redirect()->back()->withInput()->with('error', $e);
        }
    }

    public function handleDeleteGroup(): RedirectResponse
    {
        $self = getCurrentUser();
        $groupId = $this->request->getPost('id');
        $group = getGroupById($groupId);

        if (!$group) {
            log_message('warning', getCurrentUser()->getUsername() . ' tried to delete invalid group ' . $groupId);
            return redirect('admin/groups')->with('error', 'Unbekannte Gruppe.');
        }

        if (!$group->isManageableBy($self)) {
            log_message('warning', getCurrentUser()->getUsername() . ' tried to delete foreign group ' . $group->getDisplayName());
            return redirect('admin/groups')->with('error', 'Du darfst diese Gruppe nicht löschen.');
        }

        try {
            deleteGroup($groupId);

            log_message('info', getCurrentUser()->getUsername() . ' deleted group ' . $group->getDisplayName());
            return redirect('admin/groups')->with('success', 'Gruppe gelöscht.');
        } catch (Throwable $e) {
            log_message('error', 'Unable to delete group ' . $group->getDisplayName() . ': {exception}', ['exception' => $e]);
            return redirect('admin/groups')->with('error', $e);
        }
    }
}