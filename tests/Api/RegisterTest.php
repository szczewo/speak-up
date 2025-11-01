<?php

namespace App\Tests\Api;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

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
     * Helper function to assert that the JSON response contains expected key-value pairs.
     * @param array $expected
     * @return void
     */
    protected function assertJsonResponseContains(array $expected): void
    {
        $response = self::$client->getResponse();
        $json = json_decode($response->getContent(), true);
        foreach ($expected as $key => $value) {
            $this->assertEquals($value, $json[$key] ?? null);
        }
    }

    /**
     * Tests successful user registration.
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

        $this->assertJsonResponseContains([
            'status' => 'success',
            'message' => 'User registered successfully'
        ]);
    }

    /**
     * Tests registration with an existing email.
     * @return void
     */
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

        // Second attempt using the same email
        $client->jsonRequest('POST', '/api/register', $payload);
        $this->assertResponseStatusCodeSame(409);

        $this->assertJsonResponseContains([
            'status' => 'error',
            'code' => 'EMAIL_ALREADY_IN_USE',
            'message' => 'Email already in use.',
        ]);
    }

    /**
     * Tests registration with invalid payload.
     * @return void
     */
    #[Group('api')]
    public function testRegistrationWithInvalidPayload(): void
    {
        $client = self::$client;

        $payload = [
            'email' => 'invalid-email',
            'password' => 'short',
            'name' => '',
            'lastName' => 'Student',
            'type' => ''
        ];

        $client->jsonRequest('POST', '/api/register', $payload);
        $this->assertResponseStatusCodeSame(400);
        $this->assertJsonResponseContains([
            'status' => 'error',
            'code' => 'INVALID_PAYLOAD',
            'message' => 'Invalid request data format.',
        ]);

    }
}
