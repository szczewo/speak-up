<?php

namespace App\Handler;

use App\Entity\ResetPasswordRequest;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use InvalidArgumentException;


/**
 * Handles reset password token validation
 */
class ResetPasswordValidationHandler
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {}

    /**
     * @throws Exception
     */
    public function validate( string $selector, string $token) : ResetPasswordRequest
    {
        $resetPasswordRequest = $this->em->getRepository(ResetPasswordRequest::class)
            ->findOneBy(['selector' => $selector]);

        if (!$resetPasswordRequest) {
            throw new InvalidArgumentException('Invalid reset password token.');
        }

        if ($resetPasswordRequest->isExpired()) {
            throw new InvalidArgumentException('Reset password token has expired.');
        }

        if (password_verify($token, $resetPasswordRequest->getHashedToken())) {
            throw new InvalidArgumentException('Invalid reset password token.');
        }

        return $resetPasswordRequest;
    }

}