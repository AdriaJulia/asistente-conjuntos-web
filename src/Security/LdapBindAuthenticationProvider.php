<?php

namespace App\Security;

use Symfony\Component\Ldap\Security\LdapUser;
use Symfony\Component\Ldap\Security\LdapUserProvider;
use Symfony\Component\Ldap\Entry;
use Symfony\Component\Ldap\Exception\ConnectionException;
use Symfony\Component\Ldap\Exception\ExceptionInterface;
use Symfony\Component\Ldap\LdapInterface;
use Symfony\Component\Security\Core\Exception\InvalidArgumentException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

/*
 * Descripción: Refactorización del proveedor de autenticación Ldap
 *              Es necesario por las especificación de los ditintos ramales Ldap del gobierno de aragon y la forma acceder a ellos.
 *              Desde aquí se genera la clave JWT para registrarse en Apirest y solicitar Tokens autorizados.
 *              También se raliza la gestion de roles dinamicamente cada vez que el usuario hace login, respecto a la configuración
 *              actual de administradores. 
 *                   
 */

class LdapBindAuthenticationProvider extends LdapUserProvider
{
    private $ldap;
    private $baseDn;
    private $searchDn;
    private $searchPassword;
    private $defaultRoles;
    private $uidKey;
    private $defaultSearch;
    private $passwordAttribute;
    private $extraFields;
    private $params;

    
    public function __construct(LdapInterface $ldap, 
                                string $baseDn, 
                                string $searchDn = null, 
                                string $searchPassword = null, 
                                array  $defaultRoles = [], 
                                string $uidKey = null, 
                                string $filter = null, 
                                string $passwordAttribute = null, 
                                array  $extraFields = ["mail"],
                                ContainerBagInterface $params)
    {
        $this->params = $params;
        if (null === $uidKey) {
            $uidKey = 'uid';
        }

        if (null === $filter) {
            $filter = '({uid_key}={username})';
        }

        $this->ldap = $ldap;
        $this->baseDn = $baseDn;
        $this->searchDn = $searchDn;
        $this->searchPassword = $searchPassword;
        $this->defaultRoles = $defaultRoles;
        $this->uidKey = $uidKey;
        $this->defaultSearch = str_replace('{uid_key}', $uidKey, $filter);
        $this->passwordAttribute = $passwordAttribute;
        $this->extraFields = $extraFields;
    }

     /**
     * {@inheritdoc}
     */
    public function loadUserByUsername($username)
    { 
    }

    /**
     * {@inheritdoc}
     */
    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof LdapUser) {
            throw new UnsupportedUserException(sprintf('Las instancias of "%s" no son soportadas.', \get_class($user)));
        }

        return new LdapUser($user->getEntry(), $user->getUsername(), $user->getPassword(), $user->getRoles(), $user->getExtraFields());
    }

    /**
     * {@inheritdoc}
     */
    public function upgradePassword(UserInterface $user, string $newEncodedPassword): void
    {
        if (!$user instanceof LdapUser) {
            throw new UnsupportedUserException(sprintf('Las instancias of "%s" no son soportadas.', \get_class($user)));
        }

        if (null === $this->passwordAttribute) {
            return;
        }

        try {
            $user->getEntry()->setAttribute($this->passwordAttribute, [$newEncodedPassword]);
            $this->ldap->getEntryManager()->update($user->getEntry());
            $user->setPassword($newEncodedPassword);
        } catch (ExceptionInterface $e) {
            // ignore failed password upgrades
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        return LdapUser::class === $class;
    }

    /**
     * Carga el usuario desde LDAP entry.
     *
     * @return UserInterface
     */
    protected function loadUser($username, Entry $entry)
    {
        $password = null;
        $extraFields = [];

        if (null !== $this->passwordAttribute) {
            $password = $this->getAttributeValue($entry, $this->passwordAttribute);
        }

        foreach ($this->extraFields as $field) {
            $extraFields[$field] = $this->getAttributeValue($entry, $field);
        }
        //miro si el usuario es administrador
        $mail = $extraFields['mail'];
        $administradores = $this->params->get('app_administrators');
        $administrators = [];
        if (!empty($administradores)){
            $administrators = explode(",", $administradores );
        }
        $roll ="ROLE_USER";
        if (in_array($mail, $administrators)){
             $roll ="ROLE_ADMIN";
        }
        //pongo el rol en extrafiles
        $extraFields['roles'] =  $roll;
        //$this->params->get('secret_key');
         //pongo el pasword que se utiliza en JWT en extrafiles
        $password = $this->encrypt($mail);
        $password = base64_encode($password);
        $extraFields['password'] =  $password;
        return new LdapUser($entry, $username, $password, $this->defaultRoles, $extraFields);
        
    }

    /***
     * Descripcion: Es la funcion que intercepta la autenticación y lanza el proceso 
     *              de loging personalizado contra el LDAP
     *              
     * Parametros:
     *             username: nombre de usuario del formulario
     *             password: contraseña de usuario del formulario
     */
    public function getUserEntityCheckedFromLdap($username,$password){

        $correcto = !((empty($username) || empty($password)));
        $login = explode("@",$username);
        $aragonUsername = "";
        $aragonRama = "aragon.es";
        if (count($login)==2) {
            //recojo el nombre usuario Ldap
            $aragonUsername = $login[0];
            $username = $login[0];
            //asigno el nombre de la rama segun el dominio del usuario
            $aragonRama = ($login[1]=="ext.aragon.es") ? "dga" : $login[1];
        }
        //preparo la consulta LDAP
        $this->ldapSearchDnString = "uid={$username},ou=People,o=" .  $aragonRama  . ",o=isp";
        $this->ldapBaseDn = "ou=People,o=" .  $aragonRama  . ",o=isp";
      
        try{
            $this->ldap->bind($this->ldapSearchDnString, $password);
        } catch(\Exception $ex){
            $correcto = false; 
        }
        if (!$correcto){
          throw new UsernameNotFoundException("Credenciales incorrectas. Por favor, vuelva a intentarlo de nuevo");
        }
        
        $username = $this->ldap->escape($username, '', LdapInterface::ESCAPE_FILTER);
        $search = $this->ldap->query($this->ldapBaseDn, 'uid=' . $username);
        $entries = $search->execute();
        $count = count($entries);
        if (!$count) {
            throw new UsernameNotFoundException(sprintf('El usuario "%s" no existe en el directorio.', $username));
        }
        if ($count > 1) {
            throw new UsernameNotFoundException('Más de un usuario encontrado');
        }
        $entry = $entries[0];
        
        return $this->loadUser($username, $entry);
    }

    private function getAttributeValue(Entry $entry, string $attribute)
    {
        if (!$entry->hasAttribute($attribute)) {
            throw new InvalidArgumentException(sprintf('El atributo "%s" para el usuario "%s" no existe.', $attribute, $entry->getDn()));
        }

        $values = $entry->getAttribute($attribute);

        if (1 !== \count($values)) {
            throw new InvalidArgumentException(sprintf('El atributo "%s" tiene multiples valores.', $attribute));
        }

        return $values[0];
    }

 
     /***
     * Descripcion: Obtiene un alfanumérico dado una entrada (el correo del usuario) 
     *              El objetivo es obtener una contraseña aleatoria (pero simpre la misma dado un correo)
     *              para utilizar en JWT y que el proceso de registro en Apirest sea transparete al usuario
     *              
     * Parametros:
     *             string: correo del usuario
     */
    function encrypt($string) {
        $output = false;
        $encrypt_method = "AES-256-CBC";
        $secret_key = $this->params->get('secret_key'); '';
        $secret_iv =  $this->params->get('secret_iv'); '';
        
        $key = hash('sha256', $secret_key);
        $iv = substr(hash('sha256', $secret_iv), 0, 16);
        $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
        $output = base64_encode($output);
        return $output;
    }
}
                        

