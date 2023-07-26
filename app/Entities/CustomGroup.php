<?php

namespace App\Entities;

use LdapRecord\Models\Model;

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
}
