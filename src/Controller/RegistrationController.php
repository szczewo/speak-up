<?php

namespace App\Controller;

use App\Entity\Student;
use App\Entity\Teacher;
use App\Entity\User;
use App\Form\StudentRegistrationFormType;
use App\Form\TeacherRegistrationFormType;
use App\Security\EmailVerifier;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    private EmailVerifier $emailVerifier;

    public function __construct(EmailVerifier $emailVerifier)
    {
        $this->emailVerifier = $emailVerifier;
    }

    #[Route('/register/student', name: 'app_register_student')]
    public function registerStudent(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager
    ): Response
    {
        $user = new Student();
        $form = $this->createForm(StudentRegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
            $user->setCreatedAt(new DateTimeImmutable());

            $entityManager->persist($user);
            $entityManager->flush();

            // generate a signed url and email it to the user
            $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                (new TemplatedEmail())
                    ->from(new Address('aniastrzewiczek@gmail.com', 'Speak Up'))
                    ->to($user->getEmail())
                    ->subject('Please Confirm your Email')
                    ->htmlTemplate('mail/confirmation_email_student.html.twig')
            );
            return $this->redirectToRoute('app_check_email');
        }

        return $this->render('registration/register_student.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/register/teacher', name: 'app_register_teacher')]
    public function registerTeacher(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager
    ): Response
    {
        $user = new Teacher();
        $form = $this->createForm(TeacherRegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
            $user->setCreatedAt(new DateTimeImmutable());

            $entityManager->persist($user);
            $entityManager->flush();

            // generate a signed url and email it to the user
            $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                (new TemplatedEmail())
                    ->from(new Address('aniastrzewiczek@gmail.com', 'Speak Up'))
                    ->to($user->getEmail())
                    ->subject('Please Confirm your Email')
                    ->htmlTemplate('mail/confirmation_email_teacher.html.twig')
            );

            return $this->redirectToRoute('app_check_email');
        }

        return $this->render('registration/register_teacher.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/register/check-email', name: 'app_check_email')]
    public function checkEmail(): Response
    {
        return $this->render('registration/check_email.html.twig');
    }


    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $this->getUser());
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $exception->getReason());

            return $this->redirectToRoute('app_login');
        }

        // @TODO Change the redirect on success and handle or remove the flash message in your templates
        $this->addFlash('success', 'Your email address has been verified.');

        return $this->redirectToRoute('app_login');
    }
}
