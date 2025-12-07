<?php

namespace App\EventListener;

use App\Event\ResetPasswordRequestedEvent;
use App\Service\EmailServiceInterface;

/**
 * Listener to send reset password email when requested.
 */
class SendResetPasswordListener
{

    public function __construct(private EmailServiceInterface $emailService) {}

    public function __invoke(ResetPasswordRequestedEvent $event)
    {
        $this->emailService->sendResetPasswordEmail($event->getResetPasswordRequest());
    }

}