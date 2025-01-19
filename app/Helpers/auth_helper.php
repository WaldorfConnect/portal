<?php

namespace App\Helpers;

use App\Entities\User;
use App\Exceptions\Auth\AuthCredentialsInvalidException;
use App\Exceptions\Auth\AuthPasswordTemporaryException;
use App\Exceptions\Auth\AuthTFAInvalidException;
use App\Exceptions\Auth\AuthTFARequiredException;
use App\Exceptions\User\UserInactiveException;
use DateTime;
use InvalidArgumentException;
use lfkeitel\phptotp\Base32;
use lfkeitel\phptotp\Totp;
use ReflectionException;

/**
 * @throws AuthCredentialsInvalidException
 * @throws AuthTFARequiredException
 * @throws AuthTFAInvalidException
 * @throws AuthPasswordTemporaryException
 * @throws UserInactiveException
 * @throws ReflectionException
 */
function login(string $username, string $password, string $twoFactorCode = null,
               string $newPassword = null, string $newPasswordConfirmation = null): void
{
    $user = getUserByUsername($username);

    // Check user existence
    if (!$user) {
        log_message('info', "Failed login (invalid user): 'username={$username}'");
        throw new AuthCredentialsInvalidException();
    }

    // Check password correctness
    if (!checkSSHA($password, $user->getPassword())) {
        log_message('info', "Failed login (invalid password): 'username={$username}'");
        throw new AuthCredentialsInvalidException();
    }

    // Check TFA requirement
    if ($user->getTOTPSecret()) {
        if (!$twoFactorCode) {
            throw new AuthTFARequiredException();
        }

        $currentCode = generateCurrentTOTP($user);
        if ($currentCode !== $twoFactorCode) {
            log_message('info', "Failed login (invalid TFA code): 'username={$username}'");
            throw new AuthTFAInvalidException();
        }
    }

    // Check user active
    if (!$user->isActive()) {
        log_message('info', "Failed login (user inactive): 'username={$username}'");
        throw new UserInactiveException();
    }

    // Check pending password reset
    if ($user->isPasswordReset()) {
        $user->setPasswordReset(false);
        log_message('info', "Revoked pending password reset (successful login): 'username={$username}'");
    }

    // Check if password is temporary and must be changed
    if ($user->isPasswordTemporary()) {
        if ($newPassword && $newPasswordConfirmation) {

        }
        throw new AuthPasswordTemporaryException();
    }

    // Update last login date
    $user->setLastLoginDate(new DateTime());
    saveUser($user);

    // Set session data
    session()->set(SESSION_USER_ID, $user->getId());

    log_message('info', "Login: 'username={$username}'");
}

function logout(bool $external): void
{
    $userId = session(SESSION_USER_ID);
    session()->remove(SESSION_USER_ID);

    log_message('info', "Logout: 'userId={$userId},external={$external}'");
}

function generateCurrentTOTP(User $user): string
{
    $secret = $user->getTOTPSecret();
    if (!$secret) {
        throw new InvalidArgumentException('User has TFA not configured');
    }

    return (new Totp())->GenerateToken(Base32::decode($secret));
}