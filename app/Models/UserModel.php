<?php

namespace App\Models;

use App\Entities\Group;
use App\Entities\GroupMembership;
use App\Entities\Region;
use App\Entities\School;
use App\Entities\User;
use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = USERS;
    protected $primaryKey = "id";
    protected $returnType = User::class;

    protected $allowedFields = [
        'token', 'username', 'name', 'email', 'password', 'school_id', 'status'
    ];
}


