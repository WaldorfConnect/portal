<?php

namespace App\Models\OIDC;

use App\Entities\OIDC\AccessToken;
use App\Entities\OIDC\AuthCode;
use CodeIgniter\Model;

class AuthCodeModel extends Model
{
    protected $table = OIDC_AUTH_CODES;
    protected $primaryKey = "id";
    protected $returnType = AuthCode::class;

    protected $allowedFields = [
        'id', 'client_id', 'user_id', 'scopes', 'expire_at'
    ];
}


