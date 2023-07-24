<?php

namespace App\Models;

use App\Entities\Group;
use CodeIgniter\Model;

class GroupModel extends Model
{
    protected $table = GROUPS;
    protected $primaryKey = "id";
    protected $returnType = Group::class;

    protected $allowedFields = [
        'region_id', 'name', 'description'
    ];
}


