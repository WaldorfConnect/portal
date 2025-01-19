<?php

namespace App\Helpers;

use App\Entities\User;
use App\Exceptions\User\UserNotFoundException;
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

function getCurrentUserId(): ?int
{
    $user_id = session('user_id');
    if (!$user_id) {
        return null;
    }

    return intval($user_id);
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
 * Saves given user to database
 *
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

    log_message('info', "User saved: 'userId={$user->getId()},username={$user->getUsername()}'");
}

/**
 * @throws ReflectionException
 */
function createUser(string $firstName, string $lastName, string $email, string $password): User
{
    $username = generateUsername($firstName, $lastName);

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

    sendConfirmationMail($user);
    log_message('info', "User created: 'userId={$user->getId()},username={$user->getUsername()}'");
    return $user;
}

/**
 * @throws ReflectionException
 */
function sendConfirmationMail(User $user): void
{
    queueMail($user->getId(), 'E-Mail bestätigen', view('mail/ConfirmEmail', ['user' => $user]));
}

/**
 * Delete a user by its id
 *
 * @param int $id the users id
 * @return void
 */
function deleteUser(int $id): void
{
    getUserModel()->delete($id);
    log_message('info', "User deleted: 'userId={$id}'");
}

/**
 * Generate a username from a user's first and last name and add consecutive number to avoid duplicates
 *
 * @param string $firstName
 * @param string $lastName
 *
 * @return string
 * @throws InvalidArgumentException
 */
function generateUsername(string $firstName, string $lastName): string
{
    $firstLetterFirstName = substr($firstName, 0, 1);
    $username = mb_strtolower($firstLetterFirstName . $lastName);
    $username = iconv('UTF-8', 'ASCII//TRANSLIT', $username);
    $username = preg_replace("/[^\p{L}]+/", '', $username);

    $id = 2;
    while (!is_null(getUserByUsername($username))) {
        $username = $username . $id++;
    }

    return $username;
}

/**
 * Hash a given plain text
 *
 * @param string $plainText plain text
 * @return string
 */
function hashSSHA(string $plainText): string
{
    $salt = "";
    for ($i = 1; $i <= 10; $i++) {
        $salt .= substr('0123456789abcdef', rand(0, 15), 1);
    }
    return "{SSHA}" . base64_encode(pack("H*", sha1($plainText . $salt)) . $salt);
}

/**
 * Check if a given hash matches a given plain text
 *
 * @param string $plainText plain text
 * @param string $hash hash value
 * @return bool
 */
function checkSSHA(string $plainText, string $hash): bool
{
    $originalHash = base64_decode(substr($hash, 6));
    $salt = substr($originalHash, 20);
    $originalHash = substr($originalHash, 0, 20);
    $newHash = pack("H*", sha1($plainText . $salt));
    return $originalHash == $newHash;
}

/**
 * @throws UserNotFoundException
 */
function validateUser(int $userId): void
{
    $user = getUserById($userId);
    if (!$user) {
        throw new UserNotFoundException();
    }
}

/**
 * @return UserModel
 */
function getUserModel(): UserModel
{
    return new UserModel();
}