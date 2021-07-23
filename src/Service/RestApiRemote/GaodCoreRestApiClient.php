<?php

namespace App\Service\RestApiRemote;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use App\Service\RestApiRemote\RestApiClient;
use App\Service\Processor\Tool\ProcessorTool; 
 /*
 * Descripción: Clase que realiza las llamadas a otras Apirest de 3º como la del gobierno de Aragón  para 
 *              la obtención de datos necesarios 
*/
class GaodCoreRestApiClient
{
    private $client;
    private $params;
    private $autorization;
    private $processorTool;

    public function __construct(RestApiClient $client,
                                ProcessorTool $processorTool,
                                ContainerBagInterface $params){
        $this->client = $client;
        $this->params = $params;
        $this->processorTool = $processorTool;
        $this->autorization = $this->params->get('url_gaodcore_autorization');
    }
    

     /*
     * Descripción: Devuelve una conexion goadcore si es igual que la que comparamos
     *            uri: URL de la BD    
     * */     
    private function GetGaodcoreConectorByUri($uri) : array {
        $connectorSol = array();
        $url = $this->params->get('url_gaodcore_connector') . "/";
        $url = str_replace("autorization",$this->autorization,$url);
        $connectors = $this->client->GetInformation($url);
        foreach($connectors as $connector){       
           if ($connector["uri"]==$uri){
             $connectorSol= $connector;
             break;
           }
        }
        return $connectorSol;
    }


    /*
     * Descripción: Devuelve la validación de gaodcore
     * Parámetros: 
     *            uri: URL de la BD
     *            objectlocation: 
    */     
    private function GetGaodcoreValidator($uri, $object_location_schema, $objectlocation) : array {
        $url = $this->params->get('url_gaodcore_validator');
        $url = str_replace("autorization",$this->autorization,$url);
        $url = "{$url}?uri={$uri}&object_location={$objectlocation}";
        if (!empty($object_location_schema)){
            $url = $url . "&object_location_schema={$object_location_schema}";
        }
        $validator = $this->client->GetInformation($url);
        $validado = (count($validator)>0); 
        return ["validado"=>$validado];
    }

    /*
     * Descripción:  Devuelve el conector de gaodcore 
     * Parámetros: 
     *            uri:                    URL de la BD
     *            object_location_schema: Nombre del esquema de la tabla o vista. de forma completa - Ejemplo postgreSQL: schema.table -> schema
     *            objectlocation:         Nombre de la tabla o vista. de forma completa - Ejemplo postgreSQL: schema.table -> table
     *            enable:                 si se quiere tener activa desde el inicio
     *            name:                   Nombre que se le quiera dar.
     * 
    */    
    private function GetGaodcoreConnector($uri, $object_location_schema, $objectlocation, $enable, $name):array {
        //primero se comprueba si ya existe el connector
        $error = "";
        $params = array();
        $connector = $this->GetGaodcoreConectorByUri($uri);
        $validado = false;
        //si no existe lo creamos
        if (count($connector)==0){
            //aquí validamos antes de crear el connector
            if ($this->GetGaodcoreValidator($uri, $object_location_schema, $objectlocation)["validado"]) {
                //si es valido lo creamos
                $validado = true;
                $url = $this->params->get('url_gaodcore_connector') . "/";
                $url = str_replace("autorization",$this->autorization,$url);
                $params = ["uri"=>$uri, "enabled"=> $enable,"name"=>$name];
                $connector = $this->client->PostCurlInformation($url, $params);
                if (!array_key_exists('id', $connector)) {
                    $error = "Connector no validado: " .  join(" ", $params);
                    $connector = array();
                }
            } else {
                if (count($params)==0){
                    $params = array("uri"=>$uri, "object_location_schema"=>$object_location_schema, "objectlocation"=>$objectlocation);
                }
                $error = "Connector no validado: " .  join(" ", $params);
            }
        }
        return [$connector, $error, $validado];
    }

     /*
     * Descripción:  Devuelve el resource del resource de gaodcore 
     * Parámetros: 
     *            name:                   Nombre que se le quiera dar.
     * 
    */    
    private function GetGaodcoreResourceIdByName($name):array {
        $resourceSol = array();
        $url = $this->params->get('url_gaodcore_resource') . "/";
        $url = str_replace("autorization",$this->autorization,$url);
        $resources = $this->client->GetInformation($url);
        foreach($resources as $resource){       
           if ($resource["name"]==$name) {
             $resourceSol=$resource;
             break;
           }
        }
        return $resourceSol;
    }


   /*
     * Descripción:  Devuelve el recurso de gaodcore
     * Parámetros: 
     *            uri:                    Uri del servicio
     *            object_location_schema: Nombre del esquema de la tabla o vista. de forma completa - Ejemplo postgreSQL: schema.table -> schema
     *            objectlocation:         Nombre de la tabla o vista. de forma completa - Ejemplo postgreSQL: schema.table -> table
     *            enable:                 si se quiere tener activa desde el inicio
     *            name:                   Nombre que se le quiera dar.e
     * 
    */     
    public function GetGaodcoreResource($uri, $object_location_schema, $objectlocation, $enable, $name):array {

        $resource = array();
        $connector = null;
        $params = array();
        $error = "";
        $validado = false;
        $name = $this->processorTool->clean($name);
        $nameconector = "db-" . $name;

        //comienzo revisando si ya existe el resource;
        $resource = $this->GetGaodcoreResourceIdByName($name);
        if (array_key_exists('id', $resource)) {
            $resourceid = $resource['id'];
            $resource = ["resourceid" => $resourceid];
        } else {
            [$connector,$error,$validado] = $this->GetGaodcoreConnector($uri, $object_location_schema, $objectlocation, $enable, $nameconector);
             //si tengo connector por que existe o porque lo he creado despues de validad
             if (count($connector)){
                //validamos esquema y tabla
                $validado = (!$validado) ? $this->GetGaodcoreValidator($uri, $object_location_schema, $objectlocation)["validado"] : $validado;
                if (!$validado) {
                    $error = "Connector no validado: " .  join(" ", $params);
                } else {
                    //si es correcto 
                    $connector_config = $connector['id'];
                    //con el id del conector damos de alta el resource

                    $url = $this->params->get('url_gaodcore_resource') . "/";
                    $url = str_replace("autorization",$this->autorization,$url);
                    $params = ["name"=>$name, 
                            "enabled"=> $enable, 
                            "object_location"=>$objectlocation,
                            "object_location_schema"=>$object_location_schema, 
                            "connector_config"=>$connector_config];
                    $resource = $this->client->PostCurlInformation($url,$params);
                    if (count($resource)>0) {
                        //resource dado de alta
                        if (array_key_exists('id', $resource)) {
                            $resourceid = $resource['id'];
                            $resource = ["resourceid" => $resourceid];
                        } else {
                            $error .= " Error al generar el recurso: " . join(" ", $params);
                            $resource = ["resourceid" => "", "error"=>$error];
                        }
                    } else {
                        $error .= " Error al generar el recurso: " . join(" ", $params);
                        $resource = ["resourceid" => "", "error"=>$error];
                    }
                }
            }
        }
        if (!empty($error)){
            $error = "GA_OD_CORE: " . $error;
        }
        return [$resource, $error];
    }
}