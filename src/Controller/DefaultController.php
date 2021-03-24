<?php
namespace App\Controller;


use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/*
 * Descripción: para que cargue la pagina principal
 */
class DefaultController extends AbstractController
{

     /**
     * @Route("/")
     */
    public function indexA(): Response
    {
        return $this->redirect("/asistentecamposdatos");
    }
}
