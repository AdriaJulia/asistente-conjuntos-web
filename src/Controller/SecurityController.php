<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/*
 * Descripción: 
 */
class SecurityController extends AbstractController
{
    /***
     * Descripcion: Acción que realiza el evento login, lanza el formulario y recibe el resultado del proceso de autenticación
     * Parametros: 
     *             request: El objeto request de la llamada
     *             authUtils: Nativo de symfony para gestionar la autenticación
     */
     /**
     * @Route("/login", name="app_login")
     */
    public function loginAction(Request $request, AuthenticationUtils $authUtils)
    {
        // get the login error if there is one
        $error = $authUtils->getLastAuthenticationError();
         
        // last username entered by the user
        $lastUsername = $authUtils->getLastUsername();
        
        return $this->render('security/login.html.twig', array(
            'last_username' => $lastUsername,
            'error'         => $error,
        ));
        
    }

    /***
     * Descripcion: Acción que realiza el evento logout
     */
    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {   
        return $this->render('security/logout.html.twig'); 
    }
}