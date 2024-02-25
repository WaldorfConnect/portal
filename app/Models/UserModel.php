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
        'username', 'name', 'email', 'password', 'admin', 'active', 'email_confirmed', 'password_reset', 'token', 'registration_date', 'last_login_date'
    ];
}


