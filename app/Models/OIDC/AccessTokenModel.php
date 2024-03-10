<?php

namespace App\Models\OIDC;

use App\Entities\OIDC\AccessToken;
use CodeIgniter\Model;

class AccessTokenModel extends Model
{
    protected $table = OIDC_ACCESS_TOKENS;
    protected $primaryKey = "id";
    protected $returnType = AccessToken::class;

    protected $allowedFields = [
        'id', 'client_id', 'user_id', 'scopes', 'expire_at'
    ];
}


