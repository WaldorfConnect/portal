<?php

namespace App\Helpers;

use App\Entities\Organisation;
use App\Entities\Membership;
use App\Entities\MembershipStatus;
use App\Models\MembershipModel;
use App\Models\OrganisationModel;
use CodeIgniter\Database\Exceptions\DatabaseException;
use ReflectionException;

/**
 * Return all organisations
 *
 * @return Organisation[]
 * @throws DatabaseException
 */
function getOrganisations(): array
{
    return getOrganisationModel()->findAll();
}

/**
 * Returns all organisations where name matches
 *
 * @param string $name organisation name
 * @return Organisation[]
 */
function getOrganisationsByName(string $name): array
{
    return getOrganisationModel()->like('name', $name)->orLike('short_name', $name)->findAll();
}

/**
 * Returns the organisation corresponding to the given id
 *
 * @param int $id organisation id
 * @return ?Organisation
 * @throws DatabaseException
 */
function getOrganisationById(int $id): ?object
{
    return getOrganisationModel()->find($id);
}

/**
 * Get an organisation's children by its parent id
 *
 * @param int $parentId parent organisation id
 * @return array
 */
function getChildOrganisationsByParentId(int $parentId): array
{
    return getOrganisationModel()->where(['parent_id' => $parentId])->findAll();
}

/**
 * Updates a given organisation in the database
 *
 * @param Organisation $organisation the modified organisation
 * @return void
 * @throws ReflectionException
 */
function saveOrganisation(Organisation $organisation): void
{
    if (!$organisation->hasChanged()) {
        return;
    }

    getOrganisationModel()->save($organisation);
}

/**
 * Creates an organisation with the given parameters
 *
 * @param string $name
 * @param string $shortName
 * @param int $regionId
 * @param int|null $parentId
 * @return Organisation
 */
function createOrganisation(string $name, string $shortName, int $regionId, int $parentId = null): Organisation
{
    $organisation = new Organisation();
    $organisation->setName($name);
    $organisation->setShortName($shortName);
    $organisation->setRegionId($regionId);
    $organisation->setParentId($parentId);
    return $organisation;
}

/**
 * Inserts an organisation and returns its newly created id
 *
 * @throws ReflectionException
 */
function insertOrganisation(Organisation $organisation): string|int
{
    $model = getOrganisationModel();
    $model->insert($organisation);
    return $model->getInsertID();
}

/**
 * Deletes the organisation with the corresponding id
 *
 * @param int $id
 * @return void
 */
function deleteOrganisation(int $id): void
{
    getOrganisationModel()->delete($id);
}

/**
 * Returns the membership (or join request) for a given user in a given organisation
 *
 * @param int $userId
 * @param int $organisationId
 * @return ?Membership
 */
function getMembership(int $userId, int $organisationId): ?object
{
    return getMembershipModel()->where('user_id', $userId)->where('organisation_id', $organisationId)->first();
}

/**
 * Returns the memberships (or join requests) for a given user
 *
 * @param int $userId
 * @return Membership[]
 */
function getMembershipsByUserId(int $userId): array
{
    return getMembershipModel()->where('user_id', $userId)->findAll();
}

/**
 * Returns the memberships (or join requests) for a given organisation
 *
 * @param int $organisationId
 * @return Membership[]
 */
function getMembershipsByOrganisationId(int $organisationId): array
{
    return getMembershipModel()->where('organisation_id', $organisationId)->findAll();
}

/**
 * Returns the members (join requests excluded) for a given organisation
 *
 * @param int $organisationId
 * @return Membership[]
 */
function getMembers(int $organisationId): array
{
    return getMembershipModel()->where('organisation_id', $organisationId)->whereNotIn('status', [MembershipStatus::PENDING->value])->findAll();
}

/**
 * Returns count of members (join requests excluded) for a given organisation
 *
 * @param int $organisationId
 * @return int
 */
function countMembers(int $organisationId): int
{
    return getMembershipModel()->where('organisation_id', $organisationId)->whereNotIn('status', [MembershipStatus::PENDING->value])->countAllResults();
}

/**
 * Returns all organisations in a given region
 *
 * @param int $regionId
 * @return Organisation[]
 * @throws DatabaseException
 */
function getOrganisationsByRegionId(int $regionId): array
{
    return getOrganisationModel()->where('region_id', $regionId)->where('parent_id', null)->findAll();
}

/**
 * Creates a membership request with the given parameters
 *
 * @param int $userId
 * @param int $organisationId
 * @return void
 * @throws DatabaseException
 * @throws ReflectionException
 */
function createMembershipRequest(int $userId, int $organisationId): void
{
    $membership = new Membership();
    $membership->setUserId($userId);
    $membership->setOrganisationId($organisationId);
    $membership->setStatus(MembershipStatus::PENDING);
    saveMembership($membership);
}

/**
 * Creates membership with given parameters
 *
 * @param int $userId
 * @param int $organisationId
 * @param MembershipStatus $status
 * @return void
 * @throws ReflectionException
 */
function createMembership(int $userId, int $organisationId, MembershipStatus $status = MembershipStatus::USER): void
{
    $membership = new Membership();
    $membership->setUserId($userId);
    $membership->setOrganisationId($organisationId);
    $membership->setStatus($status);
    saveMembership($membership);
}

/**
 * Deletes an organisation membership
 *
 * @param int $userId
 * @param int $organisationId
 * @return void
 */
function deleteMembership(int $userId, int $organisationId): void
{
    getMembershipModel()->where('user_id', $userId)->where('organisation_id', $organisationId)->delete();
}

/**
 * Saves given membership model
 *
 * @param Membership $membership
 * @return void
 * @throws ReflectionException
 */
function saveMembership(Membership $membership): void
{
    getMembershipModel()->save($membership);
}

/**
 * Create a notification for all organisation members
 *
 * @param int $organisationId
 * @param string $subject
 * @param string $body
 * @param MembershipStatus|null $status
 * @param array $exceptUsers
 * @return void
 */
function createOrganisationNotification(int $organisationId, string $subject, string $body, MembershipStatus $status = null, array $exceptUsers = []): void
{
    $organisation = getOrganisationById($organisationId);
    $body = sprintf($body, $organisation->getUrl());

    foreach ($organisation->getMemberships() as $membership) {
        // If user isn't an accepted member of the organisation
        if ($membership->getStatus() == MembershipStatus::PENDING) {
            continue;
        }

        // If user hasn't got given membership status
        if ($status != null && $membership->getStatus() != $status) {
            continue;
        }

        // If user is in given exception list
        if (in_array($membership->getUserId(), $exceptUsers)) {
            continue;
        }

        createNotification($membership->getUserId(), $subject, $body);
    }
}

/**
 * Returns the group table wrapper and query builder
 *
 * @return OrganisationModel
 */
function getOrganisationModel(): OrganisationModel
{
    return new OrganisationModel();
}

/**
 * Returns the group membership table wrapper and query builder
 *
 * @return MembershipModel
 */
function getMembershipModel(): MembershipModel
{
    return new MembershipModel();
}
