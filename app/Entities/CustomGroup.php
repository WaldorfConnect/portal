<?php

namespace App\Entities;

use LdapRecord\Models\Model;
use LdapRecord\Models\OpenLDAP\User;
use LdapRecord\Models\Relations\HasManyIn;

class CustomGroup extends Model
{
    /**
     * The object classes of the LDAP model.
     */
    public static array $objectClasses = [
        'top',
        'groupofuniquenames',
        'uidobject'
    ];

    public function members(): HasManyIn
    {
        return $this->hasManyIn([static::class, User::class], 'uniquemember')->using($this, 'uniquemember');
    }
}
