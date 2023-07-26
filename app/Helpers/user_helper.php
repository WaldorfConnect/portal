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
    return getUserModel()->findAll();
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
 * @param User $user
 * @return void
 * @throws DatabaseException|ReflectionException
 */
function saveUser(User $user): void
{
    getUserModel()->save($user);
}

/**
 * @param string $username
 * @param string $email
 * @param string $name
 * @param string $password
 * @param int $schoolId
 * @return User
 */
function createUser(string $username, string $name, string $email, string $password, int $schoolId): User
{
    $user = new User();
    $user->setUsername($username);
    $user->setName($name);
    $user->setEmail($email);
    $user->setPassword($password);
    $user->setSchoolId($schoolId);
    $user->setStatus('PENDING');
    return $user;
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