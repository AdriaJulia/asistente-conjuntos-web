<?php
namespace App\Controller;

use App\Enum\RutasAyudaEnum;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;


use Symfony\Component\Ldap\Ldap;

class AyudaController extends AbstractController
{
    private $ClassBody = "ayuda comunidad usuarioConectado";
    private $urlAyuda = "";
    private $urlCrear = "";

    /**
    * @Route("/asistentecamposdatos/ayuda/{pagina}", requirements={"pagina"="\d+"}, name="asistentecamposdatos_ayuda_index")
    */
   public function indexAction(int $pagina=1,
                               Request $request) {
      $locationAnterior = $request->getRequestUri();
      $this->urlCrear =  $this->generateUrl("insert_asistentecamposdatos_paso1");
      $this->urlAyuda = $this->generateUrl('asistentecamposdatos_ayuda_index',["pagina"=>RutasAyudaEnum::DESCRIPCION_PASO11]);
      $actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
      $this->urlAyuda .= "?locationAnterior={$actual_link}";
      return $this->render('ayuda.html.twig',[
                              'ClassBody' => $this->ClassBody,
                              'urlAyuda' => $this->urlAyuda,
                              'urlCrear' => $this->urlCrear,
                              'locationAnterior' => $locationAnterior
                           ]);
    }
}