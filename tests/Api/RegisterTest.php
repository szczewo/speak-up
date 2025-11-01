<?php

namespace App\Tests\api;

use App\Entity\Student;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegisterTest extends WebTestCase
{
    private static $client;

    protected function setUp(): void
    {
        self::$client = static::createClient();
        $container = self::$client->getContainer();

        /** @var EntityManagerInterface $em */
        $em = $container->get('doctrine')->getManager();

        $em->createQuery('DELETE FROM App\Entity\User u WHERE u.email in (:email)')
            ->setParameter('email',
                [
                    'test@example.com',
                ])
            ->execute();
    }

    /**
     * @return void
     */
    #[Group('api')]
    public function testSuccessfulRegistration(): void
    {
        $client = self::$client;

        $payload = [
            'email' => 'test@example.com',
            'password' => 'Password123-',
            'name' => 'Test',
            'lastName' => 'Student',
            'type' => 'student'
        ];

        $client->jsonRequest('POST', '/api/register', $payload);
        $this->assertResponseStatusCodeSame(201);

        $this->assertJsonContains([
            'status' => 'success',
            'message' => 'User registered successfully'
        ]);
    }

    #[Group('api')]
   public function testRegistrationWithExistingEmail(): void
    {
        $client = self::$client;

        $payload = [
            'email' => 'test@example.com',
            'password' => 'Password123-',
            'name' => 'Test',
            'lastName' => 'Student',
            'type' => 'student'
        ];

        $client->jsonRequest('POST', '/api/register', $payload);
        $this->assertResponseStatusCodeSame(201);

        // Second attempt isong the same email
        $client->jsonRequest('POST', '/api/register', $payload);
        $this->assertResponseStatusCodeSame(409);

        $this->assertJsonContains([
            'status' => 'error',
            'code' => 'EMAIL_ALREADY_IN_USE',
            'message' => 'Email already in use.',
        ]);

    }
}
