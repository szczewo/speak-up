<?php

namespace App\DTO;

use App\Enum\UserType;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Data Transfer Object for reset password
 */
readonly class ResetPassword
{
    public function __construct(

        #[Assert\NotBlank]
        public string $selector,

        #[Assert\NotBlank]
        public string $token,

        #[Assert\NotBlank]
        #[Assert\Regex(
            pattern: '/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_]).{8,}$/',
            message: 'Password must contain an uppercase letter, lowercase letter, number and special character.'
        )]
        public string $password,

        #[Assert\NotBlank]
        #[Assert\EqualTo(propertyPath: 'password', message: 'Password confirmation does not match.')]
        public string $passwordConfirmation
    ) {}

}