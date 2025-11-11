<?php

namespace Api;

use App\Entity\Student;
use App\Tests\Trait\JsonResponseAsserts;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class VerifyEmailTest extends WebTestCase
{
    private static $client;

    use JsonResponseAsserts;

    protected function setUp(): void
    {
        self::$client = static::createClient();
        $container = self::$client->getContainer();

        /** @var EntityManagerInterface $em */
        $em = $container->get('doctrine')->getManager();

        $em->createQuery('DELETE FROM App\Entity\User u WHERE u.email in (:email)')
            ->setParameter('email',
                [
                    'valid@example.com',
                    'expired@example.com'
                ])
            ->execute();

        $user = new Student();
        $user->setEmail('valid@example.com');
        $user->setName('Test');
        $user->setLastName('User')
            ->setPassword('hashedpassword')
            ->setIsVerified(false)
            ->setVerificationToken('valid-token')
            ->setVerificationTokenExpiresAt(new \DateTimeImmutable('+1 hour'));

        $userExpired = new Student();
        $userExpired->setEmail('expired@example.com')
            ->setName('Expired')
            ->setLastName('User')
            ->setPassword('hashedpassword')
            ->setIsVerified(false)
            ->setVerificationToken('expired-token')
            ->setVerificationTokenExpiresAt(new \DateTimeImmutable('-1 hour'));

        $em->persist($user);
        $em->persist($userExpired);
        $em->flush();
    }


    /**
     * Tests successful email verification.
     * @return void
     */
    #[Group('api')]
    public function testVerificationWithValidToken(): void
    {
        $client = self::$client;

        $client->jsonRequest('POST', '/api/verify-email', ['token' => 'valid-token']);
        $this->assertResponseIsSuccessful();
        $this->assertJsonResponseContains(
            $client->getResponse(), [
            'status' => 'success',
            'message' => 'Email verified successfully.'
        ]);
    }


    /**
     * Tests email verification with an invalid token.
     * @return void
     */
    #[Group('api')]
    public function testVerificationWithInvalidToken(): void
    {
        $client = self::$client;
        $client->jsonRequest('POST', '/api/verify-email', ['token' => 'invalid-token']);
        $this->assertResponseStatusCodeSame(400);
        $this->assertJsonResponseContains(
            $client->getResponse(), [
                'status' => 'error',
                'code' => 'VERIFICATION_FAILED',
                'message' => 'Email verification failed.'
            ]
        );
    }


    /**
     * Tests email verification with an expired token.
     * @return void
     */
    #[Group('api')]
    public function testVerificationWithExpiredToken(): void
    {
        $client = self::$client;
        $client->jsonRequest('POST', '/api/verify-email', ['token' => 'expired-token']);
        $this->assertResponseStatusCodeSame(400);
        $this->assertJsonResponseContains(
            $client->getResponse(), [
                'status' => 'error',
                'code' => 'VERIFICATION_FAILED',
                'message' => 'Email verification failed.'
            ]
        );
    }

    /**
     * Tests email verification with a missing token.
     * @return void
     */
    #[Group('api')]
    public function testVerificationWithMissingToken(): void
    {
        $client = self::$client;
        $client->jsonRequest('POST', '/api/verify-email', []);
        $this->assertResponseStatusCodeSame(400);
        $this->assertJsonResponseContains(
            $client->getResponse(), [
                'status' => 'error',
                'code' => 'MISSING_TOKEN',
                'message' => 'Missing verification token.'
            ]
        );
    }

}
