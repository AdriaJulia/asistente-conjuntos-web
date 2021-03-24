<?php

namespace App\Service\RestApiLocal;

use App\Service\CurrentUser;
use app\Entity\DescripcionDatos;
Use App\Service\RestApiLocal\RestApiClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use App\Enum\RutasApirestEnum;

use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpFoundation\Session\Session;


/*
 * Descripción: Clase que realiza las llamadas A la API  a la apirest de la funcionalidad descripcion datos
 *              La Clase trabaja a trabes de RestApiClient como un adaptador de la misma.
 *              Actual como DAL de la WEB.
*/
class RestApiClientDescripcion
{

    private $params;
    private $restApiClient;
    public function __construct(ContainerBagInterface $params,
                                RestApiClient $restApiClient){

        $this->params = $params;
        $this->restApiClient = $restApiClient;
    }

    
    public function getDescripciondatos(int $numeropagina, 
                                        int $tamanopagina, 
                                        Session $session) : array {     
        $token = $this->restApiClient->GetTokenSession($session,false);
        $url = $this->params->get('host_restapi') . RutasApirestEnum::DESCRIPCION_DATOS;
        $url .= "?numeropagina={$numeropagina}&tamanopagina={$tamanopagina}";
        return $this->restApiClient->GetContent($url, $token, $session);
    }

    public function getDescripciondatosId(int $id,
                                          Session $session) : array {  
        $token = $this->restApiClient->GetTokenSession($session,false); 
        $url = $this->params->get('host_restapi') . RutasApirestEnum::DESCRIPCION_DATOS;
        $url .= "/{$id}";
        return $this->restApiClient->GetContent($url, $token, $session);
    }

    public function createDescripciondatos(DescripcionDatos $descripcion,
                                           Session $session) : array{

        $token = $this->restApiClient->GetTokenSession($session,false);         
        $url = $this->params->get('host_restapi') . RutasApirestEnum::DESCRIPCION_DATOS;
        $jsondata = $descripcion->toJsonCreate();
        return $this->restApiClient->PostContent($url, $jsondata , $token, $session);
    }

    public function updateDescripciondatos(DescripcionDatos $descripcion,
                                           Session $session) : array{

        $token = $this->restApiClient->GetTokenSession($session,false);
        $url = $this->params->get('host_restapi') . RutasApirestEnum::DESCRIPCION_DATOS;
        $url .= "/{$descripcion->getId()}";
        return $this->restApiClient->PostContent($url, $descripcion->toJsonUpdate(), $token, $session);
    }

    public function updateWorkflowDescripciondatos(DescripcionDatos $descripcion,
                                                   Session $session) : array{

        $token = $this->restApiClient->GetTokenSession($session,false);
        $url = $this->params->get('host_restapi') . RutasApirestEnum::DESCRIPCION_DATOS_POST_WORKFLOW;
        $url .= "/{$descripcion->getId()}";
        return $this->restApiClient->PostContent($url, $descripcion->toJsonWorkflow(), $token, $session);
    }

    public function deleteDescripciondatos(int $id,
                                           Session $session) : array{
        $token = $this->restApiClient->GetTokenSession($session,false);
        $url = $this->params->get('host_restapi') . RutasApirestEnum::DESCRIPCION_DATOS;
        $url .= "/{$id}";
        return $this->restApiClient->DeleteContent($url, $token, $session);
    }
}