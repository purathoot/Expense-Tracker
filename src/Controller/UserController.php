<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Security\UserPasswordHasher;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Security\JWTTokenManager;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class UserController extends BaseController
{
    private $entityManager;
    private $passwordHasher;
    private $validator;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserPasswordHasher $passwordHasher,
        ValidatorInterface $validator,
        JWTTokenManager $tokenManager
    )
    {
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
        $this->validator = $validator;
        parent::__construct($tokenManager);
    }

    #[Route('register', name: 'create_user', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            if (!isset($data['name'], $data['email'], $data['password'])) {
                return new JsonResponse(['error' => 'Invalid input data'], Response::HTTP_BAD_REQUEST);
            }

            $user = new User();
            $user->setName($data['name']);
            $user->setEmail($data['email']);
            $plainPassword = $data['password'];
            $encodedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
            $user->setPassword($encodedPassword);

            $errors = $this->validator->validate($user);
            if (count($errors) > 0) {
                $errorsArray = [];
                foreach ($errors as $error) {
                    $errorsArray[] = $error->getMessage();
                }
                return new JsonResponse(['errors' => $errorsArray], Response::HTTP_BAD_REQUEST);
            }

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            return new JsonResponse(['status' => 'User created'], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    #[Route('/api/users', name: 'list_users', methods: ['GET'])]
    public function get(): JsonResponse
    {
        try {
            $userRepository = $this->entityManager->getRepository(User::class);
            $users = $userRepository->findAll();

            $userData = [];
            foreach ($users as $user) {
                $userData[] = [
                    'id' => $user->getId(),
                    'name' => $user->getName(),
                    'email' => $user->getEmail(),
                    'createdAt' => $user->getCreatedAt()->format('Y-m-d H:i:s'),
                    'updatedAt' => $user->getUpdatedAt()->format('Y-m-d H:i:s'),
                ];
            }

            return new JsonResponse($userData, Response::HTTP_OK);

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    #[Route('/api/user/details', name: 'get_user_details', methods: ['GET'])]
    public function getUserDetails(Request $request): JsonResponse
    {
        try {
            $user = $this->getUserFromToken($request);

            // Return user details
            return new JsonResponse([
                'id' => $user->getId(),
                'name' => $user->getName(),
                'email' => $user->getEmail(),
            ], Response::HTTP_OK);

        } catch (AuthenticationException $e) {
            return new JsonResponse(['error' => 'Invalid token'], Response::HTTP_UNAUTHORIZED);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
}
