<?php

namespace App\Tests\Api;

use App\Entity\Student;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class LoginTest extends WebTestCase
{
    private static $client;

    protected function setUp(): void
    {
        self::$client = static::createClient();
        $container = self::$client->getContainer();

        /** @var EntityManagerInterface $em */
        $em = $container->get('doctrine')->getManager();

        /** @var UserPasswordHasherInterface $hasher */
        $hasher = $container->get(UserPasswordHasherInterface::class);

        $em->createQuery('DELETE FROM App\Entity\User u WHERE u.email in (:email)')
            ->setParameter('email',
                [
                    'verified@example.com',
                    'unverified@example.com',
                ])
            ->execute();

        //create unverified and verified user
        $verifiedUser = new Student();
        $verifiedUser->setEmail('verified@example.com')
            ->setName('Verified')
            ->setLastName('User')
            ->setPassword($hasher->hashPassword($verifiedUser, 'Password123-'))
            ->setIsVerified(true)
            ->setCreatedAt(new DateTimeImmutable());
        $em->persist($verifiedUser);

        $unverified = new Student();
        $unverified->setEmail('unverified@example.com')
            ->setName('Unverified')
            ->setLastName('User')
            ->setPassword($hasher->hashPassword($unverified, 'Password123-'))
            ->setIsVerified(false)
            ->setCreatedAt(new DateTimeImmutable());
        $em->persist($unverified);

        $em->flush();
    }

    /**
     * @return void
     * Tests that a verified user can successfully log in and receive a token.
     */
    #[Group('api')]
    public function testVerifiedUserCanLogin(): void
    {
        $client = self::$client;
        $client->jsonRequest('POST', '/api/login_check', [
            'username' => 'verified@example.com',
            'password' => 'Password123-',
        ]);

        $this->assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('token', $data);
        $this->assertNotEmpty($data['token']);
    }

    /**
     * @return void
     * Tests that an unverified user cannot log in.
     */
    #[Group('api')]
    public function testUnverifiedUserCannotLogin(): void
    {
        $client = self::$client;
        $client->jsonRequest('POST', '/api/login_check', [
            'username' => 'unverified@example.com',
            'password' => 'Password123-',
        ]);

        $this->assertResponseStatusCodeSame(401);
    }
}
