<?php

namespace App\Service\RestApiLocal;

use App\Service\CurrentUser;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use App\Enum\RutasApirestEnum;

use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
 
/*
 * Descripción: Clase que lanza la solicitud sobre la rest API.
 *              Se instancia desde el constructor de las distintas clases de acceso a la rest API en este mismo namepace.
 *              Su principal funcionalidad es hacer puente entre estas llamadas a la rest API y la obtención 
 *              y refresco del JWT en ada llamada.
 *              También prepara todo los demás encabezados comunes a todas la llamadas cualquiera  que sea la funcionalidad.
*/
class RestApiClient
{
    private $client;
    private $params;
    private $currentUser;

    public function __construct(HttpClientInterface $client,
                                ContainerBagInterface $params,
                                CurrentUser $currentUser){
        $this->client = $client;
        $this->params = $params;
        $this->currentUser = $currentUser;
    }
    
    /*
     * Descripción: Registro del usuario para la optencion del JWT.
     *              Este usurio se persiste en la BD y no tiene nada que ver con el LDAP, 
     *              es para la seguridad de las llamadas a la Apirest con JWT.
     *              El nombre del usuario y el correo si es el mismo que el de LDAP, 
     *              pero son usuarios de sisteas de seguridad distintos. 
     * 
     * Parametros: 
     *          session sesion request
    */ 
    public function register(Session $session){

        if ($session->get('registrado')==null){
            [$user,$password,$roles] = $this->dameUsuarioJWT();

            $url = $this->params->get('host_restapi') . RutasApirestEnum::USUARIO_REGISTER_POST;
            $data = "{\"username\":\"{$user}\",\"password\":\"{$password}\",\"roles\":\"{$roles}\" }";
            $usuario = $this->PostInformation($url, $data);
            $session->set('registrado',"true");
        }
    }

    /*
     * Descripción: Obtención de un token JWT por su usuario y contraseña
     * 
     * Parametros: 
     *          session: sesion request
     *          usario: usuario JWT
     *          contasena: contraseña JWT
    */ 
    private function login_check($session,
                                 $usario, 
                                 $contasena) : string {
            
        $url = $this->params->get('host_restapi') . RutasApirestEnum::USUARIO_LOGIN_CHECK_POST;
        $data = "{\"_username\":\"{$usario}\",\"_password\":\"{$contasena}\"}";
        $res = $this->PostInformation($url,$data);
        if ($res['statusCode']==401) {
            $session->remove('registrado');
            $this->register($session);
            $res = $this->PostInformation($url,$data);
        }
        $token = "Bearer " . $res['data']['token'];
        return $token;
    }

    /*
     * Descripción: Obtención de un token JWT en sesion, para no hacer la llamada apirest todo el rato
     *              El token jwt dura una hora
     * 
     * Parametros: 
     *          session: sesion request
     *          renew:   si renueva el token
    */ 
    public function GetTokenSession(Session $session, bool $renew) : string {
        $token = "";
        $this->register($session);
        if (($session->get('jwt')!==null) && !$renew){
            $token  =  $session->get('jwt'); 
        } else {
            [$user,$password,$roles] = $this->dameUsuarioJWT();
            $token = $this->login_check($session, $user,$password);
            $session->set('jwt',$token);
        } 
        return $token;
    }

    /*
     * Descripción: Llamada get api rest
     * 
     * Parametros: 
     *          url: url a la que se llama
     *          token: token JWT
     *          session: sesion request
    */ 
    public function GetContent($url, $token, $session): array {
        //dame contenido
        $content = $this->GetInformation($url, $token);
        //si devuelves error 401 es ie el token JWT esta caducado y tengo que pedir otro.
        if ($content['statusCode']==401) {
            //pido nuevo token
          //  $token = $this->GetTokenSession($session, true);
            [$user,$password,$roles] = $this->dameUsuarioJWT();
            $token = $this->login_check($session,$user,$password);
            //dame contenido                       
            $content = $this->GetInformation($url, $token);
        }
        return  $content;
    }

    /*
     * Descripción: Llamada post api rest
     * 
     * Parametros: 
     *          url: url a la que se llama
     *          jsondata: datos que se envían
     *          token: token JWT
     *          session: sesion request
    */ 
    public function PostContent($url, $jsondata, $token, $session): array {
         //dame contenido
        $content = $this->PostInformation($url, $jsondata, $token);
          //si devuelves error 401 es ie el token JWT esta caducado y tengo que pedir otro.
        if ($content['statusCode']==401) {
            $token = $this->GetTokenSession($session, true);
            $content = $this->PostInformation($url, $jsondata, $token);
        }
        return  $content;
    }

    /*
     * Descripción: Llamada delete api rest
     * 
     * Parametros: 
     *          url: url a la que se llama
     *          token: token JWT
     *          session: sesion request
    */ 
    public function DeleteContent($url, $token, $session): array {
        //dame contenido
        $content = $this->DeleteInformation($url, $token);
        //si devuelves error 401 es ie el token JWT esta caducado y tengo que pedir otro.
        if ($content['statusCode']==401) {
            $token = $this->GetTokenSession($session, true);
            $content = $this->DeleteInformation($url, $token);
        }
        return  $content;
    }

    /*
     * Descripción: Llamada get api rest
     *              Esta funcion es para hacer trasparente los errores de renovacion de JWT y registro Usuario JWT
     * 
     * Parametros: 
     *          url: url a la que se llama
     *          token: token JWT
    */ 
    private function GetInformation($ruta, $token): array {
        $content = array();

        if (isset($token)) {
            $response = $this->client->request('GET', $ruta, [
                'headers' => [
                    'content-type' => 'application/json',
                    'accept' => 'application/json',
                    'Authorization' => $token
                ],
            ]);
        }  else {
            $response = $this->client->request('GET', $ruta, [
                'headers' => [
                    'content-type' => 'application/json',
                    'accept' => 'application/json'
                ],
            ]);
        }
        $statusCode = $response->getStatusCode();
        if ($statusCode==401){
            return array('data'=>$content,'statusCode'=>$statusCode);
        } 
        // $statusCode = 200
        $contentType = $response->getHeaders()['content-type'][0];
        // $contentType = 'application/json'
        $content = $response->getContent();
        // $content = '{"id":521583, "name":"symfony-docs", ...}'
        $content = $response->toArray();
        // $content = ['id' => 521583, 'name' => 'symfony-docs', ...]

        return array('data'=>$content,'statusCode'=>$statusCode);
    }

    /*
     * Descripción: Llamada post api rest
     *              Esta funcion es para hacer trasparente los errores de renovacion de JWT y registro Usuario JWT
     * 
     * Parametros: 
     *          url: url a la que se llama
     *          token: token JWT
    */ 
    public function PostInformation($ruta, $jsondata, $token=null): array {
        
        $content = array();
        
        if (isset($token)) {
            $response = $this->client->request('POST', $ruta, [
                'headers' => [
                    'content-type' => 'application/json',
                    'accept' => 'application/json',
                    'Authorization' => $token
                ],
                'body' => $jsondata,
            ]);
        }  else {
            $response = $this->client->request('POST', $ruta, [
                'headers' => [
                    'content-type' => 'application/json',
                    'accept' => 'application/json'
                ],
                'body' => $jsondata,
            ]);
        }
        $statusCode = $response->getStatusCode();
        $errorprocesor="";
        // $statusCode = 200
       if ($statusCode==401){
            return  array('data'=>$content,'statusCode'=>$statusCode);;
       }  else if ($statusCode >= 402) {
            $errorprocesor = $response->getInfo()['response_headers'][4];
            return array('data'=>$errorprocesor,'statusCode'=>$statusCode);
       }

       $contentType = $response->getHeaders()['content-type'][0];
            
       $content = $response->getContent();
        // $content = '{"id":521583, "name":"symfony-docs", ...}'
       $content = $response->toArray();
        // $content = ['id' => 521583, 'name' => 'symfony-docs', ...]
    
       return array('data'=>$content,'statusCode'=>$statusCode);
    }

    /*
     * Descripción: Llamada delete api rest
     *              Esta funcion es para hacer trasparente los errores de renovacion de JWT y registro Usuario JWT
     * 
     * Parametros: 
     *          url: url a la que se llama
     *          token: token JWT
    */ 
    private function DeleteInformation($ruta, $token): array {

        $content = array();
        $response = $this->client->request('DELETE', $ruta, [
            'headers' => [
                'content-type' => 'application/json',
                'accept' => 'application/json',
                'Authorization' => $token
            ],
        ]);
        $statusCode = $response->getStatusCode(); 
        return  array('statusCode'=>$statusCode);
    }

     /*
     * Descripción: Esta funcion es para hacer las pruebas unitarias sin tener que estar logeado con LDAP 
     * 
    */ 
    private function dameUsuarioJWT(): array {

       if ($this->currentUser->getCurrentUser()!==null) {
          $user = $this->currentUser->getCurrentUser()->getExtraFields()['mail'];
          $password = $this->currentUser->getCurrentUser()->getExtraFields()['password'];
          $roles = $this->currentUser->getCurrentUser()->getExtraFields()['roles'];
       } else {
           $user = $this->params->get('unit_test_user');
           $password = $this->params->get('unit_test_pass');
           $roles = $this->params->get('unit_test_roll');
       }
       return [$user,$password,$roles];
    }

}