<?php

namespace App\Factory;

use App\DTO\RegisterUserRequest;
use App\Entity\Student;
use App\Entity\Teacher;
use App\Entity\User;

/**
 * Factory to create User entities from DTOs
 */
class UserFactory
{
    public function createFromDto(RegisterUserRequest $dto) : User
    {
        $user = match ($dto->type) {
            'student' => new Student(),
            'teacher' => new Teacher(),
            default => throw new \InvalidArgumentException('Invalid user type: ' . $dto->type),
        };

        $user->setEmail($dto->email)
             ->setName($dto->name)
             ->setLastName($dto->lastName)
            ->setIsVerified(false)
            ->setCreatedAt(new \DateTimeImmutable());

        return $user;
    }
}