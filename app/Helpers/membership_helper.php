<?php

namespace App\Helpers;

use App\Entities\Membership;
use App\Entities\MembershipStatus;
use App\Exceptions\Group\GroupAlreadyMemberException;
use App\Exceptions\Group\GroupNotAdminException;
use App\Exceptions\Group\GroupNotFoundException;
use App\Exceptions\Group\GroupNotMemberException;
use App\Exceptions\User\UserNotFoundException;
use App\Models\MembershipModel;
use ReflectionException;

function getMembership(int $userId, int $groupId): ?object
{
    return getMembershipModel()->where('user_id', $userId)->where('group_id', $groupId)->first();
}

function getMembershipsByUserId(int $userId): array
{
    return getMembershipModel()->where('user_id', $userId)->findAll();
}

function getMembershipsByGroupId(int $groupId): array
{
    return getMembershipModel()->where('group_id', $groupId)->findAll();
}

function getMembers(int $groupId): array
{
    return getMembershipModel()->where('group_id', $groupId)->whereNotIn('status', [MembershipStatus::PENDING->value])->findAll();
}

function countMembers(int $groupId): int
{
    return getMembershipModel()->where('group_id', $groupId)->whereNotIn('status', [MembershipStatus::PENDING->value])->countAllResults();
}

/**
 * @throws UserNotFoundException
 * @throws GroupAlreadyMemberException
 * @throws GroupNotFoundException
 * @throws GroupNotAdminException
 * @throws GroupNotMemberException
 * @throws ReflectionException
 */
function createMembershipRequest(int $targetUserId, int $groupId, ?int $actorUserId = null): void
{
    validateUserAndGroup($targetUserId, $groupId);
    if ($actorUserId) {
        validateGroupAdmin($actorUserId, $groupId);
    }

    $membership = getMembership($targetUserId, $groupId);
    if ($membership) {
        throw new GroupAlreadyMemberException();
    }

    $membership = new Membership();
    $membership->setUserId($targetUserId);
    $membership->setGroupId($groupId);
    $membership->setStatus(MembershipStatus::PENDING);
    saveMembership($membership);

    // TODO notifications

    log_message('info', "Created membership request: 'targetUserId={$targetUserId},groupId='{$groupId},actorUserId={$actorUserId}'");
}

/**
 * @throws GroupNotFoundException
 * @throws UserNotFoundException
 * @throws GroupNotAdminException
 * @throws GroupNotMemberException
 * @throws GroupAlreadyMemberException
 * @throws ReflectionException
 */
function createMemberships(array $userIds, int $groupId, ?int $actorUserId = null): void
{
    validateGroup($groupId);
    if ($actorUserId) {
        validateGroupAdmin($actorUserId, $groupId);
    }

    foreach ($userIds as $userId) {
        $membership = getMembership($userId, $groupId);
        if ($membership) {
            throw new GroupAlreadyMemberException($userId);
        }

        $membership = new Membership();
        $membership->setUserId($userId);
        $membership->setGroupId($groupId);
        $membership->setStatus(MembershipStatus::USER);
        saveMembership($membership);
    }

    // TODO notifications

    log_message('info', "Created memberships: 'userIds=" . print_r($userIds, true) . ",groupId={$groupId},actorUserId={$actorUserId}");
}

/**
 * @throws UserNotFoundException
 * @throws GroupAlreadyMemberException
 * @throws GroupNotFoundException
 * @throws GroupNotAdminException
 * @throws GroupNotMemberException
 * @throws ReflectionException
 */
function createMembership(int $userId, int $groupId, MembershipStatus $status = MembershipStatus::USER, ?int $actorUserId = null): void
{
    validateUserAndGroup($userId, $groupId);
    if ($actorUserId) {
        validateGroupAdmin($actorUserId, $groupId);
    }

    $membership = getMembership($userId, $groupId);
    if ($membership) {
        throw new GroupAlreadyMemberException();
    }

    $membership = new Membership();
    $membership->setUserId($userId);
    $membership->setGroupId($groupId);
    $membership->setStatus($status);
    saveMembership($membership);

    // TODO notifications

    log_message('info', "Created membership: 'userId={$userId},groupId='{$groupId},status={$status->value},actorUserId={$actorUserId}'");
}

/**
 * @throws UserNotFoundException
 * @throws GroupNotMemberException
 * @throws GroupNotFoundException
 * @throws GroupNotAdminException
 */
function deleteMembership(int $userId, int $groupId, ?int $actorUserId = null): void
{
    validateUserAndGroup($userId, $groupId);
    if ($actorUserId) {
        validateGroupAdmin($actorUserId, $groupId);
    }

    $membership = getMembership($userId, $groupId);
    if (!$membership) {
        throw new GroupNotMemberException();
    }

    getMembershipModel()->where('user_id', $userId)->where('group_id', $groupId)->delete();

    // TODO notifications

    log_message('info', "Deleted membership: 'userId={$userId},groupId='{$groupId}'");
}

/**
 * @throws ReflectionException
 */
function saveMembership(Membership $membership): void
{
    getMembershipModel()->save($membership);

    log_message('info', "Saved membership: 'userId={$membership->getUserId()},groupId={$membership->getGroupId()}'");
}

/**
 * @throws UserNotFoundException
 * @throws GroupNotFoundException
 */
function validateUserAndGroup(int $userId, int $groupId): void
{
    validateUser($userId);
    validateGroup($groupId);
}

/**
 * @throws UserNotFoundException
 * @throws GroupNotFoundException
 * @throws GroupNotMemberException
 * @throws GroupNotAdminException
 */
function validateGroupAdmin(int $userId, int $groupId): void
{
    validateUserAndGroup($userId, $groupId);

    $membership = getMembership($userId, $groupId);
    if (!$membership) {
        throw new GroupNotMemberException();
    }

    if ($membership->getStatus() !== MembershipStatus::ADMIN) {
        throw new GroupNotAdminException();
    }
}

function getMembershipModel(): MembershipModel
{
    return new MembershipModel();
}