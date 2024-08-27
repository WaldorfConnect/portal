<?php

namespace App\Controllers;

use App\Entities\MembershipStatus;
use App\Exceptions\Group\GroupAlreadyMemberException;
use App\Exceptions\Group\GroupNotAdminException;
use App\Exceptions\Group\GroupNotFoundException;
use App\Exceptions\Group\GroupNotMemberException;
use App\Exceptions\User\UserNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;
use Exception;
use ReflectionException;
use Throwable;
use function App\Helpers\createImageValidationRule;
use function App\Helpers\createMembership;
use function App\Helpers\createMembershipRequest;
use function App\Helpers\createMemberships;
use function App\Helpers\createNotification;
use function App\Helpers\createGroup;
use function App\Helpers\createGroupNotification;
use function App\Helpers\deleteMembership;
use function App\Helpers\deleteGroup;
use function App\Helpers\getCurrentUser;
use function App\Helpers\getCurrentUserId;
use function App\Helpers\getGroupById;
use function App\Helpers\getMembership;
use function App\Helpers\getUserById;
use function App\Helpers\insertGroup;
use function App\Helpers\saveImage;
use function App\Helpers\saveMembership;
use function App\Helpers\saveGroup;

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

    public function handleJoin(int $groupId): RedirectResponse
    {
        try {
            createMembershipRequest(getCurrentUserId(), $groupId);
        } catch (GroupNotFoundException) {
            return redirect('groups')->with('error', 'Diese Gruppe existiert nicht.');
        } catch (GroupAlreadyMemberException) {
            return redirect(base_url('group/' . $groupId))->with('error', 'Du bist bereits Mitglied dieser Gruppe.');
        } catch (Throwable $e) {
            return redirect(base_url('group/' . $groupId))->with('error', $e);
        }

        return redirect()->to(base_url('group/' . $groupId));
    }

    public function handleLeave(int $groupId): RedirectResponse
    {
        try {
            deleteMembership(getCurrentUserId(), $groupId);
        } catch (GroupNotFoundException) {
            return redirect('groups')->with('error', 'Diese Gruppe existiert nicht.');
        } catch (GroupNotMemberException) {
            return redirect(base_url('group/' . $groupId))->with('error', 'Du bist kein Mitglied dieser Gruppe.');
        } catch (Throwable $e) {
            return redirect(base_url('group/' . $groupId))->with('error', $e);
        }

        return redirect()->to(base_url('group/' . $groupId));
    }

    public function handleAddMember(int $groupId): RedirectResponse|string
    {
        $members = $this->request->getPost('member');

        try {
            createMemberships($members, $groupId, getCurrentUserId());
            return redirect()->to(base_url('group/' . $groupId));
        } catch (GroupNotFoundException) {
            return redirect('groups')->with('error', 'Diese Gruppe existiert nicht.');
        } catch (GroupAlreadyMemberException $e) {
            $name = getUserById($e->getUserId())->getName();
            return redirect()->to(base_url('group/' . $groupId))->with('error', "{$name} ist bereits Mitglied der Gruppe.");
        } catch (GroupNotAdminException) {
            return redirect()->to(base_url('group/' . $groupId))->with('error', 'Du bist kein Gruppenadministrator.');
        } catch (GroupNotMemberException) {
            return redirect()->to(base_url('group/' . $groupId))->with('error', 'Du bist kein Mitglied dieser Gruppe.');
        } catch (UserNotFoundException) {
            return redirect()->to(base_url('group/' . $groupId))->with('error', 'Ungültiger Benutzer ausgewählt.');
        } catch (Throwable $e) {
            return redirect(base_url('group/' . $groupId))->with('error', $e);
        }
    }

    public function handleAddSubgroup(int $groupId): RedirectResponse|string
    {
        $self = getCurrentUser();
        $group = getGroupById($groupId);
        $name = trim($this->request->getPost('name'));

        if (!$group) {
            return redirect('groups')->with('error', 'Diese Gruppe existiert nicht.');
        }

        if (!$group->isManageableBy($self)) {
            return redirect()->to(base_url('group/' . $groupId))->with('error', 'Du bist kein Gruppenadministrator.');
        }

        try {
            $workgroup = createGroup($name, $name, $group->getRegionId(), $group->getId());
            $id = insertGroup($workgroup);

            createMembership($self->getId(), $id, MembershipStatus::ADMIN);
            createGroupNotification($groupId, 'Untergruppe erstellt', "Arbeitsgruppe {$workgroup->getName()} in %s erstellt.");

            return redirect()->to(base_url('group/' . $groupId))->with('success', 'Untergruppe erstellt.');
        } catch (Throwable $e) {
            return redirect()->to(base_url('group/' . $groupId))->with('error', $e);
        }
    }

    public function edit(int $groupId): RedirectResponse|string
    {
        $self = getCurrentUser();
        $group = getGroupById($groupId);

        if (!$group) {
            return redirect()->to(base_url('group/' . $groupId))->with('error', 'Unbekannte Gruppe.');
        }

        if (!$group->isManageableBy($self)) {
            return redirect()->to(base_url('group/' . $groupId))->with('error', 'Du darfst diese Gruppe nicht bearbeiten.');
        }

        return $this->render('group/GroupEditView', ['group' => $group]);
    }

    public function handleEdit(int $groupId): RedirectResponse|string
    {
        $self = getCurrentUser();
        $group = getGroupById($groupId);

        if (!$group) {
            return redirect()->to(base_url('group/' . $groupId))->with('error', 'Unbekannte Gruppe.');
        }

        if (!$group->isManageableBy($self)) {
            return redirect()->to(base_url('group/' . $groupId))->with('error', 'Du darfst diese Gruppe nicht bearbeiten.');
        }

        $name = trim($this->request->getPost('name'));
        $address = trim($this->request->getPost('address'));
        $website = trim($this->request->getPost('website'));
        $email = trim($this->request->getPost('email'));
        $phone = trim($this->request->getPost('phone'));
        $latitude = trim($this->request->getPost('latitude'));
        $longitude = trim($this->request->getPost('longitude'));

        $regionId = $this->request->getPost('region');
        $description = $this->request->getPost('description');

        if ($self->isAdmin()) {
            if ($name && strlen($name) > 0) {
                $group->setName($name);
            }

            if ($regionId) {
                $group->setRegionId($regionId);
            }
        }

        $group->setAddress($address ?: null);

        if ($website) {
            if (!filter_var($website, FILTER_VALIDATE_URL)) {
                return redirect()->back()->with('error', 'Website ist keine gültige URL.');
            }

            $group->setWebsite($website);
        } else {
            $group->setWebsite(null);
        }

        if ($email) {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return redirect()->back()->with('error', 'E-Mail< ist keine gültige E-Mail-Adresse.');
            }

            $group->setEmail($email);
        } else {
            $group->setEmail(null);
        }

        if ($phone) {
            if (!preg_match('^\+((?:9[679]|8[035789]|6[789]|5[90]|42|3[578]|2[1-689])|9[0-58]|8[1246]|6[0-6]|5[1-8]|4[013-9]|3[0-469]|2[70]|7|1)(?:\W*\d){0,13}\d$^', $phone)) {
                return redirect()->back()->with('error', 'Telefon ist keine gültige Telefonnummer im internationalen Format. (z. B. +49...)');
            }

            $group->setPhone($phone);
        } else {
            $group->setPhone(null);
        }

        if ($latitude) {
            if ($latitude < -90 || $latitude > 90) {
                return redirect()->back()->with('error', 'Der Breitengrad muss zwischen -90 und 90 liegen.');
            }

            $group->setLatitude($latitude);
        } else {
            $group->setLatitude(null);
        }

        if ($longitude) {
            if ($longitude < -180 || $longitude > 180) {
                return redirect()->back()->with('error', 'Der Längengrad muss zwischen -180 und 180 liegen.');
            }

            $group->setLongitude($longitude);
        } else {
            $group->setLongitude(null);
        }

        $group->setDescription($description ?: null);

        // 1. Prevent a logo/image from being uploaded that is not image or bigger than 1/2MB
        if (!$this->validate(createImageValidationRule('logo', 1000, true))) {
            return redirect()->to(base_url('group/' . $groupId))->with('error', $this->validator->getErrors());
        }
        if (!$this->validate(createImageValidationRule('image'))) {
            return redirect()->to(base_url('group/' . $groupId))->with('error', $this->validator->getErrors());
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

        try {
            saveGroup($group);

            return redirect()->to(base_url('group/' . $groupId))->with('success', 'Gruppe bearbeitet.');
        } catch (Throwable $e) {
            return redirect()->to(base_url('group/' . $groupId))->with('error', $e);
        }
    }

    public function handleAcceptJoin(int $groupId): RedirectResponse
    {
        $currentUser = getCurrentUser();
        $userId = $this->request->getPost('userId');

        $group = getGroupById($groupId);
        if (!$group) {
            return redirect('groups')->with('error', 'Diese Gruppe existiert nicht.');
        }

        if (!$group->isManageableBy($currentUser)) {
            return redirect('groups')->with('error', 'Du bist kein Gruppenadministrator.');
        }

        $user = getUserById($userId);
        if (!$user) {
            return redirect('groups')->with('error', 'Dieser Benutzer existiert nicht.');
        }

        $membership = getMembership($user->getId(), $groupId);
        if (!$membership || $membership->getStatus() != MembershipStatus::PENDING) {
            return redirect('groups')->with('error', 'Keine Beitrittsanfrage für diesen Nutzer gefunden.');
        }

        $membership->setStatus(MembershipStatus::USER);

        try {
            saveMembership($membership);
        } catch (Throwable $e) {
            return redirect('groups')->with('error', $e);
        }

        createGroupNotification($groupId,
            'Gruppe beigetreten',
            "{$membership->getUser()->getName()} ist %s beigetreten.</a>",
            null,
            [$membership->getUserId()]);

        createNotification($membership->getUserId(),
            'Beitrittsanfrage akzeptiert',
            "Deine Anfrage an {$group->getUrl()} wurden akzeptiert.");

        return redirect()->to(base_url('group/' . $groupId));
    }

    public function handleDenyJoin(int $groupId): RedirectResponse
    {
        $currentUser = getCurrentUser();
        $userId = $this->request->getPost('userId');

        $group = getGroupById($groupId);
        if (!$group) {
            return redirect('groups')->with('error', 'Diese Gruppe existiert nicht.');
        }

        if (!$group->isManageableBy($currentUser)) {
            return redirect('groups')->with('error', 'Du bist kein Gruppenadministrator.');
        }

        $user = getUserById($userId);
        if (!$user) {
            return redirect('groups')->with('error', 'Dieser Benutzer existiert nicht.');
        }

        $membership = getMembership($user->getId(), $groupId);
        if (!$membership || $membership->getStatus() != MembershipStatus::PENDING) {
            return redirect('groups')->with('error', 'Keine Beitrittsanfrage für diesen Nutzer gefunden.');
        }

        try {
            deleteMembership($userId, $groupId);

            createGroupNotification($groupId,
                'Beitrittsanfrage abgelehnt',
                "Anfrage von {$membership->getUser()->getName()} an %s abgelehnt.</a>",
                MembershipStatus::ADMIN,
                [$membership->getUserId()]);

            createNotification($membership->getUserId(), 'Beitrittsanfrage abgelehnt', "Deine Anfrage an {$group->getUrl()} wurde abgelehnt.");
        } catch (Throwable $e) {
            return redirect('groups')->with('error', $e);
        }

        return redirect()->to(base_url('group/' . $groupId));
    }

    public function handleChangeMembershipStatus(int $groupId): RedirectResponse
    {
        $currentUser = getCurrentUser();
        $userId = $this->request->getPost('userId');
        $status = $this->request->getPost('status');

        $group = getGroupById($groupId);
        if (!$group) {
            return redirect('groups')->with('error', 'Diese Gruppe existiert nicht.');
        }

        if (!$group->isManageableBy($currentUser)) {
            return redirect('groups')->with('error', 'Du bist kein Gruppenadministrator.');
        }

        $user = getUserById($userId);
        if (!$user) {
            return redirect('groups')->with('error', 'Dieser Benutzer existiert nicht.');
        }

        $membership = getMembership($user->getId(), $groupId);
        if (!$membership) {
            return redirect('groups')->with('error', 'Dieser Nutzer ist nicht Mitglied dieser Gruppe.');
        }

        $statusEnum = MembershipStatus::from($status);
        $membership->setStatus($statusEnum);

        try {
            createGroupNotification($groupId,
                'Rolle geändert',
                "Rolle von {$membership->getUser()->getName()} in %s zu {$statusEnum->displayName()} geändert.</a>",
                MembershipStatus::ADMIN,
                [$membership->getUserId()]);

            createNotification($membership->getUserId(), 'Rolle geändert', "Deine Rolle in {$group->getUrl()} wurde zu {$statusEnum->displayName()} geändert.");

            saveMembership($membership);
        } catch (Throwable $e) {
            return redirect('groups')->with('error', $e);
        }

        return redirect()->to(base_url('group/' . $groupId));
    }

    public function handleKickUser(int $groupId): RedirectResponse
    {
        $currentUser = getCurrentUser();
        $userId = $this->request->getPost('userId');

        $group = getGroupById($groupId);
        if (!$group) {
            return redirect('groups')->with('error', 'Diese Gruppe existiert nicht.');
        }

        if (!$group->isManageableBy($currentUser)) {
            return redirect('groups')->with('error', 'Du bist kein Gruppenadministrator.');
        }

        $user = getUserById($userId);
        if (!$user) {
            return redirect('groups')->with('error', 'Dieser Benutzer existiert nicht.');
        }

        $membership = getMembership($user->getId(), $groupId);
        if (!$membership) {
            return redirect('groups')->with('error', 'Dieser Nutzer ist nicht Mitglied dieser Gruppe.');
        }

        try {
            deleteMembership($userId, $groupId);

            createGroupNotification($groupId,
                'Aus Gruppe entfernt',
                "{$membership->getUser()->getName()} von {$currentUser->getName()} aus %s entfernt.</a>",
                MembershipStatus::ADMIN,
                [$membership->getUserId()]);

            createGroupNotification($groupId,
                'Gruppe verlassen',
                "{$currentUser->getName()} hat %s verlassen.</a>",
                MembershipStatus::USER,
                [$membership->getUserId()]);

            createNotification($membership->getUserId(), 'Aus Gruppe entfernt', "Du wurdest aus {$group->getUrl()} entfernt.");
        } catch (Throwable $e) {
            return redirect('groups')->with('error', $e);
        }

        return redirect()->to(base_url('group/' . $groupId));
    }

    public function handleDelete(int $groupId): RedirectResponse
    {
        $currentUser = getCurrentUser();
        $group = getGroupById($groupId);

        if (!$group) {
            return redirect('groups')->with('error', 'Diese Gruppe existiert nicht.');
        }

        $parent = $group->getParent();
        if (!$parent) {
            return redirect()->to(base_url('group/' . $groupId))->with('error', 'Stammgruppen dürfen nur von globalen Administratoren gelöscht werden.');
        }

        if (!$parent->isManageableBy($currentUser)) {
            return redirect()->to(base_url('group/' . $parent->getId()))->with('error', 'Du bist kein Gruppenadministrator.');
        }

        try {
            createGroupNotification($groupId, 'Untergruppe gelöscht', "Untergruppe {$group->getName()} in %s gelöscht.");
            deleteGroup($groupId);
            return redirect()->to(base_url('group/' . $parent->getId()))->with('success', 'Untergruppe gelöscht.');
        } catch (Throwable $e) {
            return redirect()->to(base_url('group/' . $parent->getId()))->with('error', $e);
        }
    }
}
