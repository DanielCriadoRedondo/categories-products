<?php

namespace App\Controller;

use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Core\User\UserInterface;

class AuthController extends AbstractController
{
    #[Route('api/login_check', name: 'login_check', methods: ['POST'])]
    public function login(UserInterface $user, JWTTokenManagerInterface $jwtManager): JsonResponse
    {
        return new JsonResponse(['token' => $jwtManager->create($user)]);
    }
}
