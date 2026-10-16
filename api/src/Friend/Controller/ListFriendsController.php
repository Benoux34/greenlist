<?php

declare(strict_types=1);

namespace App\Friend\Controller;

use App\Friend\Dto\FriendOutput;
use App\Friend\Repository\FriendshipRepository;
use App\User\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

final class ListFriendsController extends AbstractController
{
    #[Route('/friends', name: 'friends_list', methods: ['GET'])]
    public function __invoke(#[CurrentUser] User $user, FriendshipRepository $friendships): JsonResponse
    {
        return $this->json(array_map(FriendOutput::fromEntity(...), $friendships->findByUser($user)));
    }
}
