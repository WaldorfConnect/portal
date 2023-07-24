<?php

namespace App\Helpers;

use App\Entities\Region;
use App\Models\RegionModel;
use CodeIgniter\Database\Exceptions\DatabaseException;

/**
 * @return Region[]
 * @throws DatabaseException
 */
function getRegions(): array
{
    return getRegionModel()->findAll();
}

/**
 * @return RegionModel
 */
function getRegionModel(): RegionModel
{
    return new RegionModel();
}
