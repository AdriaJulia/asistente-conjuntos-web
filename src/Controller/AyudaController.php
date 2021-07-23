<?php
namespace App\Controller;

use App\Enum\RutasAyudaEnum;
use App\Service\Controller\ToolController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

use App\Service\Processor\SoporteFormProcessor;

use Psr\Log\LoggerInterface;

/*
 * Descripción: Es el controlador de la ayuda y del envío mail a soporte
 */
class AyudaController extends AbstractController
{
    private $ClassBody = "ayuda comunidad usuarioConectado";
    private $urlAyuda = "";
    private $urlSoporte = "";
    private $urlCrear = "";
    private $urlMenu = "";


    /***
     * Descripción: Es el action de la ayuda
     * Parámetros:
     *             pagina:                    id de la página de ayuda que se va a visualiza
     *             toolController:            clase helper para procesoso comunes de los controladores
     *             request:                   El objeto request de la llamada
     */
    /**
    * @Route("/asistentecamposdatos/ayuda/{pagina}", name="asistentecamposdatos_ayuda_index")
    */
   public function indexAction(string $pagina="distribucion",
                               ToolController $toolController=null,
                               Request $request=null) {

       //el class de body en este controlador no es siempre el mismo     
      $this->ClassBody = "ayuda comunidad usuarioConectado";
      //tomo las urls del menu superior 
      [$this->urlAyuda, $this->urlSoporte, $this->urlCrear, $this->urlMenu] =  $toolController->getAyudaCrearMenu($_SERVER,RutasAyudaEnum::DESCRIPCION_DISTRIBUCION,$this->getUser());
      $locationAnterior = $request->getRequestUri();
      return $this->render('ayuda.html.twig',[
                              'ClassBody' => $this->ClassBody,
                              'urlAyuda' => $this->urlAyuda,
                              'urlSoporte' => $this->urlSoporte,
                              'urlCrear' => $this->urlCrear,
                              'urlMenu' =>  $this->urlMenu,
                              'locationAnterior' => $locationAnterior
                           ]);
    }


    /***
     * Descripción: Action que muestra el formulario  de soporte y envía el correo
     * Parámetros:
     *             soporteFormProcessor:      proceso back que envía el coreo 
     *             toolController:            clase helper  para procesoso comunes de los controladores
     *             request:                   El objeto request de la llamada
     */
    /**
    * @Route("/asistentecamposdatos/soporte", name="asistentecamposdatos_ayuda_soporte")
    */
     public function soporteAction(SoporteFormProcessor $soporteFormProcessor,
                                   ToolController $toolController,
                                   LoggerInterface $logger,
                                   Request $request) {

        //el class de body en este controlador no es siempre es el mismo                                  
        $this->ClassBody = "asistente comunidad usuarioConectado";
        //tomo las urls del menu superior 
        [$this->urlAyuda, $this->urlSoporte, $this->urlCrear, $this->urlMenu] = $toolController->getAyudaCrearMenu($_SERVER,RutasAyudaEnum::DESCRIPCION_DISTRIBUCION,$this->getUser());
        $locationAnterior = $request->getRequestUri();
    
        $error = "";
        [$form, $soporte,$error] = ($soporteFormProcessor)($request);
        if (!empty($error)) {
            $logger->error($error);
        }
        //si es correcto saco la confirmación
        if ($form->isSubmitted() && $form->isValid()) {
            //muestro elm mensaje de correo enviado
            return $this->render('soporte.html.twig', [
                'ClassBody' => $this->ClassBody,
                'locationAnterior' =>  $locationAnterior,
                'soporte_form' => $form->createView(),
                'enviado' => 'si',
                'urlAyuda' => $this->urlAyuda,
                'urlSoporte' => $this->urlSoporte,
                'urlMenu' =>  $this->urlMenu,
                'urlCrear' => $this->urlCrear,
                'errorProceso' => $error
            ]);
        } else {
            //muestro el formulario 
            return $this->render('soporte.html.twig', [
                'ClassBody' => $this->ClassBody,
                'locationAnterior' =>  $locationAnterior,
                'enviado' => '',
                'urlAyuda' => $this->urlAyuda,
                'urlSoporte' => $this->urlSoporte,
                'urlMenu' =>  $this->urlMenu,
                'urlCrear' => $this->urlCrear,
                'soporte_form' => $form->createView(),
                'errorProceso' => $error
            ]);
        }
    }

}