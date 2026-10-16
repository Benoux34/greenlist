<?php

declare(strict_types=1);

namespace App\User\Controller;

use App\User\Dto\ForgotPasswordInput;
use App\User\Service\ResetPassword;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

final class ForgotPasswordController extends AbstractController
{
    #[Route('/auth/password/forgot', name: 'auth_password_forgot', methods: ['POST'])]
    public function __invoke(#[MapRequestPayload] ForgotPasswordInput $input, ResetPassword $resetPassword): Response
    {
        $resetPassword->sendCode($input->email);

        return new Response(status: Response::HTTP_NO_CONTENT);
    }
}
