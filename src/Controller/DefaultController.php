<?php
namespace App\Controller;


use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Service\RestApiRemote\RestApiClient;

use Symfony\Component\Ldap\Ldap;

class DefaultController extends AbstractController
{


    private  $cli;
    public function __construct(RestApiClient $cli)
    {
        $this->cli =  $cli;
    }



     /**
     * @Route("/adopool/index", name="app_index")
     */
    public function index(): Response
    {
        return new Response("Hola",Response::HTTP_OK);
    }
}
