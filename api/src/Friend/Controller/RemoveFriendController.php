<?php

declare(strict_types=1);

namespace App\Friend\Controller;

use App\Friend\Repository\FriendshipRepository;
use App\User\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

final class RemoveFriendController extends AbstractController
{
    #[Route('/friends/{id}', name: 'friends_remove', requirements: ['id' => Requirement::UUID], methods: ['DELETE'])]
    public function __invoke(#[CurrentUser] User $user, User $friend, FriendshipRepository $friendships): Response
    {
        $friendships->unfriend($user, $friend);

        return new Response(status: Response::HTTP_NO_CONTENT);
    }
}
