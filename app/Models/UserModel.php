<?php

namespace App\Models;

use App\Entities\User;
use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = USERS;
    protected $primaryKey = "id";
    protected $returnType = User::class;

    protected $allowedFields = [
        'username', 'name', 'email', 'password', 'global_admin', 'status', 'token'
    ];
}


