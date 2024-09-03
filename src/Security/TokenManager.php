<?php

namespace App\Security;

use Firebase\JWT\JWT;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;

class TokenManager
{
    private $secretKey;
    private $entityManager;

    public function __construct(string $secretKey, EntityManagerInterface $entityManager)
    {
        $this->secretKey = $secretKey;
        $this->entityManager = $entityManager;
    }

    public function validateToken(string $token): ?User
    {
        try {
            $decodedToken = JWT::decode($token, $this->secretKey, ['HS256']);
            $userId = $decodedToken->user_id;

            $user = $this->entityManager->getRepository(User::class)->find($userId);

            if (!$user) {
                throw new AuthenticationException('User not found');
            }

            return $user;
        } catch (\Exception $e) {
            throw new AuthenticationException('Invalid token');
        }
    }
}
