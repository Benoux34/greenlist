<?php

declare(strict_types=1);

namespace App\User\Controller;

use App\User\Dto\RegisterUserInput;
use App\User\Dto\UserOutput;
use App\User\Service\RegisterUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

final class RegisterController extends AbstractController
{
    #[Route('/auth/register', name: 'auth_register', methods: ['POST'])]
    public function __invoke(
        #[MapRequestPayload] RegisterUserInput $input,
        RegisterUser $registerUser,
    ): JsonResponse {
        $user = $registerUser($input);

        return $this->json(UserOutput::fromEntity($user), Response::HTTP_CREATED);
    }
}
