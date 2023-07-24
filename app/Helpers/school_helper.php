<?php

namespace App\Helpers;

use App\Entities\Group;
use App\Entities\School;
use App\Models\GroupModel;
use App\Models\SchoolModel;
use CodeIgniter\Database\Exceptions\DatabaseException;

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
 * @return SchoolModel
 */
function getSchoolModel(): SchoolModel
{
    return new SchoolModel();
}
