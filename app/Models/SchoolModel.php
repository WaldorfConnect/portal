<?php

namespace App\Models;

use App\Entities\Group;
use App\Entities\GroupMembership;
use App\Entities\Region;
use App\Entities\School;
use CodeIgniter\Model;

class SchoolModel extends Model
{
    protected $table = SCHOOLS;
    protected $primaryKey = "id";
    protected $returnType = School::class;

    protected $allowedFields = [
        'name', 'short_name', 'region_id', 'address', 'website_url', 'email_bureau', 'email_smv', 'state_id', 'image_author'
    ];
}


