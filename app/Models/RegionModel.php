<?php

namespace App\Models;

use App\Entities\Region;
use CodeIgniter\Model;

class RegionModel extends Model
{
    protected $table = REGIONS;
    protected $primaryKey = "id";
    protected $returnType = Region::class;

    protected $allowedFields = [
        'name'
    ];
}


