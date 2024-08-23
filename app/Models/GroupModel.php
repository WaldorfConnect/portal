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
        'parent_id', 'name', 'short_name', 'region_id', 'address', 'description', 'website', 'email', 'phone', 'latitude', 'longitude', 'image_id', 'logo_id', 'folder_id', 'chat_id'
    ];
}


