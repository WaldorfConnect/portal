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
 * Returns all organisations.
 *
 * @return Organisation[]
 * @throws DatabaseException
 */
function getOrganisations(): array
{
    return getOrganisationModel()->findAll();
}

/**
 * Returns the organisation corresponding to the given id.
 *
 * @param int $id
 * @return ?Organisation
 * @throws DatabaseException
 */
function getOrganisationById(int $id): ?object
{
    return getOrganisationModel()->find($id);
}

/**
 * @param int $parentId
 * @return array
 */
function getChildOrganisationsByParentId(int $parentId): array
{
    return getOrganisationModel()->where(['parent_id' => $parentId])->findAll();
}

/**
 * Saves the given organisation and returns the id of the new entry.
 *
 * @param Organisation $group
 * @return string|int
 * @throws DatabaseException|ReflectionException
 */
function saveOrganisation(Organisation $group): string|int
{
    $model = new OrganisationModel();
    $model->save($group);
    return $model->getInsertID();
}

/**
 * Creates an organisation with the given parameters.
 *
 * @param string $name
 * @param string $shortName
 * @param int $regionId
 * @param int|null $parentId
 * @return Organisation
 */
function createOrganisation(string $name, string $shortName, int $regionId, int $parentId = null): Organisation
{
    $group = new Organisation();
    $group->setName($name);
    $group->setShortName($shortName);
    $group->setRegionId($regionId);
    $group->setParentId($parentId);
    return $group;
}

/**
 * Deletes the organisation with the corresponding id.
 *
 * @param int $id
 * @return void
 */
function deleteOrganisation(int $id): void
{
    getOrganisationModel()->delete($id);
}

/**
 * Returns the membership (or join request) for a given user in a given organisation.
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
 * Returns the memberships (or join requests) for a given user.
 *
 * @param int $userId
 * @return Membership[]
 */
function getMembershipsByUserId(int $userId): array
{
    return getMembershipModel()->where('user_id', $userId)->findAll();
}

/**
 * Returns the memberships (or join requests) for a given organisation.
 *
 * @param int $organisationId
 * @return Membership[]
 */
function getMembershipsByOrganisationId(int $organisationId): array
{
    return getMembershipModel()->where('organisation_id', $organisationId)->findAll();
}

/**
 * Returns the members (join requests excluded) for a given organisation.
 *
 * @param int $organisationId
 * @return Membership[]
 */
function getMembers(int $organisationId): array
{
    return getMembershipModel()->where('organisation_id', $organisationId)->whereNotIn('status', [MembershipStatus::PENDING->value])->findAll();
}

/**
 * Returns count of members (join requests excluded) for a given organisation.
 *
 * @param int $organisationId
 * @return int
 */
function countMembers(int $organisationId): int
{
    return getMembershipModel()->where('organisation_id', $organisationId)->whereNotIn('status', [MembershipStatus::PENDING->value])->countAllResults();
}

/**
 * Returns the join requests (members excluded) for a given organisation.
 *
 * @param int $organisationId
 * @return Membership[]
 */
function getJoinRequests(int $organisationId): array
{
    return getMembershipModel()->where('organisation_id', $organisationId)->where('status', MembershipStatus::PENDING->value)->findAll();
}

/**
 * Returns all organisations in a given region.
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
 * Creates a membership request with the given parameters.
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
 * Create membership with given parameters.
 *
 * @param int $userId
 * @param int $organisationId
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

function deleteMembership(int $userId, int $organisationId): void
{
    getMembershipModel()->where('user_id', $userId)->where('organisation_id', $organisationId)->delete();
}

/**
 * Saves given membership model.
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
 * Returns the group table wrapper and query builder.
 *
 * @return OrganisationModel
 */
function getOrganisationModel(): OrganisationModel
{
    return new OrganisationModel();
}

/**
 * Returns the group membership table wrapper and query builder.
 *
 * @return MembershipModel
 */
function getMembershipModel(): MembershipModel
{
    return new MembershipModel();
}
