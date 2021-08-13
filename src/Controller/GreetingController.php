<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;
use Symfony\Component\Routing\Annotation\Route;

use App\Controller\CookieTokenAuthenticatedController;

class GreetingController extends AbstractController implements CookieTokenAuthenticatedController
{
    /**
     * Get a greeting, needs a cookie token authorization.
     *
     * This call returns a greeting based on your language of choice. 
     * Cookie-based authorization cannot be tested in Swagger UI
     *  
     *
     * @Route("/api/greeting/{lang}", name="getGreeting", methods={"GET"})
     * @OA\Response(
     *     response=200,
     *     description="Returns a greeting",
     *     @OA\Schema(type="object"),
     * )
     * @OA\Response(
     *     response=401,
     *     description="Authorization failed",
     *     @OA\Schema(type="object"),
     * )
     * @OA\Tag(name="Greeting")
     * @Security(name="cookieAuth")
     */
    public function getGreeting($lang): JsonResponse
    {
        if (gettype($lang) != 'string') {
            throw new NotFoundHttpException('Expecting a string.');
        }

        if ($lang == 'en') {
            return new JsonResponse(['greeting' => 'Hello!'], Response::HTTP_OK);
        } 

        return new JsonResponse(['greeting' => 'Ahoj!'], Response::HTTP_OK);
    }
}