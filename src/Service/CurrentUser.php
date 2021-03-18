<?php

namespace App\Service;

use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Ldap\Security\LdapUser;

/*
 * Descripción: Clase para poder tener  el objeto LDAP  con los datos del usuario
 *              en cualquier controlador
*/
class CurrentUser
{
    private $security;

    public function __construct(Security $security)
    {
        // Avoid calling getUser() in the constructor: auth may not
        // be complete yet. Instead, store the entire Security object.
        $this->security = $security;
    }

    public function getCurrentUser() : ?LdapUser
    {
        // returns User object or null if not authenticated
        return $this->security->getUser() ;
    }
}