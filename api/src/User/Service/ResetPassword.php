<?php

declare(strict_types=1);

namespace App\User\Service;

use App\User\Entity\PasswordReset;
use App\User\Entity\User;
use App\User\Exception\InvalidResetCodeException;
use App\User\Mail\ResetCodeEmail;
use App\User\Repository\PasswordResetRepository;
use App\User\Repository\UserRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;

final readonly class ResetPassword
{
    private const string ALPHABET = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';

    public function __construct(
        private UserRepository $users,
        private PasswordResetRepository $resets,
        private PasswordHasherFactoryInterface $hasherFactory,
        private MailerInterface $mailer,
        private LoggerInterface $logger,
        #[Autowire('%kernel.secret%')]
        private string $secret,
    ) {
    }

    public function sendCode(string $email): void
    {
        $user = $this->users->findOneBy(['email' => mb_strtolower($email)]);
        if (null === $user) {
            return;
        }

        $now = new \DateTimeImmutable();
        $reset = $this->resets->findOneBy(['user' => $user]);
        if (null !== $reset && $reset->wasIssuedWithinAMinute($now)) {
            return;
        }

        $code = $this->newCode();
        if (null === $reset) {
            $reset = new PasswordReset($user, $this->hash($code), $now);
        } else {
            $reset->renew($this->hash($code), $now);
        }
        $this->resets->save($reset);

        try {
            $this->mailer->send(ResetCodeEmail::create($user, $code));
        } catch (TransportExceptionInterface $exception) {
            $this->logger->error('Password reset code not sent.', ['exception' => $exception]);
        }
    }

    public function checkCode(string $email, string $code): void
    {
        $this->validReset($email, $code);
    }

    public function changePassword(string $email, string $code, string $password): void
    {
        $reset = $this->validReset($email, $code);
        $user = $reset->getUser();

        $user->changePassword($this->hasherFactory->getPasswordHasher(User::class)->hash($password));
        $this->resets->remove($reset);
        $this->users->revokeSessions($user);
    }

    private function validReset(string $email, string $code): PasswordReset
    {
        $reset = $this->resets->findOneByEmail($email);
        if (null === $reset) {
            throw new InvalidResetCodeException('No password reset under way.');
        }

        $matches = $reset->matches($this->hash($code), new \DateTimeImmutable());
        $this->resets->save($reset);

        if (!$matches) {
            throw new InvalidResetCodeException('Wrong or expired code.');
        }

        return $reset;
    }

    private function newCode(): string
    {
        $code = '';
        for ($i = 0; $i < 6; ++$i) {
            $code .= self::ALPHABET[random_int(0, \strlen(self::ALPHABET) - 1)];
        }

        return $code;
    }

    private function hash(string $code): string
    {
        return hash_hmac('sha256', mb_strtoupper($code), $this->secret);
    }
}
