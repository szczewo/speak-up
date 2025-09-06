<?php

namespace App\Tests;

use App\Controller\SecurityController;
use App\Entity\Student;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class SecurityControllerTest extends WebTestCase
{
    private static $client;

    /**
     * @return void
     * Sets up the test environment by creating a client and clearing the User table.
     */
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
            ->setPassword($hasher->hashPassword($verifiedUser, 'Password123-'))
            ->setIsVerified(false)
        ->setCreatedAt(new DateTimeImmutable());
        $em->persist($unverified);

        $em->flush();
    }

    /**
     * Tests that a verified user can log in successfully.
     * @return void
     */
    public function testVerifiedUserCanLogin(): void
    {
        $crawler = self::$client->request('GET', '/login');
        $form = $crawler->selectButton('Sign in')->form([
            'email' => 'verified@example.com',
            'password' => 'Password123-',
        ]);
        self::$client->submit($form);

        $this->assertResponseRedirects('/');
    }

    /**
     * Tests that an unverified user cannot log in and receives an appropriate error message.
     * @return void
     */
    public function testUnverifiedUserCannotLogin(): void
    {
        $crawler = self::$client->request('GET', '/login');
        $form = $crawler->selectButton('Sign in')->form([
            'email' => 'unverified@example.com',
            'password' => 'Password123-',
        ]);
        self::$client->submit($form);

        $this->assertResponseRedirects('/login');
        self::$client->followRedirect();
        $this->assertSelectorTextContains('.alert', 'Please verify your email');
    }

}
