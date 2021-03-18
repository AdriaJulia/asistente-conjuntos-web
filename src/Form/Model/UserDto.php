<?php

namespace App\Form\Model;

use App\Entity\User;
use DateTime;

/*
 * Descripción: Es la clase dto del usuario  
 *              Es el objeto que recoge los datos de los formularios login             
 */
class UserDto
{
    public $username;
    public $password;



    public function __construct()
    {
 
    }
    
    public static function createFromUser(User $user): self
    {
        $dto = new self();
        $dto->username =  $user->getUsername();
        $dto->password =  $user->getPassword();
        return $dto;
    }

    public function getFromArray($array) : self {
 
        $res = new self();
        /*

        */
        return $res;
    }

}

