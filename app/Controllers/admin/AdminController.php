<?php

namespace App\Controllers\admin;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\RedirectResponse;
use DateTime;
use Throwable;
use function App\Helpers\createAndInsertRegion;
use function App\Helpers\createImageValidationRule;
use function App\Helpers\createOrganisation;
use function App\Helpers\deleteOrganisation;
use function App\Helpers\deleteRegion;
use function App\Helpers\deleteUser;
use function App\Helpers\getCurrentUser;
use function App\Helpers\getOrganisationById;
use function App\Helpers\getRegionById;
use function App\Helpers\getUserById;
use function App\Helpers\hashSSHA;
use function App\Helpers\insertOrganisation;
use function App\Helpers\queueMail;
use function App\Helpers\saveImage;
use function App\Helpers\saveRegion;
use function App\Helpers\saveUser;

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








}
