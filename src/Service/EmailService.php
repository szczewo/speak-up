<?php

namespace App\Service;

use App\Entity\Student;
use App\Entity\Teacher;
use App\Entity\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Service to handle email sending functionalities.
 */
class EmailService implements EmailServiceInterface
{
    public function __construct(
        private MailerInterface $mailer,
        private string $fromAddress,
        private string $fromName,
        private string $frontendUrl,
    ) {}

    public function sendEmailVerification(User $user) : void
    {
        $verificationUrl = sprintf(
            '%s/verify-email?token=%s',
            rtrim($this->frontendUrl, '/'),
            $user->getVerificationToken()
        );

        $template = match (true) {
            $user instanceof Teacher => 'mail/verification_email_teacher.html.twig',
            $user instanceof Student => 'mail/verification_email_student.html.twig',
            default => throw new \LogicException('Unsupported user type: ' . get_class($user)),
        };

        $email = (new TemplatedEmail())
            ->from(new Address($this->fromAddress, $this->fromName))
            ->to($user->getEmail())
            ->subject('Please confirm your email')
            ->htmlTemplate($template)
            ->context([
                'name' => $user->getName(),
                'verificationTokenExpiresAt' => $user->getVerificationTokenExpiresAt()?->format('Y-m-d H:i'),
                'verificationUrl' => $verificationUrl,
            ]);

        $this->mailer->send($email);
    }

}