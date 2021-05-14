<?php

namespace App\Service\RestApiRemote;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;


 /*
 * Descripción: Clase que realiza las llamadas a otras Apirest de 3º como la del gobierno de Aragón  para 
 *              la optencion de datos necesarios 
*/
class RestApiClient
{
    private $client;
    private $params;

    public function __construct(HttpClientInterface $client,
                                ContainerBagInterface $params){
        $this->client = $client;
        $this->params = $params;
    }
    
    /*
     * Descripción: Devuelve las organismos públicos del paso 1.2
     * 
     * Parametros: ontologia principal
    */     
    public function GetOrganismosPublicos():array {
        $organismosview = array();
        $url = $this->params->get('url_organismos');
        $organismos = $this->GetInformation($url);
        if (count($organismos)>0){
          array_shift($organismos);
          foreach($organismos as $org) {
            if (strpos($org[5],"Dirección General")===0){
                $organismosview["{$org[5]}"] = $org[5];
            }
          }
        }
        ksort($organismosview);
        return $organismosview;
    }

    /*
     * Descripción: Devuelve la validacion de gaodcore
     * Parametros: 
     *            uri: URL de la BD
     *            objectlocation: 
    */     
    private function GetGaodcoreValidator($uri, $objectlocation) : array {
        $url = $this->params->get('url_gaodcore_validator');
        $url = "{$url}?uri={$uri}&object_location={$objectlocation}";
        $validator = $this->GetInformation($url);
        $validado = (count($validator)>0); 
        return ["validado"=>$validado];
    }

    /*
     * Descripción:  Devuelve el conector de gaodcore 
     * Parametros: 
     *            uri: URL de la BD
     *            objectlocation: Nombre de la tabla o vista. de forma completa - Ejemplo postgreSQL: schema.table
     *            enable: si se quiere tener activa desde el inicio
     *            name: Nombre que se le quiera dar.
     * 
    */     
    private function GetGaodcoreConnector($uri, $objectlocation, $enable, $name):array {
     
        $conector = array();
        if ($this->GetGaodcoreValidator($uri, $objectlocation)["validado"]) {
            $url = $this->params->get('url_gaodcore_connector');
            $params = ["uri"=>$uri, "enabled"=> $enable,"name"=>$name];
            $conector = $this->PostInformation($url,$params);
        }
        return $conector;
    }

   /*
     * Descripción:  Devuelve el recurso de gaodcore
     * Parametros: 
     *            name: Nombre que se le quiera dar.
     *            connector_config: models.ForeignKey ConnectorConfig, on_delete=models.CASCADE Id del conector. Lo obtienes del paso anterior.
     *            enable:True si se quiere tener activa desde el inicio
     *            objectlocation: Nombre de la tabla o vista. de forma completa - Ejemplo postgreSQL: schema.table
     * 
    */     
    public function GetGaodcoreResource($uri, $objectlocation, $enable, $name):array {

        $resource = array();
        $connector = $this->GetGaodcoreConnector($uri, $objectlocation, $enable, $name);
        if (count($connector)){
            $connector_config = $connector['connectorConfig']['id'];
            $url = $this->params->get('url_gaodcore_resource');
            $params = ["name"=>$name, "enabled"=> $enable,"object_location"=>$objectlocation,"connector_config"=>$connector_config];
            $resource = $this->PostInformation($url,$params);
            $resourceid = $resource['resourceConfig']['id'];
            $resource = ["resourceid" => $resourceid];
        } 
        return $resource;
    }

    /*
     * Descripción: Funcion generica para llamadas get apirest de 3º
     * 
     * Parametros: ruta: ruta get de los datos que se desea obtener
    */  

    private function GetInformation($ruta): array {
        $content = array();

        $response = $this->client->request('GET', $ruta, [
            'headers' => [
                'content-type' => 'application/json',
                'accept' => 'application/json'
            ],
        ]);
        
        $statusCode = $response->getStatusCode();
        // $statusCode = 200
        $contentType = $response->getHeaders()['content-type'][0];
        // $contentType = 'application/json'
        $content = $response->getContent();
        // $content = '{"id":521583, "name":"symfony-docs", ...}'
        $content = $response->toArray();
        // $content = ['id' => 521583, 'name' => 'symfony-docs', ...]

        return $content;
    }


        /*
     * Descripción: Funcion generica para llamadas get apirest de 3º
     * 
     * Parametros: ruta: ruta get de los datos que se desea obtener
    */  

    private function PostInformation($ruta,$parameters): array {
        $content = array();

        $response = $this->client->request('POST', $ruta, [
            'headers' => [
                'content-type' => 'application/x-www-form-urlencoded',
                'accept' => 'application/json'
            ],
            'body' => $parameters
        ]);
        
        $statusCode = $response->getStatusCode();
        // $statusCode = 200
        $contentType = $response->getHeaders()['content-type'][0];
        // $contentType = 'application/json'
        $content = $response->getContent();
        // $content = '{"id":521583, "name":"symfony-docs", ...}'
        $content = $response->toArray();
        // $content = ['id' => 521583, 'name' => 'symfony-docs', ...]

        return $content;
    }

}