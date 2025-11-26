<?php

namespace App\Handler;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Handles user email verification process
 */
class UserEmailVerificationHandler
{
    public function __construct(private EntityManagerInterface $em) {}

    /**
     * @throws Exception
     */
    public function handle(string $token) : void
    {
        $user = $this->em->getRepository(User::class)->findOneBy([
            'verificationToken' => $token
        ]);

        if (!$user) {
            throw new NotFoundHttpException('Invalid verification token.');
        }

        if ($user->getVerificationTokenExpiresAt() < new \DateTimeImmutable()) {
            throw new RuntimeException('Verification token has expired.');
        }

        $user->setIsVerified(true);
        $user->setVerificationToken(null);
        $user->setVerificationTokenExpiresAt(null);

        $this->em->persist($user);
        $this->em->flush();
    }

}