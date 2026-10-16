<?php

declare(strict_types=1);

namespace App\Observation\Security;

use App\Observation\Entity\Observation;
use App\Observation\Entity\Visibility;

use App\User\Entity\User;
use App\Friend\Repository\FriendshipRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class ObservationVoter extends Voter
{
    public const string VIEW = 'OBSERVATION_VIEW';
    public const string EDIT = 'OBSERVATION_EDIT';

    public function __construct(
        private readonly FriendshipRepository $friendships,
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return \in_array($attribute, [self::VIEW, self::EDIT], true) && $subject instanceof Observation;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        $author = $subject->getAuthor();
        if ($author->getId()->equals($user->getId())) {
            return true;
        }

        return self::VIEW === $attribute
            && Visibility::Friends === $subject->getVisibility()
            && $this->friendships->areFriends($user, $author);
    }
}
