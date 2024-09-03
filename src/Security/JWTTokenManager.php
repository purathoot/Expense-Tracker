<?php
namespace App\Security;

use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Repository\UserRepository;

class JWTTokenManager
{
    private $jwtTokenManager;
    private $userRepository;

    public function __construct(JWTTokenManagerInterface $jwtTokenManager, UserRepository $userRepository)
    {
        $this->jwtTokenManager = $jwtTokenManager;
        $this->userRepository = $userRepository;
    }

    public function createToken(UserInterface $user): string
    {
        return $this->jwtTokenManager->create($user);
    }

    public function validateToken(string $token)
    {
        try {
            $payload = $this->jwtTokenManager->parse($token);
            $username = $payload['username'] ?? null; 
            
            if ($username) {
                return $this->userRepository->findOneBy(['email' => $username]);
            }
            
            return false; 
        } catch (\Exception $e) {
            return false;
        }
    }
}
