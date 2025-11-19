<?php

namespace App\DTO;

use App\Enum\UserType;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Data Transfer Object for user registration request.
 */
class RegisterUserRequest
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Email]
        #[Assert\Length(max: 180)]
        public readonly string $email,

        #[Assert\NotBlank]
        #[Assert\Length(min: 8)]
        #[Assert\Regex(
            pattern: '/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_]).{8,}$/',
            message: 'Password must contain an uppercase letter, lowercase letter, number and special character.'
        )]
        public readonly string $password,

        #[Assert\NotBlank]
        #[Assert\Length(min: 2, max: 45)]
        public readonly string $name,

        #[Assert\NotBlank]
        #[Assert\Length(min: 2, max: 45)]
        public readonly string $lastName,

        #[Assert\NotBlank]
        public readonly ?UserType $type,
    ) {}

}