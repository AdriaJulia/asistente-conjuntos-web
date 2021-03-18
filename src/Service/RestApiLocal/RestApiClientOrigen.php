<?php

namespace App\Service\RestApiLocal; 


use app\Entity\OrigenDatos;
Use App\Service\RestApiLocal\RestApiClient;
use App\Enum\RutasApirestEnum;

use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpFoundation\Session\Session;


/*
 * Descripción: Clase que realiza las llamadas A la API  a la apirest de la funcionalidad origen datos
 *              La Clase trabaja a trabes de RestApiClient como un adaptador de la misma.
 *              Actual como DAL de la WEB.
*/
class RestApiClientOrigen
{

    private $params;
    private $restApiClient;
    public function __construct(ContainerBagInterface $params,
                                RestApiClient $restApiClient){

        $this->params = $params;
        $this->restApiClient = $restApiClient;
    }
    
    public function getOrigenDatosId(int $id,
                                     Session $session) : array{
        $token = $this->restApiClient->GetTokenSession($session,false);;
        $url = $this->params->get('host_restapi') . RutasApirestEnum::ORIGEN_DATOS;
        $url .= "/{$id}";
        return $this->restApiClient->GetContent($url, $token, $session);
    }

    public function getDatosFichaId(int $id,
                                    Session $session) : array{
        $token = $this->restApiClient->GetTokenSession($session,false);;
        $url = $this->params->get('host_restapi') . RutasApirestEnum::ORIGEN_DATOS_FICHA;
        $url = $url . "/{$id}";
        return $this->restApiClient->GetContent($url, $token, $session);
    }

    public function testOrigenDatosDataBasedatos(OrigenDatos $origen,
                                                Session $session) : array{

        $token = $this->restApiClient->GetTokenSession($session,false);
        $url = $this->params->get('host_restapi') . RutasApirestEnum::ORIGEN_DATOS_POST_TEST_DATABASE;

        return $this->restApiClient->PostContent($url, $origen->toJsonDatabase(), $token, $session);
    }


    public function createOrigenDatosDataBasedatos(OrigenDatos $origen,
                                                   Session $session) : array{

        $token = $this->restApiClient->GetTokenSession($session,false);
        $url = $this->params->get('host_restapi') . RutasApirestEnum::ORIGEN_DATOS_POST_INSERTA_DATABASE;

        return $this->restApiClient->PostContent($url, $origen->toJsonDatabase(), $token, $session);
    }

    public function updateOrigenDatosDataBasedatos(OrigenDatos $origen,
                                                   Session $session) : array{

        $token = $this->restApiClient->GetTokenSession($session,false);
        $url = $this->params->get('host_restapi') . RutasApirestEnum::ORIGEN_DATOS_POST_ACTUALIZA_DATABASE;
        $url .= "/{$origen->getId()}";
        return $this->restApiClient->PostContent($url, $origen->toJsonDatabase(), $token, $session);
    }


    public function testOrigenDatosData(OrigenDatos $origen,
                                        Session $session) : array{

        $token = $this->restApiClient->GetTokenSession($session,false);
        $url = $this->params->get('host_restapi') . RutasApirestEnum::ORIGEN_DATOS_POST_TEST_DATA;
        return $this->restApiClient->PostContent($url, $origen->toJsonData(), $token, $session);
    }
    
    public function createOrigenDatosData(OrigenDatos $origen,
                                          Session $session) : array{

        $token = $this->restApiClient->GetTokenSession($session,false);
        $url = $this->params->get('host_restapi') . RutasApirestEnum::ORIGEN_DATOS_POST_INSERTA_DATA;

        return $this->restApiClient->PostContent($url, $origen->toJsonData(), $token, $session);
    }

    public function updateOrigenDatosData(OrigenDatos $origen,
                                          Session $session) : array{

        $token = $this->restApiClient->GetTokenSession($session,false);
        $url = $this->params->get('host_restapi') . RutasApirestEnum::ORIGEN_DATOS_POST_ACTUALIZA_DATA;
        $url .= "/{$origen->getId()}";
        return $this->restApiClient->PostContent($url, $origen->toJsonData(), $token, $session);
    }

    public function deleteOrigendatos($id,
                                      Session $session) : array{     
        $token = $this->restApiClient->GetTokenSession($session,false);
        $url = $this->params->get('host_restapi') . RutasApirestEnum::ORIGEN_DATOS;
        $url .= "/{$id}";
        return$this->restApiClient->DeleteContent($url, $token, $session);
    }

}