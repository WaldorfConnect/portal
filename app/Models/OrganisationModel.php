<?php

namespace App\Models;

use App\Entities\Organisation;
use CodeIgniter\Model;

class OrganisationModel extends Model
{
    protected $table = ORGANISATIONS;
    protected $primaryKey = "id";
    protected $returnType = Organisation::class;

    protected $allowedFields = [
        'parent_id', 'name', 'short_name', 'region_id', 'address', 'description', 'website_url', 'email', 'image_id', 'logo_id', 'folder_id'
    ];
}


