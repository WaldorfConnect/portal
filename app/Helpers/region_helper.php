<?php

namespace App\Helpers;

use App\Entities\Region;
use App\Entities\User;
use App\Models\RegionModel;
use CodeIgniter\Database\Exceptions\DatabaseException;
use ReflectionException;

/**
 * @return Region[]
 * @throws DatabaseException
 */
function getRegions(): array
{
    return getRegionModel()->findAll();
}

/**
 * @param int $id
 * @return ?Region
 */
function getRegionById(int $id): ?object
{
    return getRegionModel()->find($id);
}

/**
 * @param Region $region
 * @return string|int
 * @throws DatabaseException|ReflectionException
 */
function saveRegion(Region $region): string|int
{
    $model = getRegionModel();
    $model->save($region);
    return $model->getInsertID();
}

function createRegion(string $name, string $iso): Region
{
    $region = new Region();
    $region->setName($name);
    $region->setIsoCode($iso);
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

function isRegionAdmin(int $userId): bool
{

}

function isRegionAdmin(int $userId, int $regionId): bool
{

}

/**
 * @return RegionModel
 */
function getRegionModel(): RegionModel
{
    return new RegionModel();
}
