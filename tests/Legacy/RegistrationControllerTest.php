<?php

namespace App\Tests\Legacy;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\MailerAssertionsTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @deprecated Legacy test for old Twig registration.
 * @doesNotRemoveExceptionHandlers
 */
class RegistrationControllerTest extends WebTestCase
{
    use MailerAssertionsTrait;
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

        $em->createQuery('DELETE FROM App\Entity\User u WHERE u.email in (:email)')
            ->setParameter('email',
                [
                    'test@example.com',
                    'student@example.com',
                    'teacher@example.com'
                ])
            ->execute();
    }

    private function submitRegistrationForm(string $type, array $formData)
    {
        $client = self::$client;
        $crawler = $client->request('GET', "/register/$type");
        $form = $crawler->selectButton('Register')->form();

        foreach ($formData as $key => $value) {
            if (preg_match('/^([^\[]+)\[([^\]]+)\]$/', $key, $matches)) {
                $form["{$type}_registration_form[{$matches[1]}][{$matches[2]}]"] = $value;
            } else {
                $form["{$type}_registration_form[$key]"] = $value;
            }
        }

        $client->submit($form);
        return $client;
    }


    /**
     * @return void
     * Tests if the student registration form is displayed correctly.
     */
    #[Group('legacy')]
    public function testStudentRegistrationFormDisplays(): void
    {
        $crawler = self::$client->request('GET', '/register/student');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
        $this->assertSelectorTextContains('button', 'Register');
    }

    /**
     * @return void
     * Tests if a student can register with valid data.
     */
    #[Group('legacy')]
    public function testStudentCanRegisterWithValidData(): void
    {

        $formData = [
            'email' => 'student@example.com',
            'name' => 'Test',
            'lastName' => 'Student',
            'plainPassword[first]' => 'Password123-',
            'plainPassword[second]' => 'Password123-',
            'agreeTerms' => true,
        ];
        $client = $this->submitRegistrationForm('student', $formData);

        // Check redirect to check-email page and that a confirmation email was sent
        $this->assertResponseRedirects('/register/check-email');
        $this->assertEmailCount(1);
        $email = $this->getMailerMessage(0);
        $crawler = $client->followRedirect();
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Thank you for registering!');

        //Check email details
        $fromAddress = self::getContainer()->getParameter('app.mailer_from_address');
        $fromName = self::getContainer()->getParameter('app.mailer_from_name');

        $this->assertEmailHeaderSame($email, 'to', 'student@example.com');
        $this->assertEmailHeaderSame($email, 'from', sprintf('%s <%s>', $fromName, $fromAddress));
        $this->assertEmailHeaderSame($email, 'subject', 'Please Confirm your Email');
        $this->assertEmailHtmlBodyContains($email, 'Please confirm your email');

        // Check for confirmation link in the email
        $this->assertEmailHtmlBodyContains($email, '/verify/email');

        //Extract the confirmation link
        preg_match('/https?:\/\/[^\s"]+\/verify\/email[^\s"]*/', $email->getHtmlBody(), $matches);
        $this->assertNotEmpty($matches, 'No confirmation link found in email.');
        $confirmationLink = $matches[0];

        // Simulate clicking the confirmation link
        $client->request('GET', $confirmationLink);
        $this->assertResponseRedirects('/login');
        $client->followRedirect();

        //Check if user is verified in the database
        $container = self::$client->getContainer();
        /** @var EntityManagerInterface $em */
        $em = $container->get('doctrine')->getManager();
        $user = $em->getRepository('App\Entity\User')->findOneBy(['email' => 'student@example.com']);
        $this->assertNotNull($user, 'User not found in database.');
        $this->assertTrue($user->isVerified(), 'User email is not verified.');
    }

    /**
     * @return void
     * Tests if a student cannot register with invalid email.
     */
    #[Group('legacy')]
    public function testStudentCannotRegisterWithInvalidEmail(): void
    {

        $formData = [
            'email' => 'invalid-email',
            'name' => 'Test',
            'lastName' => 'Student',
            'plainPassword[first]' => 'Password123-',
            'plainPassword[second]' => 'Password123-',
            'agreeTerms' => true,
        ];

        $client = $this->submitRegistrationForm('student', $formData);

        //Redisplay form with errors
        $this->assertResponseIsSuccessful();

        // Check for validation errors
        $this->assertSelectorExists('#student_registration_form_email + .form-error-message');
    }


    /**
     * @return void
     * Tests if a student cannot register with an email that is already in use.
     */
    #[Group('legacy')]
    public function testStudentCannotRegisterWithDuplicateEmail(): void
    {
        $formData = [
            'email' => 'student@example.com',
            'name' => 'Test',
            'lastName' => 'Student',
            'plainPassword[first]' => 'Password123-',
            'plainPassword[second]' => 'Password123-',
            'agreeTerms' => true,
        ];
        $client = $this->submitRegistrationForm('student', $formData);
        $client->followRedirect();

        // Attempt to register with the same email
        $this->submitRegistrationForm('student', $formData);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('#student_registration_form_email + .form-error-message');
    }


    /**
     * @return void
     * Tests if a student cannot register without agreeing to the terms and conditions.
     */
    #[Group('legacy')]
    public function testStudentCannotRegisterWithoutAgreeingTerms(): void
    {
        $formData = [
            'email' => 'student@example.com',
            'name' => 'Test',
            'lastName' => 'Student',
            'plainPassword[first]' => 'Password123-',
            'plainPassword[second]' => 'Password123-',
            'agreeTerms' => false,
        ];
        $client = $this->submitRegistrationForm('student', $formData);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('#student_registration_form_agreeTerms ~ .form-error-message');
    }


    /**
     * @return void
     * Tests if a student cannot register with an invalid password that does not meet the criteria.
     */
    #[Group('legacy')]
    public function testStudentCannotRegisterWithInvalidPasswordCriteriaRegexp(): void
    {
        $formData = [
            'email' => 'student@example.com',
            'name' => 'Test',
            'lastName' => 'Student',
            'plainPassword[first]' => 'password', // Invalid password - regex does not match
            'plainPassword[second]' => 'password',
            'agreeTerms' => true,
        ];
        $client = $this->submitRegistrationForm('student', $formData);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('#student_registration_form_plainPassword_first + .form-error-message');
    }


    /**
     * @return void
     * Tests if a student cannot register with an invalid password that does not meet the length criteria.
     */
    #[Group('legacy')]
    public function testStudentCannotRegisterWithInvalidPasswordCriteriaLength(): void
    {
        $formData = [
            'email' => 'student@example.com',
            'name' => 'Test',
            'lastName' => 'Student',
            'plainPassword[first]' =>  'pass', // Invalid password - length does not match
            'plainPassword[second]' => 'pass',
            'agreeTerms' => true,
        ];
        $client = $this->submitRegistrationForm('student', $formData);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('#student_registration_form_plainPassword_first + .form-error-message');
    }


    /**
     * @return void
     * Tests if the teacher registration form is displayed correctly.
     */
    #[Group('legacy')]
    public function testTeacherRegistrationFormDisplays(): void
    {
        $crawler = self::$client->request('GET', '/register/teacher');
    
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
        $this->assertSelectorTextContains('button', 'Register');
    }
    
    /**
     * @return void
     * Tests if a teacher can register with valid data.
     */
    #[Group('legacy')]
    public function testTeacherCanRegisterWithValidData(): void
    {
        $formData = [
            'email' => 'teacher@example.com',
            'name' => 'Test',
            'lastName' => 'Teacher',
            'plainPassword[first]' => 'Password123-',
            'plainPassword[second]' => 'Password123-',
            'agreeTerms' => true,
        ];
        $client = $this->submitRegistrationForm('teacher', $formData);

        // Check redirect to check-email page and that a confirmation email was sent
        $this->assertResponseRedirects('/register/check-email');
        $this->assertEmailCount(1);
        $email = $this->getMailerMessage(0);
        $crawler = $client->followRedirect();
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Thank you for registering!');

        //Check email details
        $fromAddress = self::getContainer()->getParameter('app.mailer_from_address');
        $fromName = self::getContainer()->getParameter('app.mailer_from_name');

        $this->assertEmailHeaderSame($email, 'to', 'teacher@example.com');
        $this->assertEmailHeaderSame($email, 'from', sprintf('%s <%s>', $fromName, $fromAddress));
        $this->assertEmailHeaderSame($email, 'subject', 'Please Confirm your Email');
        $this->assertEmailHtmlBodyContains($email, 'Please confirm your email');

        // Check for confirmation link in the email
        $this->assertEmailHtmlBodyContains($email, '/verify/email');

        //Extract the confirmation link
        preg_match('/https?:\/\/[^\s"]+\/verify\/email[^\s"]*/', $email->getHtmlBody(), $matches);
        $this->assertNotEmpty($matches, 'No confirmation link found in email.');
        $confirmationLink = $matches[0];

        // Simulate clicking the confirmation link
        $client->request('GET', $confirmationLink);
        $this->assertResponseRedirects('/login');
        $client->followRedirect();

        //Check if user is verified in the database
        $container = self::$client->getContainer();
        /** @var EntityManagerInterface $em */
        $em = $container->get('doctrine')->getManager();
        $user = $em->getRepository('App\Entity\User')->findOneBy(['email' => 'teacher@example.com']);
        $this->assertNotNull($user, 'User not found in database.');
        $this->assertTrue($user->isVerified(), 'User email is not verified.');

    }
    
    /**
     * @return void
     * Tests if a teacher cannot register with invalid email.
     */
    #[Group('legacy')]
    public function testTeacherCannotRegisterWithInvalidEmail(): void
    {
        $formData = [
            'email' => 'invalid-email',
            'name' => 'Test',
            'lastName' => 'Teacher',
            'plainPassword[first]' => 'Password123-',
            'plainPassword[second]' => 'Password123-',
            'agreeTerms' => true,
        ];
        $client = $this->submitRegistrationForm('teacher', $formData);
    
        //Redisplay form with errors
        $this->assertResponseIsSuccessful();
    
        // Check for validation errors
        $this->assertSelectorExists('#teacher_registration_form_email + .form-error-message');
    }
    
    /**
     * @return void
     * Tests if a teacher cannot register with an email that is already in use.
     */
    #[Group('legacy')]
    public function testTeacherCannotRegisterWithDuplicateEmail(): void
    {
        $formData = [
            'email' => 'teacher@example.com',
            'name' => 'Test',
            'lastName' => 'Teacher',
            'plainPassword[first]' => 'Password123-',
            'plainPassword[second]' => 'Password123-',
            'agreeTerms' => true,
        ];

        // First registration
        $client = $this->submitRegistrationForm('teacher', $formData);

        $client->followRedirect();
    
        // Attempt to register with the same email
        $client = $this->submitRegistrationForm('teacher', $formData);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('#teacher_registration_form_email + .form-error-message');
    }
    
    /**
     * @return void
     * Tests if a teacher cannot register without agreeing to the terms and conditions.
     */
    #[Group('legacy')]
    public function testTeacherCannotRegisterWithoutAgreeingTerms(): void
    {
        $formData = [
            'email' => 'teacher@example.com',
            'name' => 'Test',
            'lastName' => 'Teacher',
            'plainPassword[first]' => 'Password123-',
            'plainPassword[second]' => 'Password123-',
            'agreeTerms' => false,
        ];
        $client = $this->submitRegistrationForm('teacher', $formData);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('#teacher_registration_form_agreeTerms ~ .form-error-message');
    }
    
    /**
     * @return void
     * Tests if a teacher cannot register with an invalid password that does not meet the criteria.
     */
    #[Group('legacy')]
    public function testTeacherCannotRegisterWithInvalidPasswordCriteriaRegexp(): void
    {
        $formData = [
            'email' => 'teacher@example.com',
            'name' => 'Test',
            'lastName' => 'Teacher',
            'plainPassword[first]' => 'password', // Invalid password - regex does not match
            'plainPassword[second]' => 'password',
            'agreeTerms' => true,
        ];
        $client = $this->submitRegistrationForm('teacher', $formData);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('#teacher_registration_form_plainPassword_first + .form-error-message');
    }
    
    /**
     * @return void
     * Tests if a teacher cannot register with an invalid password that does not meet the length criteria.
     */
    #[Group('legacy')]
    public function testTeacherCannotRegisterWithInvalidPasswordCriteriaLength(): void
    {
        $formData = [
            'email' => 'teacher@example.com',
            'name' => 'Test',
            'lastName' => 'Teacher',
            'plainPassword[first]' => 'pass', // Invalid password - length does not match
            'plainPassword[second]' => 'pass',
            'agreeTerms' => true,
        ];
        $client = $this->submitRegistrationForm('teacher', $formData);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('#teacher_registration_form_plainPassword_first + .form-error-message');
    }






}
