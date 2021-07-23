<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

/**
 * Descripción: Esta clase es parte de la sobreescritura del login para la integración con 
 *              LDAP del cliente. Basicamente lo que hace es llamar a la autenticación Ssobre escrita
 *              si el usuario no está logueado.  
 */
class RequestSubscriber implements EventSubscriberInterface
{
    use TargetPathTrait;

    private $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        if (
            !$event->isMasterRequest()
            || $request->isXmlHttpRequest()
            || 'app_login' === $request->attributes->get('_route')
        ) {
            return;
        }

        $this->saveTargetPath($this->session, 'main', $request->getUri());
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest']
        ];
    }
}