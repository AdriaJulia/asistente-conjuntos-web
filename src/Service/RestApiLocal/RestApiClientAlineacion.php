<?php

namespace App\Service\RestApiLocal; 


use app\Entity\OrigenDatos;
Use App\Service\RestApiLocal\RestApiClient;
use App\Enum\RutasApirestEnum;

use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpFoundation\Session\Session;


/*
 * Descripción: Clase que realiza las llamadas A la API  a la apirest de la funcionalidad alineación datos
 *              La Clase trabaja a trabes de RestApiClient como un adaptador de la misma.
 *              Actual como DAL de la WEB.
*/
class RestApiClientAlineacion
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

 
    public function updateAlineacionEntidadDatos(OrigenDatos $origen,
                                                 Session $session) : array{

        $token = $this->restApiClient->GetTokenSession($session,false);
        $url = $this->params->get('host_restapi') . RutasApirestEnum::ORIGEN_DATOS_POST_ACTUALIZA_ALINEACION;
        $url .= "/{$origen->getId()}";
        return $this->restApiClient->PostContent($url, $origen->toJsonAlineacion(), $token, $session);
    }

    


}