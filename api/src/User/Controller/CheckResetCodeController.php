<?php

declare(strict_types=1);

namespace App\User\Controller;

use App\User\Dto\ResetCodeInput;
use App\User\Service\ResetPassword;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

final class CheckResetCodeController extends AbstractController
{
    #[Route('/auth/password/check', name: 'auth_password_check', methods: ['POST'])]
    public function __invoke(#[MapRequestPayload] ResetCodeInput $input, ResetPassword $resetPassword): Response
    {
        $resetPassword->checkCode($input->email, $input->code);

        return new Response(status: Response::HTTP_NO_CONTENT);
    }
}
