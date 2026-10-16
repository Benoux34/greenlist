<?php

declare(strict_types=1);

namespace App\User\Controller;

use App\User\Dto\ResetPasswordInput;
use App\User\Service\ResetPassword;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

final class ResetPasswordController extends AbstractController
{
    #[Route('/auth/password/reset', name: 'auth_password_reset', methods: ['POST'])]
    public function __invoke(#[MapRequestPayload] ResetPasswordInput $input, ResetPassword $resetPassword): Response
    {
        $resetPassword->changePassword($input->email, $input->code, $input->password);

        return new Response(status: Response::HTTP_NO_CONTENT);
    }
}
