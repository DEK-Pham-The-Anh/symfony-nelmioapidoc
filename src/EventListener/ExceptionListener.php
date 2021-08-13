<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class ExceptionListener
{
    public function onKernelException(ExceptionEvent $ev)
    { 
        $ex = $ev->getThrowable();
        $exCode = $ex->getCode();
        $exMsg = $ex->getMessage();
        
        switch ($exCode) {
            case 401:
                $message = json_encode([
                    'message' => 'Authorization failed. ' . $exMsg,
                    'statusCode' => $exCode
                ]);

                break;
            
            default:
                $message = json_encode([
                    'message' => $exMsg,
                    'statusCode' => $exCode
                ]);
            
                break;
        }

        $res = new Response();
        $res->setContent($message);

        if ($ex instanceof HttpExceptionInterface) {
            $res->setStatusCode($ex->getStatusCode());
            $res->headers->replace($ex->getHeaders());
        } else {
            $res->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $res->headers->set('Content-Type', 'application/json');
        $ev->setResponse($res);
    }
}