<?php

namespace App\Security;


use App\Entity\User;
use Symfony\Component\Ldap\Security\LdapUser;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\AST\Join;
use Symfony\Component\Ldap\Security\LdapUserProvider;
use Symfony\Component\Ldap\Entry;
use Symfony\Component\Ldap\Exception\ConnectionException;
use Symfony\Component\Ldap\Exception\ExceptionInterface;
use Symfony\Component\Ldap\LdapInterface;
use Symfony\Component\Security\Core\Exception\InvalidArgumentException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

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
                                array $defaultRoles = [], 
                                string $uidKey = null, 
                                string $filter = null, 
                                string $passwordAttribute = null, 
                                array $extraFields = ["mail"],
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
        $login = explode("@",$username);
        $aragonUsername = "";
        $aragonRama = "aragon.es";
        if (count($login)==2) {
            $aragonUsername = $login[0];
            $username = $login[0];
            $aragonRama = ($login[1]=="ext.aragon.es") ? "dga" : $login[1];
        }
        $this->searchDn = "uid={username},ou=People,o=" .  $aragonRama  . ",o=isp";
        $this->baseDn = "ou=People,o=" .  $aragonRama  . ",o=isp";
        try {
            $this->ldap->bind($this->searchDn, $this->searchPassword);
            $username = $this->ldap->escape($username, '', LdapInterface::ESCAPE_FILTER);
            $query = str_replace('{username}', $username, $this->defaultSearch);
            $search = $this->ldap->query($this->baseDn, $query);
        } catch (ConnectionException $e) {
            $e = new UsernameNotFoundException(sprintf('User "%s" not found.', $username), 0, $e);
            $e->setUsername($username);

            throw $e;
        }
       
        $entries = $search->execute();
        $count = \count($entries);
        if (!$count) {
            $e = new UsernameNotFoundException(sprintf('User "%s" not found.', $username));
            $e->setUsername($username);

            throw $e;
        }

        if ($count > 1) {
            $e = new UsernameNotFoundException('More than one user found.');
            $e->setUsername($username);

            throw $e;
        }

        $entry = $entries[0];

        try {
            if (null !== $this->uidKey) {
                $username = $this->getAttributeValue($entry, $this->uidKey);
            }
        } catch (InvalidArgumentException $e) {
        }

        return $this->loadUser($username, $entry);
    }

    /**
     * {@inheritdoc}
     */
    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof LdapUser) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        return new LdapUser($user->getEntry(), $user->getUsername(), $user->getPassword(), $user->getRoles(), $user->getExtraFields());
    }

    /**
     * {@inheritdoc}
     */
    public function upgradePassword(UserInterface $user, string $newEncodedPassword): void
    {
        if (!$user instanceof LdapUser) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
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
     * Loads a user from an LDAP entry.
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
        $extraFields['roles'] =  $roll;
        //$this->params->get('secret_key');
        $password = $this->encrypt($mail);
        $password = base64_encode($password);
        $extraFields['password'] =  $password;

  
        return new LdapUser($entry, $username, $password, $this->defaultRoles, $extraFields);
        
    }

    private function getAttributeValue(Entry $entry, string $attribute)
    {
        if (!$entry->hasAttribute($attribute)) {
            throw new InvalidArgumentException(sprintf('Missing attribute "%s" for user "%s".', $attribute, $entry->getDn()));
        }

        $values = $entry->getAttribute($attribute);

        if (1 !== \count($values)) {
            throw new InvalidArgumentException(sprintf('Attribute "%s" has multiple values.', $attribute));
        }

        return $values[0];
    }

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
                        

