<?php

namespace App\Service;

use App\Entity\ResetPasswordRequest;
use App\Entity\User;

interface EmailServiceInterface
{
    public function sendEmailVerification(User $user): void;

    public function sendResetPasswordEmail(ResetPasswordRequest $resetPasswordRequest): void;
}