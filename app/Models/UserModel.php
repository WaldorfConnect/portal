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
        'username', 'first_name', 'last_name', 'email', 'email_confirmed', 'password', 'password_reset', 'admin', 'active', 'token', 'registration_date', 'accept_date', 'last_login_date'
    ];
}


