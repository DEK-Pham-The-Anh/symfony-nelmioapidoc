<?php

namespace App\EventSubscriber;

use App\Controller\BearerTokenAuthenticatedController;
use App\Controller\CookieTokenAuthenticatedController;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

use Nowakowskir\JWT\JWT;
use Nowakowskir\JWT\TokenDecoded;
use Nowakowskir\JWT\TokenEncoded;


class TokenSubscriber implements EventSubscriberInterface
{
    private $secret;
    private $restrictedRoutes;
    private $allowOrigins;
    private $wwwAuthenticateBearerToken;
    private $wwwAuthenticateCookieToken;

    public function __construct($secret, $restrictedRoutes, $allowOrigins)
    {
        $this->secret = $secret;
        $this->restrictedRoutes = $restrictedRoutes;
        $this->allowOrigins = explode(', ', str_replace(['[', ']'], '', $allowOrigins));
        $this->wwwAuthenticateBearerToken = 'Bearer realm="Access to JWT protected API"';
        $this->wwwAuthenticateCookieToken = 'cookieAuth realm="Access to JWT protected API"';
    }

    public function isAuthorized($token) 
    {
        try {
            $tokenEncoded = new TokenEncoded($token);
    
            return $tokenEncoded->validate($this->secret, JWT::ALGORITHM_HS256);
        } catch (\Throwable $th) { 
            return false;
        }

        return true;
    }

    public function originVerified($httpReferer) {
        try {
            $httpReferer = parse_url($httpReferer);
            $httpReferer = $httpReferer['scheme'] . '://' . $httpReferer['host']; 
        } catch (\Throwable $th) {
            return false;
        }

        if ($httpReferer == '' || !in_array($httpReferer, $this->allowOrigins)) { 
            return false;
        } 

        return true;
    }

    public function onKernelController(ControllerEvent $event)
    {
        $route = $event->getRequest()->attributes->get('_route');

        $controller = $event->getController();

        if (is_array($controller)) {
            $controller = $controller[0];
        }

        if ($controller instanceof CookieTokenAuthenticatedController && in_array($route, $this->restrictedRoutes)) { 

            if (!$this->originVerified($event->getRequest()->server->get('HTTP_REFERER'))) {
                throw new UnauthorizedHttpException($this->wwwAuthenticateCookieToken, 'Bad request!', null, 401);
            } 

            try {
                $token = $event->getRequest()->cookies->get('vue_api_token');
            } catch (\Throwable $th) {
                throw new UnauthorizedHttpException($this->wwwAuthenticateCookieToken, 'This action needs a cookie token!', null, 401);
            }

            if (!($this->isAuthorized($token))) {
                throw new UnauthorizedHttpException($this->wwwAuthenticateCookieToken, 'Your token is invalid!', null, 401);
            }

            $event->getRequest()->attributes->set('token_passed', $token);

        } elseif ($controller instanceof BearerTokenAuthenticatedController && in_array($route, $this->restrictedRoutes)) {

            if (!$this->originVerified($event->getRequest()->server->get('HTTP_REFERER'))) {
                throw new UnauthorizedHttpException($this->wwwAuthenticateBearerToken, 'Bad request!', null, 401);
            } 

            $token = str_replace('Bearer ', '', $event->getRequest()->headers->get('Authorization'));
            
            if ($token == '' && $token == NULL) {
                throw new UnauthorizedHttpException($this->wwwAuthenticateBearerToken, 'This action needs a bearer token!', null, 401);
            }

            if (!($this->isAuthorized($token))) { 
                throw new UnauthorizedHttpException($this->wwwAuthenticateBearerToken, 'Your token is invalid!', null, 401);
            }

            $event->getRequest()->attributes->set('token_passed', $token);

        }
    }

    public function onKernelResponse(ResponseEvent $event)
    {
        if (!$token = $event->getRequest()->attributes->get('token_passed')) {
            return;
        }

        $response = $event->getResponse();
        $hash = sha1($response->getContent().$token);      
        $response->headers->set('X-CONTENT-HASH', 'MY_TOKEN_HASH===' . $token);
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
            KernelEvents::RESPONSE => 'onKernelResponse',
        ];
    }
}