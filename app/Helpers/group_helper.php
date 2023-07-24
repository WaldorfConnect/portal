<?php

namespace App\Helpers;

use App\Entities\Group;
use App\Entities\School;
use App\Models\GroupModel;
use CodeIgniter\Database\Exceptions\DatabaseException;

/**
 * @return Group[]
 * @throws DatabaseException
 */
function getGroups(): array
{
    return getGroupModel()->findAll();
}

/**
 * @return Group[]
 * @throws DatabaseException
 */
function getGroupsByRegionId(int $regionId): array
{
    return getGroupModel()->where('region_id', $regionId)->findAll();
}

/**
 * @return GroupModel
 */
function getGroupModel(): GroupModel
{
    return new GroupModel();
}
