<?php

namespace App\Event;

use App\Entity\ResetPasswordRequest;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event triggered when a password reset is requested.
 */
class ResetPasswordRequestedEvent extends Event
{
    public const NAME = 'user.reset_password_requested';

    public function __construct(
        private readonly ResetPasswordRequest $resetPasswordRequest
    ) {}

  public function getResetPasswordRequest(): ResetPasswordRequest
  {
      return $this->resetPasswordRequest;
  }

}