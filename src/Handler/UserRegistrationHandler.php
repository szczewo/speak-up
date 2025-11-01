<?php

namespace App\Handler;

use App\DTO\RegisterUserRequest;
use App\Exception\EmailAlreadyInUseException;
use App\Factory\UserFactory;
use App\Legacy\Security\EmailVerifier;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Handles user registration process
 */
class UserRegistrationHandler
{
    public function __construct(
        private UserFactory $factory,
        private UserPasswordHasherInterface $hasher,
        private EntityManagerInterface $em,
    ) {}

    public function handle(RegisterUserRequest $dto) : void
    {
        $existingUser = $this->em->getRepository('App\Entity\User')->findOneBy(['email' => $dto->email]);
        if ($existingUser) {
            throw new EmailAlreadyInUseException('Email already in use: ' . $dto->email);
        }
        $user = $this->factory->createFromDto($dto);

        $hashedPassword = $this->hasher->hashPassword($user, $dto->password);
        $user->setPassword($hashedPassword);

        try {
            $this->em->persist($user);
            $this->em->flush();
        } catch (UniqueConstraintViolationException $e) {
            throw new EmailAlreadyInUseException('Email already in use: ' . $dto->email);
        } catch (\Exception $e) {
            throw new \RuntimeException('Failed to register user: ' . $e->getMessage());
        }

    }

}