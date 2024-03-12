<?php

namespace App\Models\OIDC;

use App\Entities\OIDC\AccessToken;
use App\Entities\OIDC\AuthCode;
use App\Entities\OIDC\RefreshToken;
use CodeIgniter\Model;

class RefreshTokenModel extends Model
{
    protected $table = OIDC_REFRESH_TOKENS;
    protected $primaryKey = "id";
    protected $returnType = RefreshToken::class;

    protected $allowedFields = [
        'id', 'access_token_id', 'expire_at'
    ];
}


