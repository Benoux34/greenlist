<?php

declare(strict_types=1);

namespace App\User\Mail;

use App\User\Entity\PasswordReset;
use App\User\Entity\User;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

final class ResetCodeEmail
{
    public static function create(User $user, string $code): Email
    {
        $minutes = PasswordReset::LIFETIME_MINUTES;
        $name = htmlspecialchars($user->getFirstName());

        return (new Email())
            ->to(new Address($user->getEmail(), $user->getFirstName()))
            ->subject('Votre code pour changer de mot de passe')
            ->text(<<<TEXT
                Bonjour {$user->getFirstName()},

                Voici votre code pour choisir un nouveau mot de passe sur Fauneflore : {$code}

                Il est valable {$minutes} minutes. Si vous n'avez rien demandé, ignorez ce message : votre mot de passe ne change pas.
                TEXT)
            ->html(<<<HTML
                <p>Bonjour {$name},</p>
                <p>Voici votre code pour choisir un nouveau mot de passe sur Fauneflore :</p>
                <p style="font-family:monospace;font-size:28px;font-weight:bold;letter-spacing:6px">{$code}</p>
                <p>Il est valable {$minutes} minutes. Si vous n'avez rien demandé, ignorez ce message : votre mot de passe ne change pas.</p>
                HTML);
    }
}
