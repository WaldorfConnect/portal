<?php

namespace App\Controllers;

use CodeIgniter\HTTP\RedirectResponse;
use function App\Helpers\getSchoolById;

class SchoolController extends BaseController
{
    public function list(): string
    {
        return $this->render('school/SchoolsView');
    }

    public function school(int $schoolId): RedirectResponse|string
    {
        $school = getSchoolById($schoolId);
        if (!$school) {
            return redirect('schools')->with('error', 'Diese Schule existiert nicht.');
        }

        return $this->render('school/SchoolView', ['school' => $school]);
    }
}
