<?php

namespace App\Handler;

use App\DTO\ResetPassword;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
* Handles password reset
 */
class PasswordResetFinalHandler
{
    public function __construct(
        private EntityManagerInterface $em,
        private PasswordResetValidationHandler $validator,
        private UserPasswordHasherInterface $hasher
    ) {}

    /**
     * @throws Exception
     */
    public function handle(ResetPassword $dto) : void
    {
        $resetPasswordRequest = $this->validator->validate($dto->selector, $dto->token);

        $user = $resetPasswordRequest->getUser();

        $hashedPassword = $this->hasher->hashPassword($user, $dto->password);
        $user->setPassword($hashedPassword);

        try {
            $this->em->remove($resetPasswordRequest);
            $this->em->flush();
        } catch (Exception $e) {
            throw new RuntimeException('Failed to reset password: ' . $e->getMessage());
        }
    }

}