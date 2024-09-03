<?php

namespace App\Controller;

use App\Entity\User;
use App\Security\JWTTokenManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use App\Security\UserPasswordHasher;

class AuthController extends BaseController
{
    private $passwordHasher;
    private $userProvider;
    private $jwtTokenManager;

    public function __construct(UserPasswordHasher $passwordHasher, UserProviderInterface $userProvider, JWTTokenManager $jwtTokenManager)
    {
        $this->passwordHasher = $passwordHasher;
        $this->userProvider = $userProvider;
        $this->jwtTokenManager = $jwtTokenManager;
    }

    #[Route('/login', name: 'login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;

        if (!$email || !$password) {
            return new JsonResponse(['error' => 'Email and password are required'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $user = $this->userProvider->loadUserByIdentifier($email);
           
            if (!$this->passwordHasher->isPasswordValid($user, $password)) {
                throw new BadCredentialsException('Invalid credentials');
            }
            $token = $this->jwtTokenManager->createToken($user);
          
            return new JsonResponse(['token' => $token], Response::HTTP_OK);
        } catch (BadCredentialsException $e) {
            return new JsonResponse(['error' => 'Invalid email or password'], Response::HTTP_UNAUTHORIZED);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Invalid email or password'], Response::HTTP_UNAUTHORIZED);
        }
    }
}
