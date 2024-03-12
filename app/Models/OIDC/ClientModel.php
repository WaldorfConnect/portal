<?php

namespace App\Models\OIDC;

use App\Entities\OIDC\Client;
use CodeIgniter\Model;

class ClientModel extends Model
{
    protected $table = OIDC_CLIENTS;
    protected $primaryKey = "id";
    protected $returnType = Client::class;

    protected $allowedFields = [
        'id', 'name', 'redirect_uri', 'secret', 'confidential'
    ];
}


