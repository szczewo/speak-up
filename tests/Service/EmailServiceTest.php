<?php

namespace App\Tests\Service;

use App\Entity\Student;
use App\Entity\Teacher;
use App\Entity\User;
use App\Service\EmailService;
use App\Service\EmailServiceInterface;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class EmailServiceTest extends TestCase
{
    private MailerInterface $mailer;
    private EmailServiceInterface $emailService;


    protected function setUp(): void
    {
        $this->mailer = $this->createMock(MailerInterface::class);
        $this->emailService  = new EmailService(
            $this->mailer,
            'test@example.com',
            'Test Sender',
            'http://frontend.test',
        );
    }

    /**
     * Tests sending email verification for a Student user.
     * @return void
     */
    #[Group('service')]
    public function testSendEmailVerificationForStudent()
    {
        $student = new Student();
        $student->setEmail('student@example.com')
            ->setName('Test')
            ->setLastName('Student')
            ->setIsVerified(false)
            ->setVerificationToken('verification-token-123')
            ->setVerificationTokenExpiresAt(new DateTimeImmutable('+1 day'));

        $this->mailer->expects($this->once())
            ->method('send')
            ->with($this->callback(function (Email $email) use ($student) {
                $this->assertSame('student@example.com', $email->getTo()[0]->getAddress());
                $this->assertSame('mail/verification_email_student.html.twig', $email->getHtmlTemplate());
                $context = $email->getContext();
                $this->assertArrayHasKey('verificationUrl', $context);
                $this->assertStringContainsString('verify-email?token=verification-token-123', $context['verificationUrl']);
                return true;
            }));

        $this->emailService->sendEmailVerification($student);
    }

    /**
     * Tests sending email verification for a Teacher user.
     * @return void
     */
    #[Group('service')]
    public function testSendEmailVerificationForTeacher(): void
    {
        $teacher = (new Teacher())
            ->setEmail('teacher@example.com')
            ->setName('Test')
            ->setLastName('Teacher')
            ->setVerificationToken('verification-token-123')
            ->setVerificationTokenExpiresAt(new DateTimeImmutable('+1 day'));

        $this->mailer
            ->expects($this->once())
            ->method('send')
            ->with($this->callback(function (Email $email) use ($teacher) {
                $this->assertSame('teacher@example.com', $email->getTo()[0]->getAddress());
                $this->assertSame('mail/verification_email_teacher.html.twig', $email->getHtmlTemplate());
                $context = $email->getContext();
                $this->assertArrayHasKey('verificationUrl', $context);
                $this->assertStringContainsString('verify-email?token=verification-token-123', $context['verificationUrl']);
                return true;
            }));

        $this->emailService->sendEmailVerification($teacher);
    }

    /**
     * Tests sending email verification for an unsupported user type.
     * @return void
     */
    #[Group('service')]
    public function testSendEmailVerificationUnsupportedUserType(): void
    {
        $this->expectException(\LogicException::class);

        $unsupportedUser = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->emailService->sendEmailVerification($unsupportedUser);
    }
}
