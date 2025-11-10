<?php

namespace App\EventListener;

use App\Event\UserRegisteredEvent;
use App\Service\EmailService;

/**
 * Listener to send email verification upon user registration.
 */
class SendEmailVerificationListener
{

    public function __construct
    (
        private EmailService $emailService
    ){}

    public function onUserRegistered(UserRegisteredEvent $event)
    {
        $user = $event->getUser();
        $this->emailService->sendEmailVerification($user);
    }

}