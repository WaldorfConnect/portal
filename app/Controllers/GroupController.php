<?php

namespace App\Controllers;

use App\Entities\MembershipStatus;
use CodeIgniter\HTTP\RedirectResponse;
use Exception;
use function App\Helpers\createGroupMembershipRequest;
use function App\Helpers\deleteGroupMembership;
use function App\Helpers\getCurrentUser;
use function App\Helpers\getGroupById;
use function App\Helpers\getGroupMembership;
use function App\Helpers\getUserById;
use function App\Helpers\saveMembership;

class GroupController extends BaseController
{
    public function list(): string
    {
        return $this->render('group/GroupsView');
    }

    public function group(int $groupId): RedirectResponse|string
    {
        $group = getGroupById($groupId);
        if (!$group) {
            return redirect('groups')->with('error', 'Diese Gruppe existiert nicht.');
        }

        return $this->render('group/GroupView', ['group' => $group]);
    }

    public function handleJoin(): string|RedirectResponse
    {
        $currentUser = getCurrentUser();
        $groupId = $this->request->getPost('id');
        $group = getGroupById($groupId);
        if (!$group) {
            return redirect('groups')->with('error', 'Diese Gruppe existiert nicht.');
        }

        $membership = getGroupMembership($currentUser->getId(), $groupId);
        if ($membership) {
            return redirect('groups')->with('error', 'Du bist bereits Mitglied dieser Gruppe.');
        }

        try {
            createGroupMembershipRequest($currentUser->getId(), $groupId);
            // TODO send email / notification
        } catch (Exception $e) {
            return redirect('groups')->with('error', 'Fehler beim Speichern: ' . $e->getMessage());
        }

        return redirect()->to(base_url('group/' . $groupId));
    }

    public function handleAcceptJoin(): string|RedirectResponse
    {
        $currentUser = getCurrentUser();
        $groupId = $this->request->getPost('groupId');
        $userId = $this->request->getPost('userId');

        $group = getGroupById($groupId);
        if (!$group) {
            return redirect('groups')->with('error', 'Diese Gruppe existiert nicht.');
        }

        if (!$group->mayManage($currentUser)) {
            return redirect('groups')->with('error', 'Du darfst diese Gruppe nicht verwalten.');
        }

        $user = getUserById($userId);
        if (!$user) {
            return redirect('groups')->with('error', 'Dieser Benutzer existiert nicht.');
        }

        $membership = getGroupMembership($user->getId(), $groupId);
        if (!$membership || $membership->getStatus() != MembershipStatus::PENDING) {
            return redirect('groups')->with('error', 'Keine Beitrittsanfrage für diesen Nutzer gefunden.');
        }

        $membership->setStatus(MembershipStatus::USER);

        try {
            saveMembership($membership);
            // TODO send email / notification
        } catch (Exception $e) {
            return redirect('groups')->with('error', 'Fehler beim Speichern: ' . $e->getMessage());
        }

        return redirect()->to(base_url('group/' . $groupId));
    }

    public function handleDenyJoin(): string|RedirectResponse
    {
        $currentUser = getCurrentUser();
        $groupId = $this->request->getPost('groupId');
        $userId = $this->request->getPost('userId');

        $group = getGroupById($groupId);
        if (!$group) {
            return redirect('groups')->with('error', 'Diese Gruppe existiert nicht.');
        }

        if (!$group->mayManage($currentUser)) {
            return redirect('groups')->with('error', 'Du darfst diese Gruppe nicht verwalten.');
        }

        $user = getUserById($userId);
        if (!$user) {
            return redirect('groups')->with('error', 'Dieser Benutzer existiert nicht.');
        }

        $membership = getGroupMembership($user->getId(), $groupId);
        if (!$membership || $membership->getStatus() != MembershipStatus::PENDING) {
            return redirect('groups')->with('error', 'Keine Beitrittsanfrage für diesen Nutzer gefunden.');
        }

        try {
            deleteGroupMembership($userId, $groupId);
            // TODO send email / notification
        } catch (Exception $e) {
            return redirect('groups')->with('error', 'Fehler beim Speichern: ' . $e->getMessage());
        }

        return redirect()->to(base_url('group/' . $groupId));
    }

    public function handleChangeUserStatus(): string|RedirectResponse
    {
        $currentUser = getCurrentUser();
        $groupId = $this->request->getPost('groupId');
        $userId = $this->request->getPost('userId');
        $status = $this->request->getPost('status');

        $group = getGroupById($groupId);
        if (!$group) {
            return redirect('groups')->with('error', 'Diese Gruppe existiert nicht.');
        }

        if (!$group->mayManage($currentUser)) {
            return redirect('groups')->with('error', 'Du darfst diese Gruppe nicht verwalten.');
        }

        $user = getUserById($userId);
        if (!$user) {
            return redirect('groups')->with('error', 'Dieser Benutzer existiert nicht.');
        }

        $membership = getGroupMembership($user->getId(), $groupId);
        if (!$membership) {
            return redirect('groups')->with('error', 'Dieser Nutzer ist nicht Mitglied dieser Gruppe.');
        }

        $membership->setStatus(MembershipStatus::from($status));

        try {
            saveMembership($membership);
        } catch (Exception $e) {
            return redirect('groups')->with('error', 'Fehler beim Speichern: ' . $e->getMessage());
        }

        return redirect()->to(base_url('group/' . $groupId));
    }

    public function handleKickUser(): string|RedirectResponse
    {
        $currentUser = getCurrentUser();
        $groupId = $this->request->getPost('groupId');
        $userId = $this->request->getPost('userId');

        $group = getGroupById($groupId);
        if (!$group) {
            return redirect('groups')->with('error', 'Diese Gruppe existiert nicht.');
        }

        if (!$group->mayManage($currentUser)) {
            return redirect('groups')->with('error', 'Du darfst diese Gruppe nicht verwalten.');
        }

        $user = getUserById($userId);
        if (!$user) {
            return redirect('groups')->with('error', 'Dieser Benutzer existiert nicht.');
        }

        $membership = getGroupMembership($user->getId(), $groupId);
        if (!$membership) {
            return redirect('groups')->with('error', 'Dieser Nutzer ist nicht Mitglied dieser Gruppe.');
        }

        try {
            deleteGroupMembership($userId, $groupId);
        } catch (Exception $e) {
            return redirect('groups')->with('error', 'Fehler beim Speichern: ' . $e->getMessage());
        }

        return redirect()->to(base_url('group/' . $groupId));
    }
}
