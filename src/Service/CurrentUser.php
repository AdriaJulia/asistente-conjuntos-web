<?php

namespace App\Service;

use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Ldap\Security\LdapUser;

/*
 * Descripción: Clase para poder tener el objeto LDAP  con los datos del usuario
 *              en cualquier controlador
*/
class CurrentUser
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function getCurrentUser() : ?LdapUser
    {
        return $this->security->getUser() ;
    }
}