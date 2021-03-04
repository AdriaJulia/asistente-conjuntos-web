<?php

namespace App\Form\Model;

use App\Entity\User;
use DateTime;

class UserDto
{
    public $username;
    public $password;
    public $ldapToken;

 
    public function __construct()
    {
 
    }
    
    public static function createFromUser(User $user): self
    {
        $dto = new self();
        $dto->username =  $user->getUsername();
        $dto->password =  $user->getPassword();
        $dto->ldapToken =  $user->getLdapToken();
        return $dto;
    }

    public function getFromArray($array) : self {
 
        $res = new self();
        /*

        */
        return $res;
    }

}

