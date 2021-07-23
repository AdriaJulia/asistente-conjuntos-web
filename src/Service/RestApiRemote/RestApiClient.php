<?php

namespace App\Service\RestApiRemote;

use Exception;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpClient\Exception\TransportException;
use Psr\Log\LoggerInterface;

 /*
 * Descripción: Clase que realiza las llamadas a otras Apirest de 3º como la del gobierno de Aragón  para 
 *              la obtención de datos necesarios 
*/
class RestApiClient
{
    private $client;
    private $params;
    private $logger;
    public function __construct(HttpClientInterface $client,
                                LoggerInterface $logger,
                                ContainerBagInterface $params){
        $this->client = $client;
        $this->params = $params;
        $this->logger = $logger;
    }
    
    /*
     * Descripción: Devuelve las organismos públicos del paso 2
     * 
     * Parámetros: ontologia principal
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
     * Descripción: Función genérica para llamadas get apirest de 3
     * 
     * Parámetros: 
     *              ruta: ruta get de los datos que se desea obtener
     *              autorizacion: autorización de la llamada
    */  

    public function GetInformation($ruta): array {
        $content = array();

        $headers =  [
            'content-type' => 'application/json',
            'accept' => 'application/json'
        ];
        try{
            $response = $this->client->request('GET', $ruta, [
                'timeout' => 2.5,
                'headers' =>$headers,
            ]);
            
            $statusCode = $response->getStatusCode();
            // $statusCode = 200
            if(($statusCode>=200) && ($statusCode<300)) {
                $contentType = $response->getHeaders()['content-type'][0];
                // $contentType = 'application/json'
                $content = $response->getContent();
                // $content = '{"id":521583, "name":"symfony-docs", ...}'
                $content = $response->toArray();
                // $content = ['id' => 521583, 'name' => 'symfony-docs', ...]
            } else {
                $error = "Website GetInformation statusCode: {$statusCode}: - Ruta: {$ruta}";
                $this->logger->error($error);
            }
        } catch(TransportException $ex){
            $this->logger->error($ex->getMessage());
        }
        // $content = ['id' => 521583, 'name' => 'symfony-docs', ...]
        return $content;
    }


    /*
     * Descripción: Función genérica para llamadas get apirest de 3º
     * 
     * Parámetros: 
     *               ruta: ruta get de los datos que se desea obtener
     *               parameters: parametros de la llamada
     *               autorizacion: autorización de la llamada
    */  

    public function PostInformation($ruta,$parameters): array {
        $content = array();

        $headers =  [
            'content-type' => 'application/json',
            'accept' => 'application/json'
        ];

        try{

            $response = $this->client->request('POST', $ruta, [
                'timeout' => 2.5,
                'headers' => $headers,
                'body' => $parameters
            ]);  
        
            $statusCode = $response->getStatusCode();
            // $statusCode = 200
            if(($statusCode>=200) && ($statusCode<300)) {
                $contentType = $response->getHeaders()['content-type'][0];
                // $contentType = 'application/json'
                $content = $response->getContent();
                // $content = '{"id":521583, "name":"symfony-docs", ...}'
                $content = $response->toArray();
                // $content = ['id' => 521583, 'name' => 'symfony-docs', ...]
            }  else {
                $paramErro= "";
                foreach($parameters as $key=>$value){
                    $par = "[{$key}]={$value} \n";
                    $paramErro = $paramErro + $par;
                }
                $error = "Website PostInformation statusCode: {$statusCode}: - Ruta: {$ruta} - Parametros : {$paramErro}";
                $this->logger->error($error);
            }
        } catch(TransportException $ex){
            $this->logger->error($ex->getMessage());
        }
        return $content;
    }


    /*
     * Descripción: Función genérica para llamadas get apirest de 3º
     * 
     * Parámetros: 
     *               ruta: ruta get de los datos que se desea obtener
     *               parameters: parametros de la llamada
    */  

    public function PostCurlInformation($ruta,$parameters): array {
        $content = array();

        try{
            $curl = curl_init($ruta);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($parameters));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($curl);
            curl_close($curl);
            $content = (array) json_decode($response);

        } catch(TransportException $ex){
            $this->logger->error($ex->getMessage());
        }
        return $content;
    }

}