<?php

namespace App\Controllers;

use function App\Helpers\getGroupById;
use function App\Helpers\getUserByUsernameAndPassword;
use function App\Helpers\login;
use function App\Helpers\logout;

class GroupController extends BaseController
{
    public function list(): string
    {
        return $this->render('group/GroupsView');
    }

    public function group(int $groupId): string
    {
        $group = getGroupById($groupId);
        return $this->render('group/GroupView', ['group' => $group]);
    }
}
