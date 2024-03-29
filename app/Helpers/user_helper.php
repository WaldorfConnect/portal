<?php

namespace App\Helpers;

use App\Entities\User;
use App\Models\UserModel;
use CodeIgniter\Database\Exceptions\DatabaseException;
use InvalidArgumentException;
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
    $users = getUserModel()->findAll();
    usort($users, fn($a, $b) => strcmp($a->getName(), $b->getName()));
    return $users;
}

/**
 * @param string $name
 * @return User[]
 */
function getUsersByName(string $name): array
{
    return getUserModel()->like('first_name', $name)->orLike('last_name', $name)->findAll();
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
 * @return void
 * @throws ReflectionException
 */
function saveUser(User $user): void
{
    if (!$user->hasChanged()) {
        return;
    }

    $model = getUserModel();
    $model->save($user);
}

/**
 * @param string $username
 * @param string $email
 * @param string $firstName
 * @param string $lastName
 * @param string $password
 * @return User
 * @throws ReflectionException
 */
function createAndInsertUser(string $username, string $firstName, string $lastName, string $email, string $password): User
{
    $user = new User();
    $user->setUsername($username);
    $user->setFirstName($firstName);
    $user->setLastName($lastName);
    $user->setEmail($email);
    $user->setPassword($password);
    $user->generateAndSetToken();

    $model = getUserModel();
    $model->insert($user);
    $user->setId($model->getInsertID());

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
 * @param $firstName
 * @param $lastName
 *
 * @return string
 * @throws InvalidArgumentException
 */
function generateUsername($firstName, $lastName): string
{
    $firstLetterFirstName = substr($firstName, 0, 1);
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