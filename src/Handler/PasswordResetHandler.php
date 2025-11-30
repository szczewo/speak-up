<?php

namespace App\Handler;

use App\Entity\ResetPasswordRequest;
use App\Entity\User;
use App\Event\ResetPasswordRequestedEvent;
use App\Service\TokenGeneratorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Handles user registration process
 */
class PasswordResetHandler
{
    public function __construct(
        private EntityManagerInterface $em,
        private EventDispatcherInterface $dispatcher,
        private TokenGeneratorInterface $tokenGenerator,
    ) {}

    /**
     * @throws Exception
     */
    public function handle(string $email) : void
    {
        $existingUser = $this->em->getRepository(User::class)->findOneBy(['email' => $email]);
        if (!$existingUser) {
            throw new InvalidArgumentException('No user found with email: ' . $email);
        }

        $selector = $this->tokenGenerator->generateSelector();
        $plainToken = $this->tokenGenerator->generateToken();

        $hashedToken = password_hash($plainToken, PASSWORD_ARGON2ID);

        $resetPasswordRequest = new ResetPasswordRequest(
            user: $existingUser,
            hashedToken: $hashedToken,
            selector: $selector,
            expiresAt: (new \DateTimeImmutable())->modify('+1 hour')
        );

        $resetPasswordRequest->setPlainToken($plainToken);

        try {
            $this->em->persist($resetPasswordRequest);
            $this->em->flush();
        } catch (Exception $e) {
            throw new RuntimeException('Failed to create reset password request: ' . $e->getMessage());
        }

        try {
            $event = new ResetPasswordRequestedEvent($resetPasswordRequest);
            $this->dispatcher->dispatch($event, ResetPasswordRequestedEvent::NAME);
        } catch (Exception $e) {
            throw new RuntimeException('Failed to dispatch reset password event: ' . $e->getMessage());
        }
    }

}