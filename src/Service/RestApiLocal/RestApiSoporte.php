<?php

namespace App\Service\RestApiLocal; 



use App\Form\Model\SoporteDto;
Use App\Service\RestApiLocal\RestApiClient;
use App\Enum\RutasApirestEnum;

use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpFoundation\Session\Session;


/*
 * Descripción: Clase que realiza las llamadas A la API  a la apirest en la funcionalidad de soporte ayuda.
 *              La Clase trabaja a trabes de RestApiClient como un adaptador de la misma.
 *              Actual como DAL de la WEB. 
*/
class RestApiSoporte
{

    private $params;
    private $restApiClient;
    public function __construct(ContainerBagInterface $params,
                                RestApiClient $restApiClient){

        $this->params = $params;
        $this->restApiClient = $restApiClient;
    }

    public function envia(SoporteDto $soporte,
                          Session $session) : array {
        $token = $this->restApiClient->GetTokenSession($session,false);
        $url = $this->params->get('host_restapi') . RutasApirestEnum::SOPORTE_USUARIOS;
        return $this->restApiClient->PostContent($url, $soporte->toJsonData(), $token, $session);
    }

}