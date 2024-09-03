<?php

namespace App\Controller;

use App\Security\JWTTokenManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\HttpFoundation\Response;


/**
 * This class is the base of all controller.
 *
 * @author Divyasree MP <divyasree67@gmail.com>
 */

class BaseController extends AbstractController
{
    protected $tokenManager;

    public function __construct(JWTTokenManager $tokenManager)
    {
        $this->tokenManager = $tokenManager;
    }

    protected function getUserFromToken(Request $request)
    {
       
        $authorizationHeader = $request->headers->get('Authorization');
        if (!$authorizationHeader) {
            throw new AuthenticationException('Authorization header is missing');
        }

        $token = str_replace('Bearer ', '', $authorizationHeader);

        try {
            return $this->tokenManager->validateToken($token);
        } catch (AuthenticationException $e) {
            throw new AuthenticationException('Invalid token');
        }
    }

    protected function handleException(\Exception $e): JsonResponse
    {
        if ($e instanceof NotFoundHttpException) {
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        } elseif ($e instanceof BadRequestHttpException) {
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } else {
            // Handle other exceptions
            return new JsonResponse(['message' => 'An error occurred'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
