<?php

declare(strict_types=1);

namespace App\Observation\Security;

use App\Observation\Entity\Observation;
use App\User\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class ObservationVoter extends Voter
{
    public const string VIEW = 'OBSERVATION_VIEW';
    public const string EDIT = 'OBSERVATION_EDIT';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return \in_array($attribute, [self::VIEW, self::EDIT], true) && $subject instanceof Observation;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();

        return $user instanceof User && $subject->getAuthor()->getId()->equals($user->getId());
    }
}
