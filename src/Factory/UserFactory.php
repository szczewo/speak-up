<?php

namespace App\Factory;

use App\DTO\RegisterUserRequest;
use App\Entity\Student;
use App\Entity\Teacher;
use App\Entity\User;
use App\Enum\UserType;

/**
 * Factory to create User entities from DTOs
 */
class UserFactory
{
    public function createFromDto(RegisterUserRequest $dto) : User
    {
        $user = match ($dto->type) {
            UserType::STUDENT => new Student(),
            UserType::TEACHER => new Teacher(),
            default => throw new \InvalidArgumentException('Invalid user type: ' . $dto->type->value),
        };

        $roles = match ($dto->type) {
            UserType::STUDENT => ['ROLE_USER','ROLE_STUDENT'],
            UserType::TEACHER => ['ROLE_USER','ROLE_TEACHER'],
            default => throw new \Exception('Unexpected match value'),
        };

        $user->setEmail($dto->email)
            ->setName($dto->name)
            ->setLastName($dto->lastName)
            ->setIsVerified(false)
            ->setCreatedAt(new \DateTimeImmutable())
            ->setRoles($roles);

        return $user;
    }
}