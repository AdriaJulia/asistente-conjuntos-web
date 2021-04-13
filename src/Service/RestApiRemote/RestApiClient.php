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