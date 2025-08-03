<?php

namespace App\Tests;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
/**
 * @doesNotRemoveExceptionHandlers
 */
class RegistrationControllerTest extends WebTestCase
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

        $em->createQuery('DELETE FROM App\Entity\User u WHERE u.email in (:email)')
            ->setParameter('email',
                [
                    'test@example.com',
                    'student@example.com',
                    'teacher@example.com'
                ])
            ->execute();
    }

    /**
     * @return void
     * Tests if the student registration form is displayed correctly.
     */
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
    public function testStudentCanRegisterWithValidData(): void
    {
        $client = self::$client;
        $crawler = $client->request('GET', '/register/student');

        $form = $crawler->selectButton('Register')->form();

        $form['student_registration_form[email]'] = 'student@example.com';
        $form['student_registration_form[name]'] = 'Test';
        $form['student_registration_form[lastName]'] = 'Student';
        $form['student_registration_form[plainPassword][first]'] = 'Password123-';
        $form['student_registration_form[plainPassword][second]'] = 'Password123-';
        $form['student_registration_form[agreeTerms]'] = true;


        $client->submit($form);

        $this->assertResponseRedirects('/register/check-email');

        $crawler = $client->followRedirect();

        $this->assertResponseIsSuccessful();

        $this->assertSelectorTextContains('h1', 'Thank you for registering!');
    }

    /**
     * @return void
     * Tests if a student cannot register with invalid email.
     */
    public function testStudentCannotRegisterWithInvalidEmail(): void
    {
        $client = self::$client;
        $crawler = $client->request('GET', '/register/student');

        $form = $crawler->selectButton('Register')->form();

        $form['student_registration_form[email]'] = 'invalid-email';
        $form['student_registration_form[name]'] = 'Test';
        $form['student_registration_form[lastName]'] = 'Student';
        $form['student_registration_form[plainPassword][first]'] = 'Password123-';
        $form['student_registration_form[plainPassword][second]'] = 'Password123-';
        $form['student_registration_form[agreeTerms]'] = true;


        $client->submit($form);

        //Redisplay form with errors
        $this->assertResponseIsSuccessful();

        // Check for validation errors
        $this->assertSelectorExists('#student_registration_form_email + .form-error-message');
    }


    /**
     * @return void
     * Tests if a student cannot register with an email that is already in use.
     */
    public function testStudentCannotRegisterWithDuplicateEmail(): void
    {
        $client = self::$client;

        // First registration
        $crawler = $client->request('GET', '/register/student');
        $form = $crawler->selectButton('Register')->form();
        $form['student_registration_form[email]'] = 'student@example.com';
        $form['student_registration_form[name]'] = 'Test';
        $form['student_registration_form[lastName]'] = 'Student';
        $form['student_registration_form[plainPassword][first]'] = 'Password123-';
        $form['student_registration_form[plainPassword][second]'] = 'Password123-';
        $form['student_registration_form[agreeTerms]'] = true;
        $client->submit($form);
        $client->followRedirect();

        // Attempt to register with the same email
        $crawler = $client->request('GET', '/register/student');
        $form = $crawler->selectButton('Register')->form();
        $form['student_registration_form[email]'] = 'student@example.com';
        $form['student_registration_form[name]'] = 'Test';
        $form['student_registration_form[lastName]'] = 'Student';
        $form['student_registration_form[plainPassword][first]'] = 'Password123-';
        $form['student_registration_form[plainPassword][second]'] = 'Password123-';
        $form['student_registration_form[agreeTerms]'] = true;
        $client->submit($form);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('#student_registration_form_email + .form-error-message');
    }


    /**
     * @return void
     * Tests if a student cannot register without agreeing to the terms and conditions.
     */
    public function testStudentCannotRegisterWithoutAgreeingTerms(): void
    {
        $client = self::$client;
        $crawler = $client->request('GET', '/register/student');
        $form = $crawler->selectButton('Register')->form();

        $form['student_registration_form[email]'] = 'student@example.com';
        $form['student_registration_form[name]'] = 'Test';
        $form['student_registration_form[lastName]'] = 'Student';
        $form['student_registration_form[plainPassword][first]'] = 'Password123-';
        $form['student_registration_form[plainPassword][second]'] = 'Password123-';
        $form['student_registration_form[agreeTerms]'] = false;
        $client->submit($form);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('#student_registration_form_agreeTerms + .form-error-message');
    }


    /**
     * @return void
     * Tests if a student cannot register with an invalid password that does not meet the criteria.
     */
    public function testStudentCannotRegisterWithInvalidPasswordCriteriaRegexp(): void
    {
        $client = self::$client;
        $crawler = $client->request('GET', '/register/student');
        $form = $crawler->selectButton('Register')->form();

        $form['student_registration_form[email]'] = 'student@example.com';
        $form['student_registration_form[name]'] = 'Test';
        $form['student_registration_form[lastName]'] = 'Student';
        $form['student_registration_form[plainPassword][first]'] = 'password'; // Invalid password - regex does not match
        $form['student_registration_form[plainPassword][second]'] = 'password';
        $form['student_registration_form[agreeTerms]'] = false;
        $client->submit($form);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('#student_registration_form_plainPassword_first + .form-error-message');
    }


    /**
     * @return void
     * Tests if a student cannot register with an invalid password that does not meet the length criteria.
     */
    public function testStudentCannotRegisterWithInvalidPasswordCriteriaLength(): void
    {
        $client = self::$client;
        $crawler = $client->request('GET', '/register/student');
        $form = $crawler->selectButton('Register')->form();

        $form['student_registration_form[email]'] = 'student@example.com';
        $form['student_registration_form[name]'] = 'Test';
        $form['student_registration_form[lastName]'] = 'Student';
        $form['student_registration_form[plainPassword][first]'] = 'pass'; // Invalid password - lenght does not match
        $form['student_registration_form[plainPassword][second]'] = 'pass';
        $form['student_registration_form[agreeTerms]'] = false;
        $client->submit($form);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('#student_registration_form_plainPassword_first + .form-error-message');
    }


    /**
     * @return void
     * Tests if the teacher registration form is displayed correctly.
     */
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
    public function testTeacherCanRegisterWithValidData(): void
    {
        $client = self::$client;
        $crawler = $client->request('GET', '/register/teacher');
    
        $form = $crawler->selectButton('Register')->form();
    
        $form['teacher_registration_form[email]'] = 'teacher@example.com';
        $form['teacher_registration_form[name]'] = 'Test';
        $form['teacher_registration_form[lastName]'] = 'Teacher';
        $form['teacher_registration_form[plainPassword][first]'] = 'Password123-';
        $form['teacher_registration_form[plainPassword][second]'] = 'Password123-';
        $form['teacher_registration_form[agreeTerms]'] = true;
    
        $client->submit($form);
    
        $this->assertResponseRedirects('/register/check-email');
    
        $crawler = $client->followRedirect();
    
        $this->assertResponseIsSuccessful();
    
        $this->assertSelectorTextContains('h1', 'Thank you for registering!');
    }
    
    /**
     * @return void
     * Tests if a teacher cannot register with invalid email.
     */
    public function testTeacherCannotRegisterWithInvalidEmail(): void
    {
        $client = self::$client;
        $crawler = $client->request('GET', '/register/teacher');
    
        $form = $crawler->selectButton('Register')->form();
    
        $form['teacher_registration_form[email]'] = 'invalid-email';
        $form['teacher_registration_form[name]'] = 'Test';
        $form['teacher_registration_form[lastName]'] = 'Teacher';
        $form['teacher_registration_form[plainPassword][first]'] = 'Password123-';
        $form['teacher_registration_form[plainPassword][second]'] = 'Password123-';
        $form['teacher_registration_form[agreeTerms]'] = true;
    
        $client->submit($form);
    
        //Redisplay form with errors
        $this->assertResponseIsSuccessful();
    
        // Check for validation errors
        $this->assertSelectorExists('#teacher_registration_form_email + .form-error-message');
    }
    
    /**
     * @return void
     * Tests if a teacher cannot register with an email that is already in use.
     */
    public function testTeacherCannotRegisterWithDuplicateEmail(): void
    {
        $client = self::$client;
    
        // First registration
        $crawler = $client->request('GET', '/register/teacher');
        $form = $crawler->selectButton('Register')->form();
        $form['teacher_registration_form[email]'] = 'teacher@example.com';
        $form['teacher_registration_form[name]'] = 'Test';
        $form['teacher_registration_form[lastName]'] = 'Teacher';
        $form['teacher_registration_form[plainPassword][first]'] = 'Password123-';
        $form['teacher_registration_form[plainPassword][second]'] = 'Password123-';
        $form['teacher_registration_form[agreeTerms]'] = true;
        $client->submit($form);
        $client->followRedirect();
    
        // Attempt to register with the same email
        $crawler = $client->request('GET', '/register/teacher');
        $form = $crawler->selectButton('Register')->form();
        $form['teacher_registration_form[email]'] = 'teacher@example.com';
        $form['teacher_registration_form[name]'] = 'Test';
        $form['teacher_registration_form[lastName]'] = 'Teacher';
        $form['teacher_registration_form[plainPassword][first]'] = 'Password123-';
        $form['teacher_registration_form[plainPassword][second]'] = 'Password123-';
        $form['teacher_registration_form[agreeTerms]'] = true;
        $client->submit($form);
    
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('#teacher_registration_form_email + .form-error-message');
    }
    
    /**
     * @return void
     * Tests if a teacher cannot register without agreeing to the terms and conditions.
     */
    public function testTeacherCannotRegisterWithoutAgreeingTerms(): void
    {
        $client = self::$client;
        $crawler = $client->request('GET', '/register/teacher');
        $form = $crawler->selectButton('Register')->form();
    
        $form['teacher_registration_form[email]'] = 'teacher@example.com';
        $form['teacher_registration_form[name]'] = 'Test';
        $form['teacher_registration_form[lastName]'] = 'Teacher';
        $form['teacher_registration_form[plainPassword][first]'] = 'Password123-';
        $form['teacher_registration_form[plainPassword][second]'] = 'Password123-';
        $form['teacher_registration_form[agreeTerms]'] = false;
        $client->submit($form);
    
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('#teacher_registration_form_agreeTerms + .form-error-message');
    }
    
    /**
     * @return void
     * Tests if a teacher cannot register with an invalid password that does not meet the criteria.
     */
    public function testTeacherCannotRegisterWithInvalidPasswordCriteriaRegexp(): void
    {
        $client = self::$client;
        $crawler = $client->request('GET', '/register/teacher');
        $form = $crawler->selectButton('Register')->form();
    
        $form['teacher_registration_form[email]'] = 'teacher@example.com';
        $form['teacher_registration_form[name]'] = 'Test';
        $form['teacher_registration_form[lastName]'] = 'Teacher';
        $form['teacher_registration_form[plainPassword][first]'] = 'password'; // Invalid password - regex does not match
        $form['teacher_registration_form[plainPassword][second]'] = 'password';
        $form['teacher_registration_form[agreeTerms]'] = false;
        $client->submit($form);
    
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('#teacher_registration_form_plainPassword_first + .form-error-message');
    }
    
    /**
     * @return void
     * Tests if a teacher cannot register with an invalid password that does not meet the length criteria.
     */
    public function testTeacherCannotRegisterWithInvalidPasswordCriteriaLength(): void
    {
        $client = self::$client;
        $crawler = $client->request('GET', '/register/teacher');
        $form = $crawler->selectButton('Register')->form();
    
        $form['teacher_registration_form[email]'] = 'teacher@example.com';
        $form['teacher_registration_form[name]'] = 'Test';
        $form['teacher_registration_form[lastName]'] = 'Teacher';
        $form['teacher_registration_form[plainPassword][first]'] = 'pass'; // Invalid password - length does not match
        $form['teacher_registration_form[plainPassword][second]'] = 'pass';
        $form['teacher_registration_form[agreeTerms]'] = false;
        $client->submit($form);
    
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('#teacher_registration_form_plainPassword_first + .form-error-message');
    }






}
