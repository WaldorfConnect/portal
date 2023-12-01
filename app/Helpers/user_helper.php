<?php

namespace App\Helpers;

use App\Entities\User;
use App\Entities\UserStatus;
use App\Models\UserModel;
use CodeIgniter\Database\Exceptions\DatabaseException;
use InvalidArgumentException;
use Ramsey\Uuid\Uuid;
use ReflectionException;

/**
 * @throws DatabaseException
 */
function isLoggedIn(): bool
{
    return !is_null(session('user_id'));
}

/**
 * @return ?User
 * @throws DatabaseException
 */
function getCurrentUser(): ?User
{
    $user_id = session('user_id');
    if (!$user_id) {
        return null;
    }

    return getUserById($user_id);
}

/**
 * @return User[]
 * @throws DatabaseException
 */
function getUsers(): array
{
    return getUserModel()->findAll();
}

/**
 * @return User[]
 * @throws DatabaseException
 */
function getManageableUsers(): array
{
    $self = getCurrentUser();
    $manageableUsers = [];

    $allUsers = getUsers();
    foreach ($allUsers as $user) {
        if ($self->mayManage($user)) {
            $manageableUsers[] = $user;
        }
    }

    return $manageableUsers;
}

/**
 * @param int $id
 * @return ?User
 * @throws DatabaseException
 */
function getUserById(int $id): ?object
{
    return getUserModel()->find($id);
}

/**
 * @param string $username
 * @return ?User
 * @throws DatabaseException
 */
function getUserByUsername(string $username): ?object
{
    return getUserModel()->where('username', $username)->first();
}

/**
 * @param string $email
 * @return ?User
 * @throws DatabaseException
 */
function getUserByEmail(string $email): ?object
{
    return getUserModel()->where('email', $email)->first();
}

/**
 * @param string $username
 * @param string $email
 * @return ?User
 * @throws DatabaseException
 */
function getUserByUsernameAndEmail(string $username, string $email): ?object
{
    return getUserModel()->where('username', $username)->where('email', $email)->first();
}

/**
 * @param string $token
 * @return ?User
 * @throws DatabaseException
 */
function getUserByToken(string $token): ?object
{
    return getUserModel()->where('token', $token)->first();
}

/**
 * @param User $user
 * @return string|int
 * @throws DatabaseException|ReflectionException
 */
function saveUser(User $user): string|int
{
    $model = getUserModel();
    $model->save($user);
    return $model->getInsertID();
}

/**
 * @param string $username
 * @param string $email
 * @param string $name
 * @param string $password
 * @return User
 */
function createUser(string $username, string $name, string $email, string $password): User
{
    $user = new User();
    $user->setToken(Uuid::uuid4()->toString());
    $user->setUsername($username);
    $user->setName($name);
    $user->setEmail($email);
    $user->setPassword($password);
    $user->setStatus(UserStatus::PENDING_REGISTER);
    return $user;
}

function deleteUser(int $id): void
{
    getUserModel()->delete($id);
}

/**
 * @return UserModel
 */
function getUserModel(): UserModel
{
    return new UserModel();
}

/**
 * @param $name
 * @return string
 * @throws InvalidArgumentException
 */
function generateUsername($name): string
{
    $firstLetterFirstName = substr($name, 0, 1);
    $splitName = explode(' ', $name);
    if (count($splitName) < 2) {
        throw new InvalidArgumentException();
    }

    $lastName = end($splitName);
    $username = mb_strtolower($firstLetterFirstName . $lastName);
    $username = iconv('UTF-8', 'ASCII//TRANSLIT', $username);
    return preg_replace("/[^\p{L}]+/", '', $username);
}

function hashSSHA($text): string
{
    $salt = "";
    for ($i = 1; $i <= 10; $i++) {
        $salt .= substr('0123456789abcdef', rand(0, 15), 1);
    }
    return "{SSHA}" . base64_encode(pack("H*", sha1($text . $salt)) . $salt);
}

function checkSSHA($text, $hash): bool
{
    $originalHash = base64_decode(substr($hash, 6));
    $salt = substr($originalHash, 20);
    $originalHash = substr($originalHash, 0, 20);
    $newHash = pack("H*", sha1($text . $salt));
    return $originalHash == $newHash;
}