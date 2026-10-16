<?php

declare(strict_types=1);

namespace App\Friend\Controller;

use App\Friend\Service\FriendInvitations;
use App\User\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

final class CreateInvitationController extends AbstractController
{
    #[Route('/friends/invitations', name: 'friends_invitations_create', methods: ['POST'])]
    public function __invoke(#[CurrentUser] User $user, FriendInvitations $invitations): JsonResponse
    {
        return $this->json($invitations->create($user), Response::HTTP_CREATED);
    }
}
