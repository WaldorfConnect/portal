<?php

namespace App\Controllers;

use function App\Helpers\getSchoolById;
use function App\Helpers\getUserByUsernameAndPassword;
use function App\Helpers\login;
use function App\Helpers\logout;

class SchoolController extends BaseController
{
    public function list(): string
    {
        return $this->render('school/SchoolsView');
    }

    public function school(int $schoolId): string
    {
        $school = getSchoolById($schoolId);
        return $this->render('school/SchoolView', ['school' => $school]);
    }
}
