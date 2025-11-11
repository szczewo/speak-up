<?php

namespace App\Event;

use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event triggered when a user registers.
 */
class UserRegisteredEvent extends Event
{
    public const NAME = 'user.registered';

    public function __construct(
        private readonly User $user
    ) {}

    public function getUser(): User
    {
        return $this->user;
    }

}