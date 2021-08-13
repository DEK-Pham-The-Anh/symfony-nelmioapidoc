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

use App\Controller\BearerTokenAuthenticatedController;

class GreetingTwoController extends AbstractController implements BearerTokenAuthenticatedController
{
    /**
     * Get a greeting, needs a bearer token authorization.
     *
     * This call returns a greeting based on your language of choice. 
     * Bearer-based authorization can be tested in Swagger UI, 
     * set this Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIn0.I8LpmsjyaG_FsQX_8_Mg2rR561aGkoBOtY0-sHrMfn4
     *  
     *
     * @Route("/api/greetingtwo/{lang}", name="getGreetingTwo", methods={"GET"})
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
     * @Security(name="Bearer")
     */
    public function getGreetingTwo($lang): JsonResponse
    {
        if (gettype($lang) != 'string') {
            // throw new NotFoundHttpException('Expecting a string.');
            return new JsonResponse(['message' => 'Expecting a string.'], Response::HTTP_NOT_FOUND);
        }

        if ($lang == 'en') {
            return new JsonResponse(['greeting' => 'Hello!'], Response::HTTP_OK);
        } 

        return new JsonResponse(['greeting' => 'Ahoj!'], Response::HTTP_OK);
    }
}