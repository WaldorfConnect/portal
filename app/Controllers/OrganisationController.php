<?php

namespace App\Controllers;

use App\Entities\MembershipStatus;
use CodeIgniter\HTTP\RedirectResponse;
use Exception;
use function App\Helpers\createMembershipRequest;
use function App\Helpers\deleteMembership;
use function App\Helpers\getCurrentUser;
use function App\Helpers\getOrganisationById;
use function App\Helpers\getMembership;
use function App\Helpers\getUserById;
use function App\Helpers\saveMembership;

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

    public function handleJoin(): string|RedirectResponse
    {
        $currentUser = getCurrentUser();
        $organisationId = $this->request->getPost('id');
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

    public function handleAcceptJoin(): string|RedirectResponse
    {
        $currentUser = getCurrentUser();
        $organisationId = $this->request->getPost('organisationId');
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

    public function handleDenyJoin(): string|RedirectResponse
    {
        $currentUser = getCurrentUser();
        $organisationId = $this->request->getPost('organisationId');
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

    public function handleChangeMembershipStatus(): string|RedirectResponse
    {
        $currentUser = getCurrentUser();
        $organisationId = $this->request->getPost('organisationId');
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

    public function handleKickUser(): string|RedirectResponse
    {
        $currentUser = getCurrentUser();
        $organisationId = $this->request->getPost('$organisationId');
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
