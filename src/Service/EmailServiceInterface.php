<?php

namespace App\Service;

use App\Entity\User;

interface EmailServiceInterface
{
    public function sendEmailVerification(User $user): void;
}