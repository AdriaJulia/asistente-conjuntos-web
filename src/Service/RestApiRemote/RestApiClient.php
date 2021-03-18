<?php

namespace App\Service\RestApiRemote;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

use Symfony\Component\HttpFoundation\Session\Session;
/**
 * Clase que realiza las llamadas a otras Apirest de 3º como la del gobierno de Aragón  para 
 * la optencion de datos necesarios 
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
    
    public function GetOntologias():array {
       $Ontologiasview = array();
       $Ontologiasview["categorization_1"] = "http://opendata.aragon.es/def/ei2a/categorization#1";
       $Ontologiasview["categorization_2"] = "http://opendata.aragon.es/def/ei2a/categorization#2";
       $Ontologiasview["categorization_3"] = "http://opendata.aragon.es/def/ei2a/categorization#3";
       $organismosview["categorization_4"] = "http://opendata.aragon.es/def/ei2a/categorization#4";
       ksort($organismosview);
       return $Ontologiasview;
    }

    public function GetOntologia($ontologia):array {
        $Ontologiasview = array();
        if ($ontologia=="http://opendata.aragon.es/def/ei2a/categorization#1") {
            $Ontologiasview["entidad_11"] = "http://opendata.aragon.es/def/ei2a/categorization#1";
            $Ontologiasview["entidad_12"] = "http://opendata.aragon.es/def/ei2a/categorization#12";
            $Ontologiasview["entidad_13"] = "http://opendata.aragon.es/def/ei2a/categorization#13";
            $organismosview["entidad_14"] = "http://opendata.aragon.es/def/ei2a/categorization#14";
        } else if ($ontologia=="http://opendata.aragon.es/def/ei2a/categorization#2") {
            $Ontologiasview["entidad_21"] = "http://opendata.aragon.es/def/ei2a/categorization#2";
            $Ontologiasview["entidad_22"] = "http://opendata.aragon.es/def/ei2a/categorization#22";
            $Ontologiasview["entidad_23"] = "http://opendata.aragon.es/def/ei2a/categorization#23";
            $organismosview["entidad_24"] = "http://opendata.aragon.es/def/ei2a/categorization#24";
        } else if ($ontologia=="http://opendata.aragon.es/def/ei2a/categorization#3") {
            $Ontologiasview["entidad_31"] = "http://opendata.aragon.es/def/ei2a/categorization#3";
            $Ontologiasview["entidad_32"] = "http://opendata.aragon.es/def/ei2a/categorization#32";
            $Ontologiasview["entidad_33"] = "http://opendata.aragon.es/def/ei2a/categorization#33";
            $organismosview["entidad_34"] = "http://opendata.aragon.es/def/ei2a/categorization#34";
        } else {
            $Ontologiasview["entidad_41"] = "http://opendata.aragon.es/def/ei2a/categorization#4";
            $Ontologiasview["entidad_42"] = "http://opendata.aragon.es/def/ei2a/categorization#42";
            $Ontologiasview["entidad_43"] = "http://opendata.aragon.es/def/ei2a/categorization#43";
            $organismosview["entidad_44"] = "http://opendata.aragon.es/def/ei2a/categorization#44";
        }
        ksort($organismosview);
        return $Ontologiasview;
     }

        
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