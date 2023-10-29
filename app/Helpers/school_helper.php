<?php

namespace App\Helpers;

use App\Entities\Group;
use App\Entities\School;
use App\Models\GroupModel;
use App\Models\SchoolModel;
use CodeIgniter\Database\Exceptions\DatabaseException;
use ReflectionException;

/**
 * @return School[]
 * @throws DatabaseException
 */
function getSchools(): array
{
    return getSchoolModel()->findAll();
}

/**
 * @param int $id
 * @return ?School
 * @throws DatabaseException
 */
function getSchoolById(int $id): ?object
{
    return getSchoolModel()->find($id);
}

/**
 * @return School[]
 * @throws DatabaseException
 */
function getSchoolsByRegionId(int $regionId): array
{
    return getSchoolModel()->where('region_id', $regionId)->findAll();
}

/**
 * @param School $school
 * @return string|int
 * @throws DatabaseException|ReflectionException
 */
function saveSchool(School $school): string|int
{
    $model = new SchoolModel();
    $model->save($school);
    return $model->getInsertID();
}

function createSchool(string $name, string $shortName, string $address, string $emailBureau, int $regionId): School
{
    $school = new School();
    $school->setName($name);
    $school->setShortName($shortName);
    $school->setAddress($address);
    $school->setEmailBureau($emailBureau);
    $school->setRegionId($regionId);
    return $school;
}

function deleteSchool(int $id): void
{
    getSchoolModel()->delete($id);
}

/**
 * @return SchoolModel
 */
function getSchoolModel(): SchoolModel
{
    return new SchoolModel();
}
