<?php

namespace App\Controllers;

use CodeIgniter\HTTP\RedirectResponse;
use function App\Helpers\getGroupById;

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

    public function handleJoin(): string|RedirectResponse
    {

    }

    public function handleAcceptJoin(): string|RedirectResponse
    {

    }

    public function handleDenyJoin(): string|RedirectResponse
    {

    }

    public function handleChangeUserStatus(): string|RedirectResponse
    {

    }

    public function handleKickUser(): string|RedirectResponse
    {

    }
}
