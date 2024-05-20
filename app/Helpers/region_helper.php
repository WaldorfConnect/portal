<?php

namespace App\Helpers;

use App\Entities\Region;
use App\Models\RegionModel;
use CodeIgniter\Database\Exceptions\DatabaseException;
use ReflectionException;

/**
 * Returns all regions
 *
 * @return Region[]
 * @throws DatabaseException
 */
function getRegions(): array
{
    return getRegionModel()->findAll();
}

/**
 * Returns a region by its id
 *
 * @param int $id the region id
 * @return ?Region
 */
function getRegionById(int $id): ?object
{
    return getRegionModel()->find($id);
}

/**
 * Saves the changes to a region to the database
 *
 * @param Region $region
 * @return void
 * @throws ReflectionException
 */
function saveRegion(Region $region): void
{
    if (!$region->hasChanged()) {
        return;
    }

    getRegionModel()->save($region);
}

/**
 * Create and insert a new region
 *
 * @param string $name The name of the new region
 * @throws ReflectionException
 */
function createAndInsertRegion(string $name): Region
{
    $region = new Region();
    $region->setName($name);

    $model = getRegionModel();
    $model->insert($region);
    $region->setId($model->getInsertID());

    return $region;
}

/**
 * @param int $id
 * @return void
 * @throws DatabaseException
 */
function deleteRegion(int $id): void
{
    getRegionModel()->delete($id);
}

/**
 * @return RegionModel
 */
function getRegionModel(): RegionModel
{
    return new RegionModel();
}
