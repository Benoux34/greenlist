<?php

declare(strict_types=1);

namespace App\Friend\Controller;

use App\Friend\Service\FriendInvitations;
use App\User\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

final class AcceptInvitationController extends AbstractController
{
    #[Route('/friends/invitations/{token}/accept', name: 'friends_invitations_accept', requirements: ['token' => '[a-f0-9]{32}'], methods: ['POST'])]
    public function __invoke(string $token, #[CurrentUser] User $user, FriendInvitations $invitations): Response
    {
        $invitations->accept($token, $user);

        return new Response(status: Response::HTTP_NO_CONTENT);
    }
}
