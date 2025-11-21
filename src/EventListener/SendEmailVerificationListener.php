<?php

namespace App\EventListener;

use App\Event\UserRegisteredEvent;
use App\Service\EmailService;
use App\Service\EmailServiceInterface;

/**
 * Listener to send email verification upon user registration.
 */
class SendEmailVerificationListener
{

    public function __construct(private EmailServiceInterface $emailService) {}

    public function onUserRegistered(UserRegisteredEvent $event)
    {
        $this->emailService->sendEmailVerification( $event->getUser());
    }

}