<?php

declare(strict_types=1);

namespace App\Friend\Controller;

use App\Friend\Dto\InvitationOutput;
use App\Friend\Service\FriendInvitations;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class ShowInvitationController extends AbstractController
{
    #[Route('/friends/invitations/{token}', name: 'friends_invitations_show', requirements: ['token' => '[a-f0-9]{32}'], methods: ['GET'])]
    public function __invoke(string $token, FriendInvitations $invitations): JsonResponse
    {
        return $this->json(InvitationOutput::fromEntity($invitations->find($token)));
    }
}
