<?php

namespace App\Handler;

use App\DTO\RegisterUserRequest;
use App\Entity\User;
use App\Event\UserRegisteredEvent;
use App\Exception\EmailAlreadyInUseException;
use App\Factory\UserFactory;
use App\Service\TokenGeneratorInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use RuntimeException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
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
        private EventDispatcherInterface $dispatcher,
        private TokenGeneratorInterface $tokenGenerator,
    ) {}

    /**
     * @throws Exception
     */
    public function handle(RegisterUserRequest $dto) : void
    {
        $existingUser = $this->em->getRepository(User::class)->findOneBy(['email' => $dto->email]);
        if ($existingUser) {
            throw new EmailAlreadyInUseException('Email already in use: ' . $dto->email);
        }
        $user = $this->factory->createFromDto($dto);

        $hashedPassword = $this->hasher->hashPassword($user, $dto->password);
        $user->setPassword($hashedPassword);

        ['token' => $token, 'expiresAt' => $expiresAt] = $this->tokenGenerator->generateExpiringToken();
        $user->setVerificationToken($token);
        $user->setVerificationTokenExpiresAt($expiresAt);

        try {
            $this->em->persist($user);
            $this->em->flush();

            $event = new UserRegisteredEvent($user);
            $this->dispatcher->dispatch($event, UserRegisteredEvent::NAME);
        } catch (UniqueConstraintViolationException $e) {
            throw new EmailAlreadyInUseException('Email already in use: ' . $dto->email);
        } catch (Exception $e) {
            throw new RuntimeException('Failed to register user: ' . $e->getMessage());
        }
    }

}