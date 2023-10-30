<?php

namespace App\Controllers;

use App\Entities\UserRole;
use App\Entities\UserStatus;
use CodeIgniter\HTTP\RedirectResponse;
use Exception;
use function App\Helpers\createGroup;
use function App\Helpers\createRegion;
use function App\Helpers\createSchool;
use function App\Helpers\deleteGroup;
use function App\Helpers\deleteRegion;
use function App\Helpers\deleteSchool;
use function App\Helpers\deleteUser;
use function App\Helpers\getCurrentUser;
use function App\Helpers\getGroupById;
use function App\Helpers\getRegionById;
use function App\Helpers\getSchoolById;
use function App\Helpers\getUserById;
use function App\Helpers\getUserByUsernameAndPassword;
use function App\Helpers\hashSSHA;
use function App\Helpers\login;
use function App\Helpers\logout;
use function App\Helpers\saveGroup;
use function App\Helpers\saveRegion;
use function App\Helpers\saveSchool;
use function App\Helpers\saveUser;
use function App\Helpers\sendMail;

class AdminController extends BaseController
{
    public function index(): string
    {
        return $this->render('admin/IndexView');
    }

    public function accept(): string
    {
        return $this->render('admin/AcceptView');
    }

    public function acceptUser(): RedirectResponse
    {
        $userId = $this->request->getPost('id');
        $user = getUserById($userId);

        // User awaiting acceptance
        if ($user->getStatus() != UserStatus::PENDING_ACCEPT) {
            return redirect('admin/users')->with('error', 'Dieser Nutzer wurde bereits akzeptiert.');
        }

        $user->setStatus(UserStatus::OK);
        try {
            saveUser($user);
            sendMail($user->getEmail(), 'Konto freigegeben', view('mail/AccountAccepted', ['user' => $user]));
        } catch (Exception $e) {
            return redirect('admin/users')->with('error', 'Fehler beim Speichern: ' . $e->getMessage());
        }

        return redirect('admin/users')->with('success', $user->getName() . ' erfolgreich freigegeben!');
    }

    public function denyUser(): RedirectResponse
    {
        $userId = $this->request->getPost('id');
        $user = getUserById($userId);

        // User awaiting acceptance
        if ($user->getStatus() != UserStatus::PENDING_ACCEPT) {
            return redirect('admin/users')->with('error', 'Dieser Nutzer wurde bereits abgelehnt.');
        }

        $user->setStatus(UserStatus::DENIED);
        try {
            saveUser($user);
            sendMail($user->getEmail(), 'Kontoerstellung abgelehnt', view('mail/AccountDenied', ['user' => $user]));
        } catch (Exception $e) {
            return redirect('admin/users')->with('error', 'Fehler beim Speichern: ' . $e->getMessage());
        }

        return redirect('admin/users')->with('success', $user->getName() . ' erfolgreich abgelehnt!');
    }

    public function users(): string
    {
        return $this->render('admin/user/UsersView');
    }

    public function editUser(int $userId): string|RedirectResponse
    {
        $self = getCurrentUser();
        $user = getUserById($userId);

        if (!$user) {
            return redirect('admin/users')->with('error', 'Unbekannter Benutzer.');
        }

        if (!$self->mayManage($user)) {
            return redirect('admin/users')->with('error', 'Du darfst diesen Benutzer nicht bearbeiten.');
        }

        return $this->render('admin/user/UserEditView', ['user' => $user]);
    }

    public function handleEditUser(): RedirectResponse
    {
        $self = getCurrentUser();
        $userId = $this->request->getPost('id');
        $user = getUserById($userId);

        if (!$user) {
            return redirect('admin/users')->with('error', 'Unbekannter Benutzer.');
        }

        if (!$self->mayManage($user)) {
            return redirect('admin/users')->with('error', 'Du darfst diesen Benutzer nicht bearbeiten.');
        }

        $name = $this->request->getPost('name');
        $email = $this->request->getPost('email');
        $schoolId = $this->request->getPost('school');
        $role = $this->request->getPost('role');
        $password = $this->request->getPost('password');
        $confirmedPassword = $this->request->getPost('confirmedPassword');

        $user->setName($name);
        $user->setEmail($email);

        $school = getSchoolById($schoolId);
        if (!$school) {
            return redirect()->to('admin/user/edit/' . $userId)->with('error', 'Unbekannte Schule.');
        }

        if ($school->mayManage($self)) {
            $user->setSchoolId($schoolId);
        }

        if ($self->getRole() == UserRole::GLOBAL_ADMIN) {
            $user->setRole(UserRole::from($role));
        }

        // Check if user wants to change password
        if (strlen($password) > 0) {
            // Ensure matching
            if ($password != $confirmedPassword) {
                return redirect()->to('admin/user/edit/' . $userId)->with('error', 'Passwörter stimmen nicht überein.');
            }

            $user->setPassword(hashSSHA($password));
        }

        try {
            saveUser($user);
            return redirect('admin/users')->with('success', 'Benutzer bearbeitet.');
        } catch (Exception $e) {
            return redirect('admin/users')->with('error', 'Fehler beim Speichern: ' . $e->getMessage());
        }
    }

    public function handleDeleteUser(): RedirectResponse
    {
        $self = getCurrentUser();
        $userId = $this->request->getPost('id');
        $user = getUserById($userId);

        if (!$user) {
            return redirect('admin/users')->with('error', 'Unbekannter Benutzer.');
        }

        if (!$self->mayManage($user)) {
            return redirect('admin/users')->with('error', 'Du darfst diesen Benutzer nicht löschen.');
        }

        try {
            deleteUser($userId);
            return redirect('admin/users')->with('success', 'Benutzer gelöscht.');
        } catch (Exception $e) {
            return redirect('admin/users')->with('error', 'Fehler beim Löschen: ' . $e->getMessage());
        }
    }

    public function groups(): string
    {
        return $this->render('admin/group/GroupsView');
    }

    public function createGroup(): string
    {
        return $this->render('admin/group/GroupCreateView');
    }

    public function handleCreateGroup(): RedirectResponse
    {
        $self = getCurrentUser();
        $name = $this->request->getPost('name');
        $regionId = $this->request->getPost('region');
        $region = getRegionById($regionId);

        if (!$region) {
            return redirect('admin/groups')->with('error', 'Unbekannte Region.');
        }

        if (!$region->mayManage($self)) {
            return redirect('admin/groups')->with('error', 'Du darfst in dieser Region keine Gruppen verwalten.');
        }

        $group = createGroup($name, $regionId);

        try {
            saveGroup($group);
            return redirect('admin/groups')->with('success', 'Gruppe erstellt.');
        } catch (Exception $e) {
            return redirect('admin/groups')->with('error', 'Fehler beim Speichern: ' . $e->getMessage());
        }
    }

    public function handleDeleteGroup(): RedirectResponse
    {
        $self = getCurrentUser();
        $groupId = $this->request->getPost('id');
        $group = getGroupById($groupId);

        if (!$group) {
            return redirect('admin/groups')->with('error', 'Unbekannte Gruppe.');
        }

        if (!$group->mayManage($self)) {
            return redirect('admin/groups')->with('error', 'Du darfst diese Gruppe nicht löschen.');
        }

        try {
            deleteGroup($groupId);
            return redirect('admin/groups')->with('success', 'Gruppe gelöscht.');
        } catch (Exception $e) {
            return redirect('admin/groups')->with('error', 'Fehler beim Löschen: ' . $e->getMessage());
        }
    }

    public function editGroup(int $groupId): RedirectResponse|string
    {
        $self = getCurrentUser();
        $group = getGroupById($groupId);
        if (!$group) {
            return redirect('admin/groups')->with('error', 'Unbekannte Gruppe.');
        }

        if (!$group->mayManage($self)) {
            return redirect('admin/groups')->with('error', 'Du darfst diese Gruppe nicht bearbeiten.');
        }

        return $this->render('admin/group/GroupEditView', ['group' => $group]);
    }

    public function handleEditGroup(): RedirectResponse
    {
        $self = getCurrentUser();
        $groupId = $this->request->getPost('id');
        $group = getGroupById($groupId);
        if (!$group) {
            return redirect('admin/groups')->with('error', 'Unbekannte Gruppe.');
        }

        if (!$group->mayManage($self)) {
            return redirect('admin/groups')->with('error', 'Du darfst diese Gruppe nicht bearbeiten.');
        }

        $name = $this->request->getPost('name');
        $regionId = $this->request->getPost('region');

        $group->setName($name);
        $group->setRegionId($regionId);

        try {
            saveGroup($group);
            return redirect('admin/groups')->with('success', 'Gruppe bearbeitet.');
        } catch (Exception $e) {
            return redirect('admin/groups')->with('error', 'Fehler beim Speichern: ' . $e->getMessage());
        }
    }

    public function schools(): string
    {
        return $this->render('admin/school/SchoolsView');
    }

    public function createSchool(): string
    {
        return $this->render('admin/school/SchoolCreateView');
    }

    public function handleCreateSchool(): RedirectResponse
    {
        $self = getCurrentUser();
        $name = $this->request->getPost('name');
        $shortName = $this->request->getPost('shortName');
        $address = $this->request->getPost('address');
        $emailBureau = $this->request->getPost('emailBureau');
        $regionId = $this->request->getPost('region');
        $region = getRegionById($regionId);

        if (!$region) {
            return redirect('admin/schools')->with('error', 'Unbekannte Region.');
        }

        if (!$region->mayManage($self)) {
            return redirect('admin/schools')->with('error', 'Du darfst in dieser Region keine Schulen verwalten.');
        }

        $group = createSchool($name, $shortName, $address, $emailBureau, $regionId);

        try {
            saveSchool($group);
            return redirect('admin/schools')->with('success', 'Schule erstellt.');
        } catch (Exception $e) {
            return redirect('admin/schools')->with('error', 'Fehler beim Speichern: ' . $e->getMessage());
        }
    }

    public function handleDeleteSchool(): RedirectResponse
    {
        $self = getCurrentUser();
        $schoolId = $this->request->getPost('id');
        $school = getSchoolById($schoolId);

        if (!$school) {
            return redirect('admin/schools')->with('error', 'Unbekannte Schule.');
        }

        if (!$school->mayManage($self)) {
            return redirect('admin/schools')->with('error', 'Du darfst diese Schule nicht löschen.');
        }

        try {
            deleteSchool($schoolId);
            return redirect('admin/schools')->with('success', 'Schule gelöscht.');
        } catch (Exception $e) {
            return redirect('admin/schools')->with('error', 'Fehler beim Löschen: ' . $e->getMessage());
        }
    }

    public function editSchool(int $schoolId): RedirectResponse|string
    {
        $self = getCurrentUser();
        $school = getSchoolById($schoolId);
        if (!$school) {
            return redirect('admin/schools')->with('error', 'Unbekannte Schule.');
        }

        if (!$school->mayManage($self)) {
            return redirect('admin/schools')->with('error', 'Du darfst diese Schule nicht bearbeiten.');
        }

        return $this->render('admin/school/SchoolEditView', ['school' => $school]);
    }

    public function handleEditSchool(): RedirectResponse
    {
        $self = getCurrentUser();
        $schoolId = $this->request->getPost('id');
        $school = getSchoolById($schoolId);

        if (!$school) {
            return redirect('admin/schools')->with('error', 'Unbekannte Schule.');
        }

        if (!$school->mayManage($self)) {
            return redirect('admin/schools')->with('error', 'Du darfst diese Schule nicht löschen.');
        }

        $name = $this->request->getPost('name');
        $shortName = $this->request->getPost('shortName');
        $address = $this->request->getPost('address');
        $emailBureau = $this->request->getPost('emailBureau');
        $emailSMV = $this->request->getPost('emailSMV');
        $regionId = $this->request->getPost('region');

        $school->setName($name);
        $school->setShortName($shortName);
        $school->setAddress($address);
        $school->setEmailBureau($emailBureau);
        $school->setEmailSMV($emailSMV);
        $school->setRegionId($regionId);

        try {
            saveSchool($school);
            return redirect('admin/schools')->with('success', 'Schule bearbeitet.');
        } catch (Exception $e) {
            return redirect('admin/schools')->with('error', 'Fehler beim Speichern: ' . $e->getMessage());
        }
    }

    public function regions(): string
    {
        return $this->render('admin/region/RegionsView');
    }

    public function createRegion(): string
    {
        return $this->render('admin/region/RegionCreateView');
    }

    public function handleCreateRegion(): RedirectResponse
    {
        $name = $this->request->getPost('name');
        $isoCode = $this->request->getPost('iso');
        $region = createRegion($name, $isoCode);

        try {
            saveRegion($region);
            return redirect('admin/regions')->with('success', 'Region erstellt.');
        } catch (Exception $e) {
            return redirect('admin/regions')->with('error', 'Fehler beim Speichern: ' . $e->getMessage());
        }
    }

    public function handleDeleteRegion(): RedirectResponse
    {
        $regionId = $this->request->getPost('id');
        $region = getRegionById($regionId);

        if (!$region) {
            return redirect('admin/regions')->with('error', 'Unbekannte Region.');
        }

        try {
            deleteRegion($regionId);
            return redirect('admin/regions')->with('success', 'Region gelöscht.');
        } catch (Exception $e) {
            return redirect('admin/regions')->with('error', 'Fehler beim Löschen: ' . $e->getMessage());
        }
    }

    public function editRegion(int $regionId): RedirectResponse|string
    {
        $region = getRegionById($regionId);
        if (!$region) {
            return redirect('admin/regions')->with('error', 'Unbekannte Region.');
        }

        return $this->render('admin/region/RegionEditView', ['region' => $region]);
    }

    public function handleEditRegion(): RedirectResponse
    {
        $regionId = $this->request->getPost('id');
        $region = getRegionById($regionId);

        if (!$region) {
            return redirect('admin/regions')->with('error', 'Unbekannte Region.');
        }

        $name = $this->request->getPost('name');
        $iso = $this->request->getPost('iso');

        $region->setName($name);
        $region->setIsoCode($iso);

        try {
            saveRegion($region);
            return redirect('admin/regions')->with('success', 'Region bearbeitet.');
        } catch (Exception $e) {
            return redirect('admin/regions')->with('error', 'Fehler beim Speichern: ' . $e->getMessage());
        }
    }
}
