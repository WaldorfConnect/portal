<?php

namespace App\Controllers;

use App\Entities\MembershipStatus;
use CodeIgniter\HTTP\RedirectResponse;
use Exception;
use function App\Helpers\createImageValidationRule;
use function App\Helpers\createMembership;
use function App\Helpers\createMembershipRequest;
use function App\Helpers\deleteMembership;
use function App\Helpers\getCurrentUser;
use function App\Helpers\getOrganisationById;
use function App\Helpers\getMembership;
use function App\Helpers\getUserById;
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
            // TODO send email / notification
        } catch (Exception $e) {
            return redirect('organisations')->with('error', 'Fehler beim Speichern: ' . $e->getMessage());
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
            createMembership($member, $organisationId);
        }

        return redirect()->to(base_url('organisation/' . $organisationId));
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
        $websiteUrl = $this->request->getPost('websiteUrl');
        $regionId = $this->request->getPost('region');
        $description = $this->request->getPost('description');

        if ($self->isAdmin()) {
            $organisation->setName($name);
            $organisation->setWebsiteUrl($websiteUrl);
            $organisation->setRegionId($regionId);
        }

        $organisation->setDescription($description);

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
            return redirect()->to(base_url('organisation/' . $organisationId))->with('error', 'Fehler beim Speichern: ' . $e->getMessage());
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
            // TODO send email / notification
        } catch (Exception $e) {
            return redirect('organisations')->with('error', 'Fehler beim Speichern: ' . $e->getMessage());
        }

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
            // TODO send email / notification
        } catch (Exception $e) {
            return redirect('organisations')->with('error', 'Fehler beim Speichern: ' . $e->getMessage());
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

        $membership->setStatus(MembershipStatus::from($status));

        try {
            saveMembership($membership);
        } catch (Exception $e) {
            return redirect('organisations')->with('error', 'Fehler beim Speichern: ' . $e->getMessage());
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
        } catch (Exception $e) {
            return redirect('organisations')->with('error', 'Fehler beim Speichern: ' . $e->getMessage());
        }

        return redirect()->to(base_url('organisation/' . $organisationId));
    }
}
