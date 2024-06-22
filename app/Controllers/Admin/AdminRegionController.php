<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\RedirectResponse;
use Throwable;
use function App\Helpers\createAndInsertRegion;
use function App\Helpers\createImageValidationRule;
use function App\Helpers\createGroup;
use function App\Helpers\deleteGroup;
use function App\Helpers\deleteRegion;
use function App\Helpers\deleteUser;
use function App\Helpers\getCurrentUser;
use function App\Helpers\getGroupById;
use function App\Helpers\getRegionById;
use function App\Helpers\getUserById;
use function App\Helpers\hashSSHA;
use function App\Helpers\insertGroup;
use function App\Helpers\queueMail;
use function App\Helpers\saveImage;
use function App\Helpers\saveRegion;
use function App\Helpers\saveUser;

class AdminRegionController extends BaseController
{
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
            log_message('error', 'Unable to create organisation ' . $name . ': {exception}', ['exception' => $e]);
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
        } catch (Throwable $e) {
            log_message('error', 'Unable to delete organisation ' . $region->getName() . ': {exception}', ['exception' => $e]);
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

        $name = trim($this->request->getPost('name'));
        $region->setName($name);

        try {
            saveRegion($region);

            return redirect('admin/regions')->with('success', 'Region bearbeitet.');
        } catch (Throwable $e) {
            log_message('error', 'Unable to edit organisation ' . $region->getName() . ': {exception}', ['exception' => $e]);
            return redirect('admin/regions')->with('error', $e);
        }
    }
}