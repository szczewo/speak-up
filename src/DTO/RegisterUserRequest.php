<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;
class RegisterUserRequest
{
    #[Assert\NotBlank]
    #[Assert\Email]
    #[Assert\Length(max: 180)]
    public string $email;

    #[Assert\NotBlank]
    #[Assert\Length(min: 8)]
    #[Assert\Regex(
        pattern: '/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_]).{8,}$/',
        message: 'Password must contain an uppercase letter, lowercase letter, number and special character.'
    )]
    public string $password;

    #[Assert\NotBlank]
    #[Assert\Length(min:2, max: 45)]
    public string $name;

    #[Assert\NotBlank]
    #[Assert\Length(min:2, max: 45)]
    public string $lastName;

    #[Assert\NotBlank]
    #[Assert\Choice(['student', 'teacher'])]
    public string $type;



}