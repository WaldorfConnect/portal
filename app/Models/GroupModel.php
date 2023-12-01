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
        'parent_group_id', 'name', 'short_name', 'region_id', 'address', 'description', 'website_url', 'email_office', 'email_students', 'image_author'
    ];
}


