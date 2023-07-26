<?php

namespace App\Helpers;

use App\Exceptions\LDAPException;
use LdapRecord\Auth\BindException;
use LdapRecord\Configuration\ConfigurationException;
use LdapRecord\Connection;
use LdapRecord\Container;
use LdapRecord\LdapRecordException;

/**
 * @throws LDAPException
 */
function createLDAPConnection(): Connection
{
    try {
        return new Connection([
            'hosts' => [getenv('ldap.host')],
            'base_dn' => getenv('ldap.baseDN'),
            'username' => getenv('ldap.admin.username'),
            'password' => getenv('ldap.admin.password')
        ]);
    } catch (ConfigurationException $e) {
        throw new LDAPException('Error while creating ldap connection', $e);
    }
}

/**
 * @throws LDAPException
 */
function openLDAPConnection(): Connection
{
    // If container already holds connection
    if (Container::hasConnection(Container::getDefaultConnectionName())) {
        return Container::getDefaultConnection();
    }

    // Create new connection
    $connection = createLDAPConnection();
    try {
        $connection->connect();
    } catch (BindException|LdapRecordException $e) {
        throw new LDAPException('Error binding to ldap server', $e);
    }
    Container::addConnection($connection, Container::getDefaultConnectionName());

    return $connection;
}