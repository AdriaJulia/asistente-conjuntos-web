<?php

namespace App\Service\RestApiRemote;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

use Symfony\Component\HttpFoundation\Session\Session;

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
     * Descripción: Devuelve las ontologias principales del paso3
    */  
    public function GetOntologias():array {
       $Ontologiasview = array();
       $Ontologiasview["Hoteles"] = "http://opendata.aragon.es/def/ei2a/categorization#hoteles";
       $Ontologiasview["Museos"] = "http://opendata.aragon.es/def/ei2a/categorization#museos";
       $Ontologiasview["Ayuntamientos"] = "http://opendata.aragon.es/def/ei2a/categorization#ayuntamientos";
       $organismosview["Paradores"] = "http://opendata.aragon.es/def/ei2a/categorization#paradores";
       ksort($organismosview);
       return $Ontologiasview;
    }

    /*
     * Descripción: Devuelve las ontologias segundarías principales del paso3
     * 
     * Parametros: ontologia principal  
    */ 
    public function GetOntologia($ontologia):array {
        $Ontologiasview = array();
        if ($ontologia=="http://opendata.aragon.es/def/ei2a/categorization#hoteles") {
            $Ontologiasview["localidad"] = "http://opendata.aragon.es/def/ei2a/categorization#localidad";
            $Ontologiasview["direccion"] = "http://opendata.aragon.es/def/ei2a/categorization#direccion";
            $Ontologiasview["telefono"] = "http://opendata.aragon.es/def/ei2a/categorization#telefono";
            $organismosview["reservas"] = "http://opendata.aragon.es/def/ei2a/categorization#reservas";
        } else if ($ontologia=="http://opendata.aragon.es/def/ei2a/categorization#museos") {
            $Ontologiasview["localidad"] = "http://opendata.aragon.es/def/ei2a/categorization#localidad";
            $Ontologiasview["tematica"] = "http://opendata.aragon.es/def/ei2a/categorization#tematica";
            $Ontologiasview["horarios"] = "http://opendata.aragon.es/def/ei2a/categorization#horarios";
            $organismosview["visitas"] = "http://opendata.aragon.es/def/ei2a/categorization#visitas";
        } else if ($ontologia=="http://opendata.aragon.es/def/ei2a/categorization#ayuntamientos") {
            $Ontologiasview["localidad"] = "http://opendata.aragon.es/def/ei2a/categorization#localidad";
            $Ontologiasview["alcalde"] = "http://opendata.aragon.es/def/ei2a/categorization#alcalde";
            $Ontologiasview["numero_concejales"] = "http://opendata.aragon.es/def/ei2a/categorization#numero_concejales";
            $organismosview["partido_politico"] = "http://opendata.aragon.es/def/ei2a/categorization#partido_politico";
        } else {
            $Ontologiasview["ubicacion"] = "http://opendata.aragon.es/def/ei2a/categorization#ubicacion";
            $Ontologiasview["capacidad"] = "http://opendata.aragon.es/def/ei2a/categorization#capacidad";
            $Ontologiasview["parking"] = "http://opendata.aragon.es/def/ei2a/categorization#parking";
            $organismosview["zona_verde"] = "http://opendata.aragon.es/def/ei2a/categorization#zona_verde";
        }
        ksort($organismosview);
        return $Ontologiasview;
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
            $organismosview["{$org[5]}"] = $org[5];
          }
        }
        ksort($organismosview);
        return $organismosview;
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

}