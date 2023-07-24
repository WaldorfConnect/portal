<?php

namespace App\Models;

use App\Entities\Group;
use App\Entities\GroupMembership;
use CodeIgniter\Model;

class GroupMembershipModel extends Model
{
    protected $table = MEMBERSHIPS;
    protected $primaryKey = "id";
    protected $returnType = GroupMembership::class;

    protected $allowedFields = [
        'user_id', 'group_id', 'status'
    ];
}


