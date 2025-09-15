<?php


use App\Controller\ResetPasswordController;
use App\Entity\ResetPasswordRequest;
use App\Entity\Student;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

class ResetPasswordControllerTest extends WebTestCase
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

        $em->createQuery('DELETE FROM App\Entity\ResetPasswordRequest r WHERE r.user IS NOT NULL')->execute();

        $em->createQuery('DELETE FROM App\Entity\User u WHERE u.email in (:email)')
            ->setParameter('email',
                [
                    'reset_student@example.com',
                ])
            ->execute();


        $user = new Student();
        $user->setEmail('reset_student@example.com')
            ->setName('Test')
            ->setLastName('Student')
            ->setPassword($hasher->hashPassword($user, 'Password123-'))
            ->setIsVerified(true)
            ->setCreatedAt(new DateTimeImmutable());
        $em->persist($user);

        $em->flush();
    }


    /**
     * Tests the password reset request process for an existing user.
     * @return void
     */
    public function testRequestResetPasswordForExistingUser() : void
    {
        $crawler = self::$client->request('GET', '/reset-password');
        $form = $crawler->selectButton('Send password reset email')->form([
            'reset_password_request_form[email]' => 'reset_student@example.com'
        ]);
        self::$client->submit($form);

        // Assert that we are redirected to the check email page
        $this->assertResponseRedirects('/reset-password/check-email');
        $this->assertEmailCount(1);
        $email = $this->getMailerMessage(0);
        $crawler = self::$client->followRedirect();
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Password Reset Email Sent');

        //Check if token has been set for the user
        $container = self::$client->getContainer();
        /** @var EntityManagerInterface $em */
        $em = $container->get('doctrine')->getManager();

        /** @var Student $user */
        $user = $em->getRepository(Student::class)->findOneBy(['email' => 'reset_student@example.com']);

        /** @var ResetPasswordRequest $resetRequest */
        $resetRequest = $em->getRepository(ResetPasswordRequest::class)->findOneBy(['user' => $user]);
        $this->assertNotNull($resetRequest);

        //Check reset password request details
        $this->assertNotEmpty($resetRequest->getHashedToken(), 'Hashed token should not be empty');
        $this->assertGreaterThan(new DateTimeImmutable(), $resetRequest->getExpiresAt(), 'Expiration date should be in the future');

        // Check email details
        $fromAddress = self::getContainer()->getParameter('app.mailer_from_address');
        $fromName = self::getContainer()->getParameter('app.mailer_from_name');
        $this->assertEmailHeaderSame($email, 'to', 'reset_student@example.com');
        $this->assertEmailHeaderSame($email, 'from', sprintf('%s <%s>', $fromName, $fromAddress));
        $this->assertEmailHeaderSame($email, 'subject', 'Your password reset request');
        $this->assertEmailHtmlBodyContains($email, '/reset-password/reset/');

        // Extract the reset URL from the email
        preg_match('/href="([^"]*\/reset-password\/reset\/[^"]*)"/', $email->getHtmlBody(), $matches);
        $this->assertNotEmpty($matches, 'Reset URL not found in email body');
        $resetUrl = $matches[1];

        // Simulate clicking the reset link
        $crawler = self::$client->request('GET', $resetUrl);
        $this->assertResponseRedirects('/reset-password/reset');
        $crawler = self::$client->followRedirect();
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Reset your password');

        // Submit the new password form
        $form = $crawler->selectButton('Reset password')->form([
            'change_password_form[plainPassword][first]' => 'NewPassword123-',
            'change_password_form[plainPassword][second]' => 'NewPassword123-',
        ]);
        self::$client->submit($form);
        $this->assertResponseRedirects('/login');

        self::$client->followRedirect();
        $this->assertResponseIsSuccessful();

        // Verify that the password has been updated
        /** @var UserPasswordHasherInterface $hasher */
        $hasher = self::$client->getContainer()->get(UserPasswordHasherInterface::class);
        $user = $em->getRepository(Student::class)->findOneBy(['email' => 'reset_student@example.com']);

        $this->assertTrue($hasher->isPasswordValid($user, 'NewPassword123-'), 'Password was not updated correctly');

        // Verify that the reset request has been removed
        $resetRequest = $em->getRepository(ResetPasswordRequest::class)->findOneBy(['user' => $user]);
        $this->assertNull($resetRequest, 'Reset request was not removed after password reset');
    }

    /**
     * Tests the password reset request process for a non-existing user.
     * @return void
     */
    public function testRequestResetPasswordForNonExistingUser() : void
    {
        $crawler = self::$client->request('GET', '/reset-password');
        $form = $crawler->selectButton('Send password reset email')->form([
            'reset_password_request_form[email]' => 'nonexistent@example.com'
        ]);
        self::$client->submit($form);

        // Assert that we are redirected to the check email page and no email is sent
        $this->assertResponseRedirects('/reset-password/check-email');
        $this->assertEmailCount(0);
        $crawler = self::$client->followRedirect();
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Password Reset Email Sent');

        //Check that no reset password request has been created
        $container = self::$client->getContainer();
        $em = $container->get('doctrine')->getManager();
        $resetRequests = $em->getRepository(ResetPasswordRequest::class)->findAll();
        $this->assertCount(0, $resetRequests, 'No reset requests should be created for non-existing users');
    }

    /**
     * Tests the password reset process with an invalid token.
     * @return void
     */
    public function testResetPasswordWithInvalidToken() : void
    {
        $crawler = self::$client->request('GET', '/reset-password/reset/invalidtoken1234');

        // Check if there is redirect after saving token in session
        $this->assertResponseRedirects('/reset-password/reset');
        self::$client->followRedirect();

        // Check if there is redirect after token validation has failed
        $this->assertResponseRedirects('/reset-password');
        self::$client->followRedirect();

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.alert');
        $this->assertSelectorTextContains('.alert', 'There was a problem validating your password reset request');

    }

    /**
     * Tests the password reset process with an expired token.
     * @return void
     * @throws \Doctrine\DBAL\Exception
     * @throws \SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface
     */
    public function testResetPasswordWithExpiredToken() : void
    {

        $container = self::$client->getContainer();
        /** @var EntityManagerInterface $em */
        $em = $container->get('doctrine')->getManager();

        $user = $em->getRepository(User::class)->findOneBy(['email' => 'reset_student@example.com']);

        // Generate a reset token for the user
        /** @var ResetPasswordHelperInterface $resetPasswordHelper */
        $resetPasswordHelper = $container->get(ResetPasswordHelperInterface::class);

        $resetToken = $resetPasswordHelper->generateResetToken($user);

        $em->flush();

        // Manually expire the token by setting its expiration time to the past
        $connection = $em->getConnection();
        $connection->executeStatement(
            'UPDATE reset_password_request SET expires_at = :past WHERE user_id = :id',
            [
                'past' => (new \DateTimeImmutable('-10 minutes'))->format('Y-m-d H:i:s'),
                'id' => $user->getId()
            ]
        );

        $publicToken = $resetToken->getToken();

        self::$client->request('GET', '/reset-password/reset/' . $publicToken);
        $this->assertResponseRedirects('/reset-password/reset');
        self::$client->followRedirect();

        $this->assertResponseRedirects('/reset-password');
        self::$client->followRedirect();

        $this->assertSelectorExists('.alert');
        $this->assertSelectorTextContains('.alert', 'There was a problem validating your password reset request');

    }

    /**
     * Tests the password reset process with a token that has already been used.
     * @return void
     */
    public function testResetPasswordWithUsedToken()
    {
        $container = self::$client->getContainer();
        /** @var EntityManagerInterface $em */
        $em = $container->get('doctrine')->getManager();

        $user = $em->getRepository(User::class)->findOneBy(['email' => 'reset_student@example.com']);

        // Generate a reset token for the user
        /** @var ResetPasswordHelperInterface $resetPasswordHelper */
        $resetPasswordHelper = $container->get(ResetPasswordHelperInterface::class);
        $resetToken = $resetPasswordHelper->generateResetToken($user);
        $em->flush();
        $publicToken = $resetToken->getToken();

        // Simulate using the token by resetting the password
        self::$client->request('GET', '/reset-password/reset/' . $publicToken);
        self::$client->followRedirect();
        $crawler = self::$client->getCrawler();
        $form = $crawler->selectButton('Reset password')->form([
            'change_password_form[plainPassword][first]' => 'AnotherNewPassword123-',
            'change_password_form[plainPassword][second]' => 'AnotherNewPassword123-',
        ]);
        self::$client->submit($form);
        $this->assertResponseRedirects('/login');
        self::$client->followRedirect();

        // Attempt to reuse the same token
        self::$client->request('GET', '/reset-password/reset/' . $publicToken);
        $this->assertResponseRedirects('/reset-password/reset');
        self::$client->followRedirect();
        $this->assertResponseRedirects('/reset-password');
        self::$client->followRedirect();
        $this->assertSelectorExists('.alert');
        $this->assertSelectorTextContains('.alert', 'There was a problem validating your password reset request');

    }

    /**
     * Tests the password reset process with mismatched passwords.
     * @return void
     * @throws \SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface
     */
    public function testResetPasswordWithMismatchedPasswords()
    {
        $container = self::$client->getContainer();
        /** @var EntityManagerInterface $em */
        $em = $container->get('doctrine')->getManager();

        $user = $em->getRepository(User::class)->findOneBy(['email' => 'reset_student@example.com']);

        // Generate a reset token for the user
        /** @var ResetPasswordHelperInterface $resetPasswordHelper */
        $resetPasswordHelper = $container->get(ResetPasswordHelperInterface::class);
        $resetToken = $resetPasswordHelper->generateResetToken($user);
        $em->flush();
        $publicToken = $resetToken->getToken();
        // Simulate using the token by resetting the password
        self::$client->request('GET', '/reset-password/reset/' . $publicToken);
        self::$client->followRedirect();
        $crawler = self::$client->getCrawler();
        $form = $crawler->selectButton('Reset password')->form([
            'change_password_form[plainPassword][first]' => 'MismatchPassword123-',
            'change_password_form[plainPassword][second]' => 'DifferentPassword123-',
        ]);
        self::$client->submit($form);
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('#change_password_form_plainPassword_first + .form-error-message');

    }

    /**
     * Tests the password reset process with a weak password that does not meet criteria.
     * @return void
     * @throws \SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface
     */
    public function testResetPasswordWithWeakPassword() : void
    {
        $container = self::$client->getContainer();
        /** @var EntityManagerInterface $em */
        $em = $container->get('doctrine')->getManager();

        $user = $em->getRepository(User::class)->findOneBy(['email' => 'reset_student@example.com']);

        // Generate a reset token for the user
        /** @var ResetPasswordHelperInterface $resetPasswordHelper */
        $resetPasswordHelper = $container->get(ResetPasswordHelperInterface::class);
        $resetToken = $resetPasswordHelper->generateResetToken($user);
        $em->flush();
        $publicToken = $resetToken->getToken();
        // Simulate using the token by resetting the password
        self::$client->request('GET', '/reset-password/reset/' . $publicToken);
        self::$client->followRedirect();
        $crawler = self::$client->getCrawler();
        $form = $crawler->selectButton('Reset password')->form([
            'change_password_form[plainPassword][first]' => 'password', // Invalid password - regex does not match
            'change_password_form[plainPassword][second]' => 'password',
        ]);
        self::$client->submit($form);
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('#change_password_form_plainPassword_first + .form-error-message');

    }


    /**
     * Tests that multiple password reset requests are throttled to prevent abuse.
     * @return void
     */
    public function testMultipleResetRequestsThrottle() : void
    {
        $container = self::$client->getContainer();
        /** @var EntityManagerInterface $em */
        $em = $container->get('doctrine')->getManager();

        $user = $em->getRepository(User::class)->findOneBy(['email' => 'reset_student@example.com']);
        $crawler = self::$client->request('GET', '/reset-password');
        $form = $crawler->selectButton('Send password reset email')->form([
            'reset_password_request_form[email]' => 'reset_student@example.com']);
        // Send multiple requests in quick succession
        for ($i = 0; $i < 5; $i++) {
            self::$client->submit($form);
            // Delay to simulate rapid requests
            usleep(100000);
        }
        // Check that only one reset request exists
        $resetRequests = $em->getRepository(ResetPasswordRequest::class)->findBy(['user' => $user]);
        $this->assertCount(1, $resetRequests, 'Only one reset request should be created due to throttling');
    }


}
