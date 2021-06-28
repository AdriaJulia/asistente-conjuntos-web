<?php

namespace App\Service\RestApiRemote;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use App\Service\RestApiRemote\RestApiClient;
use App\Service\Processor\Tool\ProcessorTool; 
 /*
 * Descripción: Clase que realiza las llamadas a otras Apirest de 3º como la del gobierno de Aragón  para 
 *              la optencion de datos necesarios 
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
        $url = $this->params->get('url_gaodcore_connector');
        $connectors = $this->client->GetInformation($url,$this->autorization);
        foreach($connectors as $connector){       
           if ($connector["uri"]==$uri){
             $connectorSol= $connector;
             break;
           }
        }
        return $connectorSol;
    }


    /*
     * Descripción: Devuelve la validacion de gaodcore
     * Parametros: 
     *            uri: URL de la BD
     *            objectlocation: 
    */     
    private function GetGaodcoreValidator($uri, $object_location_schema, $objectlocation) : array {
        $url = $this->params->get('url_gaodcore_validator');
        $url = "{$url}?uri={$uri}&object_location={$objectlocation}&object_location_schema={$object_location_schema}";
        $validator = $this->client->GetInformation($url,$this->autorization);
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
    
            /*{
  "id": 9,
  "name": "app20",
  "uri": "postgresql://empleo_publico:KcpHS1V5YE@bev-aodback-01.aragon.local:5432/empleo_publico_aragon",
  "enabled": true,
  "created_at": "2021-05-25T14:01:40.729139+02:00",
  "updated_at": "2021-05-25T14:01:40.729186+02:00"
}
*/
    private function GetGaodcoreConnector($uri, $object_location_schema, $objectlocation, $enable, $name):array {
        //primero se comprueba si ya existe el connexctor
        $error = "";
        $params = array();
        $connector = $this->GetGaodcoreConectorByUri($uri);
        $validado = false;
        //si no existe lo creamos
        if (count($connector)==0){
            //aqui validamos antes de crear el connector
            if ($this->GetGaodcoreValidator($uri, $object_location_schema, $objectlocation)["validado"]) {
                //si es valido lo creamos
                $validado = true;
                $url = $this->params->get('url_gaodcore_connector');
                $params = ["uri"=>$uri, "enabled"=> $enable,"name"=>$name];
                $connector = $this->client->PostInformation($url, $params, $this->autorization);
            } else {
                $error = "Connector no validado: " .  join(" ", $params);
            }
        }
        return [$connector, $error, $validado];
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
    public function GetGaodcoreResource($uri, $object_location_schema, $objectlocation, $enable, $name):array {

        $resource = array();
        $connector = null;
        $params = array();
        $error = "";
        $validado = false;
        $name = $this->processorTool->clean($name);
        $nameconector = "db-" . $name;
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
                $url = $this->params->get('url_gaodcore_resource');
                $params = ["name"=>$name, 
                        "enabled"=> $enable, 
                        "object_location"=>$objectlocation,
                        "object_location_schema"=>$object_location_schema, 
                        "connector_config"=>$connector_config];
                $resource = $this->client->PostInformation($url,$params,$this->autorization);
                if (count($resource)>0) {
                    //resource dado de alta
                    $resourceid = $resource['id'];
                    $resource = ["resourceid" => $resourceid];
                } else {
                    $error .= " Error al generar el recurso: " . join(" ", $params);
                    $resource = ["resourceid" => "", "error"=>$error];
                }
            }
        } 
        return [$resource, $error];
    }

}