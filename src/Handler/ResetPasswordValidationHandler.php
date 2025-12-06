<?php

namespace App\Handler;

use App\Entity\ResetPasswordRequest;
use App\Repository\ResetPasswordRequestRepository;
use InvalidArgumentException;

/**
 * Handles reset password token validation.
 */
class ResetPasswordValidationHandler
{
    public function __construct(
        private ResetPasswordRequestRepository $repository,
    ) {}

    /**
     * @throws InvalidArgumentException
     */
    public function validate(string $selector, string $token): ResetPasswordRequest
    {
        $resetPasswordRequest = $this->repository->findOneBySelector($selector);

        if (!$resetPasswordRequest) {
            throw new InvalidArgumentException('Invalid reset password token.');
        }

        if ($resetPasswordRequest->isExpired()) {
            throw new InvalidArgumentException('Reset password token has expired.');
        }

        if (!password_verify($token, $resetPasswordRequest->getHashedToken())) {
            throw new InvalidArgumentException('Invalid reset password token.');
        }

        return $resetPasswordRequest;
    }
}
