<?php

namespace App\Controllers;

use App\Entities\MembershipStatus;
use CodeIgniter\HTTP\RedirectResponse;
use Exception;
use function App\Helpers\createImageValidationRule;
use function App\Helpers\createMembership;
use function App\Helpers\createMembershipRequest;
use function App\Helpers\createNotification;
use function App\Helpers\createOrganisation;
use function App\Helpers\createOrganisationNotification;
use function App\Helpers\deleteMembership;
use function App\Helpers\deleteOrganisation;
use function App\Helpers\getCurrentUser;
use function App\Helpers\getOrganisationById;
use function App\Helpers\getMembership;
use function App\Helpers\getUserById;
use function App\Helpers\insertOrganisation;
use function App\Helpers\saveImage;
use function App\Helpers\saveMembership;
use function App\Helpers\saveOrganisation;

class OrganisationController extends BaseController
{
    public function list(): string
    {
        return $this->render('organisation/OrganisationsView');
    }

    public function organisation(int $organisationId): RedirectResponse|string
    {
        $organisation = getOrganisationById($organisationId);
        if (!$organisation) {
            return redirect('organisations')->with('error', 'Diese Organisation existiert nicht.');
        }

        return $this->render('organisation/OrganisationView', ['organisation' => $organisation]);
    }

    public function handleJoin(int $organisationId): RedirectResponse
    {
        $currentUser = getCurrentUser();
        $organisation = getOrganisationById($organisationId);

        if (!$organisation) {
            return redirect('organisations')->with('error', 'Diese Organisation existiert nicht.');
        }

        $membership = getMembership($currentUser->getId(), $organisationId);
        if ($membership) {
            return redirect('organisations')->with('error', 'Du bist bereits Mitglied dieser Organisation.');
        }

        try {
            createMembershipRequest($currentUser->getId(), $organisationId);

            createOrganisationNotification($organisationId,
                'Beitrittsanfrage',
                "{$currentUser->getName()} möchte %s beitreten.</a>",
                MembershipStatus::ADMIN);
        } catch (Exception $e) {
            return redirect('organisations')->with('error', $e);
        }

        return redirect()->to(base_url('organisation/' . $organisationId));
    }

    public function handleLeave(int $organisationId): RedirectResponse
    {
        $currentUser = getCurrentUser();
        $organisation = getOrganisationById($organisationId);

        if (!$organisation) {
            return redirect('organisations')->with('error', 'Diese Organisation existiert nicht.');
        }

        $membership = getMembership($currentUser->getId(), $organisationId);
        if (!$membership) {
            return redirect('organisations')->with('error', 'Du bist kein Mitglied dieser Organisation.');
        }

        deleteMembership($currentUser->getId(), $organisationId);
        createOrganisationNotification($organisationId,
            'Organisation verlassen',
            "{$currentUser->getName()} hat %s verlassen.</a>");

        return redirect()->to(base_url('organisation/' . $organisationId));
    }

    public function handleAddMember(int $organisationId): RedirectResponse|string
    {
        $self = getCurrentUser();
        $organisation = getOrganisationById($organisationId);

        if (!$organisation) {
            return redirect('organisations')->with('error', 'Diese Organisation existiert nicht.');
        }

        if (!$organisation->isManageableBy($self)) {
            return redirect()->to(base_url('organisation/' . $organisationId))->with('error', 'Du darfst diese Organisation nicht verwalten.');
        }

        $members = $this->request->getPost('member');
        foreach ($members as $member) {
            try {
                createMembership($member, $organisationId);
            } catch (Exception $e) {
                return redirect()->to(base_url('organisation/' . $organisationId))->with('error', $e);
            }

            $memberUser = getUserById($member);
            createOrganisationNotification($organisationId,
                'Organisation beigetreten',
                "{$memberUser->getName()} ist %s beigetreten.</a>",
                null,
                [$member]);

            createNotification($member, 'Zur Organisation hinzugefügt', "Du wurdest zu {$organisation->getUrl()} hinzugefügt.");
        }

        return redirect()->to(base_url('organisation/' . $organisationId));
    }

    public function handleAddWorkgroup(int $organisationId): RedirectResponse|string
    {
        $self = getCurrentUser();
        $organisation = getOrganisationById($organisationId);

        $name = $this->request->getPost('name');

        if (!$organisation) {
            return redirect('organisations')->with('error', 'Diese Organisation existiert nicht.');
        }

        if (!$organisation->isManageableBy($self)) {
            return redirect()->to(base_url('organisation/' . $organisationId))->with('error', 'Du darfst diese Organisation nicht verwalten.');
        }

        try {
            $workgroup = createOrganisation($name, $name, $organisation->getRegionId(), $organisation->getId());
            $id = insertOrganisation($workgroup);

            createMembership($self->getId(), $id, MembershipStatus::ADMIN);
            createOrganisationNotification($organisationId, 'Arbeitsgruppe erstellt', "Arbeitsgruppe {$workgroup->getName()} in %s erstellt.");

            return redirect()->to(base_url('organisation/' . $organisationId))->with('success', 'Arbeitsgruppe erstellt.');
        } catch (Exception $e) {
            return redirect()->to(base_url('organisation/' . $organisationId))->with('error', $e);
        }
    }

    public function edit(int $organisationId): RedirectResponse|string
    {
        $self = getCurrentUser();
        $organisation = getOrganisationById($organisationId);

        if (!$organisation) {
            return redirect()->to(base_url('organisation/' . $organisationId))->with('error', 'Unbekannte Organisation.');
        }

        if (!$organisation->isManageableBy($self)) {
            return redirect()->to(base_url('organisation/' . $organisationId))->with('error', 'Du darfst diese Organisation nicht bearbeiten.');
        }

        return $this->render('organisation/OrganisationEditView', ['organisation' => $organisation]);
    }

    public function handleEdit(int $organisationId): RedirectResponse|string
    {
        $self = getCurrentUser();
        $organisation = getOrganisationById($organisationId);

        if (!$organisation) {
            return redirect()->to(base_url('organisation/' . $organisationId))->with('error', 'Unbekannte Organisation.');
        }

        if (!$organisation->isManageableBy($self)) {
            return redirect()->to(base_url('organisation/' . $organisationId))->with('error', 'Du darfst diese Organisation nicht bearbeiten.');
        }

        $name = $this->request->getPost('name');
        $address = $this->request->getPost('address');
        $website = $this->request->getPost('website');
        $email = $this->request->getPost('email');
        $phone = $this->request->getPost('phone');
        $regionId = $this->request->getPost('region');
        $description = $this->request->getPost('description');

        if ($self->isAdmin()) {
            $organisation->setName($name);

            if ($regionId) {
                $organisation->setRegionId($regionId);
            }
        }

        if ($address) {
            $organisation->setAddress($address);
        }

        if ($website) {
            $organisation->setWebsite($website);
        }

        if ($email) {
            $organisation->setEmail($email);
        }

        if ($phone) {
            $organisation->setPhone($phone);
        }

        if ($description) {
            $organisation->setDescription($description);
        }

        // 1. Prevent a logo/image from being uploaded that is not image or bigger than 1/2MB
        if (!$this->validate(createImageValidationRule('logo', 1000, true))) {
            return redirect()->to(base_url('organisation/' . $organisationId))->with('error', $this->validator->getErrors());
        }
        if (!$this->validate(createImageValidationRule('image'))) {
            return redirect()->to(base_url('organisation/' . $organisationId))->with('error', $this->validator->getErrors());
        }

        $logoFile = $this->request->getFile('logo');
        $logoAuthor = $this->request->getPost('logoAuthor');

        $imageFile = $this->request->getFile('image');
        $imageAuthor = $this->request->getPost('imageAuthor');

        // 2. If a logo/image was uploaded save it | Logos may be SVGs, all other formats are converted to WEBP
        if ($logoFile->isValid()) {
            $logoId = saveImage($logoFile, $logoAuthor);
            $organisation->setLogoId($logoId);
        }
        if ($imageFile->isValid()) {
            $imageId = saveImage($imageFile, $imageAuthor);
            $organisation->setImageId($imageId);
        }

        try {
            saveOrganisation($organisation);

            return redirect()->to(base_url('organisation/' . $organisationId))->with('success', 'Organisation bearbeitet.');
        } catch (Exception $e) {
            return redirect()->to(base_url('organisation/' . $organisationId))->with('error', $e);
        }
    }

    public function handleAcceptJoin(int $organisationId): RedirectResponse
    {
        $currentUser = getCurrentUser();
        $userId = $this->request->getPost('userId');

        $organisation = getOrganisationById($organisationId);
        if (!$organisation) {
            return redirect('organisations')->with('error', 'Diese Organisation existiert nicht.');
        }

        if (!$organisation->isManageableBy($currentUser)) {
            return redirect('organisations')->with('error', 'Du darfst diese Organisation nicht verwalten.');
        }

        $user = getUserById($userId);
        if (!$user) {
            return redirect('organisations')->with('error', 'Dieser Benutzer existiert nicht.');
        }

        $membership = getMembership($user->getId(), $organisationId);
        if (!$membership || $membership->getStatus() != MembershipStatus::PENDING) {
            return redirect('organisations')->with('error', 'Keine Beitrittsanfrage für diesen Nutzer gefunden.');
        }

        $membership->setStatus(MembershipStatus::USER);

        try {
            saveMembership($membership);
        } catch (Exception $e) {
            return redirect('organisations')->with('error', $e);
        }

        createOrganisationNotification($organisationId,
            'Organisation beigetreten',
            "{$membership->getUser()->getName()} ist %s beigetreten.</a>",
            null,
            [$membership->getUserId()]);

        createNotification($membership->getUserId(),
            'Beitrittsanfrage akzeptiert',
            "Deine Anfrage an {$organisation->getUrl()} wurden akzeptiert.");

        return redirect()->to(base_url('organisation/' . $organisationId));
    }

    public function handleDenyJoin(int $organisationId): RedirectResponse
    {
        $currentUser = getCurrentUser();
        $userId = $this->request->getPost('userId');

        $organisation = getOrganisationById($organisationId);
        if (!$organisation) {
            return redirect('organisations')->with('error', 'Diese Organisation existiert nicht.');
        }

        if (!$organisation->isManageableBy($currentUser)) {
            return redirect('organisations')->with('error', 'Du darfst diese Organisation nicht verwalten.');
        }

        $user = getUserById($userId);
        if (!$user) {
            return redirect('organisations')->with('error', 'Dieser Benutzer existiert nicht.');
        }

        $membership = getMembership($user->getId(), $organisationId);
        if (!$membership || $membership->getStatus() != MembershipStatus::PENDING) {
            return redirect('organisations')->with('error', 'Keine Beitrittsanfrage für diesen Nutzer gefunden.');
        }

        try {
            deleteMembership($userId, $organisationId);

            createOrganisationNotification($organisationId,
                'Beitrittsanfrage abgelehnt',
                "Anfrage von {$membership->getUser()->getName()} an %s abgelehnt.</a>",
                MembershipStatus::ADMIN,
                [$membership->getUserId()]);

            createNotification($membership->getUserId(), 'Beitrittsanfrage abgelehnt', "Deine Anfrage an {$organisation->getUrl()} wurde abgelehnt.");
        } catch (Exception $e) {
            return redirect('organisations')->with('error', $e);
        }

        return redirect()->to(base_url('organisation/' . $organisationId));
    }

    public function handleChangeMembershipStatus(int $organisationId): RedirectResponse
    {
        $currentUser = getCurrentUser();
        $userId = $this->request->getPost('userId');
        $status = $this->request->getPost('status');

        $organisation = getOrganisationById($organisationId);
        if (!$organisation) {
            return redirect('organisations')->with('error', 'Diese Organisation existiert nicht.');
        }

        if (!$organisation->isManageableBy($currentUser)) {
            return redirect('organisations')->with('error', 'Du darfst diese Organisation nicht verwalten.');
        }

        $user = getUserById($userId);
        if (!$user) {
            return redirect('organisations')->with('error', 'Dieser Benutzer existiert nicht.');
        }

        $membership = getMembership($user->getId(), $organisationId);
        if (!$membership) {
            return redirect('organisations')->with('error', 'Dieser Nutzer ist nicht Mitglied dieser Organisation.');
        }

        $statusEnum = MembershipStatus::from($status);
        $membership->setStatus($statusEnum);

        try {
            createOrganisationNotification($organisationId,
                'Organisationsrolle geändert',
                "Rolle von {$membership->getUser()->getName()} in %s zu {$statusEnum->displayName()} geändert.</a>",
                MembershipStatus::ADMIN,
                [$membership->getUserId()]);

            createNotification($membership->getUserId(), 'Organisationsrolle geändert', "Deine Rolle in {$organisation->getUrl()} wurde zu {$statusEnum->displayName()} geändert.");

            saveMembership($membership);
        } catch (Exception $e) {
            return redirect('organisations')->with('error', $e);
        }

        return redirect()->to(base_url('organisation/' . $organisationId));
    }

    public function handleKickUser(int $organisationId): RedirectResponse
    {
        $currentUser = getCurrentUser();
        $userId = $this->request->getPost('userId');

        $organisation = getOrganisationById($organisationId);
        if (!$organisation) {
            return redirect('organisations')->with('error', 'Diese Organisation existiert nicht.');
        }

        if (!$organisation->isManageableBy($currentUser)) {
            return redirect('organisations')->with('error', 'Du darfst diese Organisation nicht verwalten.');
        }

        $user = getUserById($userId);
        if (!$user) {
            return redirect('organisations')->with('error', 'Dieser Benutzer existiert nicht.');
        }

        $membership = getMembership($user->getId(), $organisationId);
        if (!$membership) {
            return redirect('organisations')->with('error', 'Dieser Nutzer ist nicht Mitglied dieser Organisation.');
        }

        try {
            deleteMembership($userId, $organisationId);

            createOrganisationNotification($organisationId,
                'Aus Organisation entfernt',
                "{$membership->getUser()->getName()} von {$currentUser->getName()} aus %s entfernt.</a>",
                MembershipStatus::ADMIN,
                [$membership->getUserId()]);

            createOrganisationNotification($organisationId,
                'Organisation verlassen',
                "{$currentUser->getName()} hat %s verlassen.</a>",
                MembershipStatus::USER,
                [$membership->getUserId()]);

            createNotification($membership->getUserId(), 'Aus Organisation entfernt', "Du wurdest aus {$organisation->getUrl()} entfernt.");
        } catch (Exception $e) {
            return redirect('organisations')->with('error', $e);
        }

        return redirect()->to(base_url('organisation/' . $organisationId));
    }

    public function handleDelete(int $organisationId): RedirectResponse
    {
        $currentUser = getCurrentUser();
        $organisation = getOrganisationById($organisationId);

        if (!$organisation) {
            return redirect('organisations')->with('error', 'Diese Organisation existiert nicht.');
        }

        $parent = $organisation->getParent();
        if (!$parent) {
            return redirect()->to(base_url('organisation/' . $organisationId))->with('error', 'Stammgruppen dürfen nur von globalen Administratoren gelöscht werden.');
        }

        if (!$parent->isManageableBy($currentUser)) {
            return redirect()->to(base_url('organisation/' . $parent->getId()))->with('error', 'Du darfst diese Organisation nicht verwalten.');
        }

        try {
            createOrganisationNotification($organisationId, 'Arbeitsgruppe erstellt', "Arbeitsgruppe {$organisation->getName()} in %s gelöscht.");
            deleteOrganisation($organisationId);
            return redirect()->to(base_url('organisation/' . $parent->getId()))->with('success', 'Arbeitsgruppe gelöscht.');
        } catch (Exception $e) {
            return redirect()->to(base_url('organisation/' . $parent->getId()))->with('error', $e);
        }
    }
}
