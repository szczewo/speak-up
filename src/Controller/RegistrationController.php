<?php

namespace App\Controller;

use App\DTO\RegisterUserRequest;
use App\Exception\EmailAlreadyInUseException;
use App\Handler\UserEmailVerificationHandler;
use App\Handler\UserRegistrationHandler;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Serializer\SerializerInterface;



class RegistrationController extends AbstractController
{

    public function __construct(
        private LoggerInterface $logger
    ){}


    /**
     * Handles user registration API endpoint
     *
     * @param Request $request
     * @param ValidatorInterface $validator
     * @param SerializerInterface $serializer
     * @param UserRegistrationHandler $handler
     * @return JsonResponse
     */
    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
    public function register(
        Request                 $request,
        ValidatorInterface      $validator,
        SerializerInterface     $serializer,
        UserRegistrationHandler $handler,
    ): JsonResponse
    {
        $dto = $serializer->deserialize(
            $request->getContent(),
            RegisterUserRequest::class,
            'json');
        $errors = $validator->validate($dto);

        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }
            return new JsonResponse([
                'status' => 'error',
                'code' => 'INVALID_PAYLOAD',
                'message' => 'Invalid request data format.',
                'errors' => $errorMessages
            ], Response::HTTP_BAD_REQUEST);
        }
        try {
            $handler->handle($dto);
        } catch (EmailAlreadyInUseException $e) {
            $this->logger->error('Email already in use.', [
                'exception' => $e,
                'message' => $e->getMessage(),
            ]);

            return new JsonResponse([
                'status' => 'error',
                'code' => 'EMAIL_ALREADY_IN_USE',
                'message' => 'Email already in use.',
                'errors' => ['email' => 'Email already in use.']
            ], Response::HTTP_CONFLICT);
        } catch (\Exception $e) {
            $this->logger->error('Registration failed', [
                'exception' => $e,
                'message' => $e->getMessage(),
            ]);

            return new JsonResponse([
                'status' => 'error',
                'code' => 'REGISTRATION_FAILED',
                'message' => 'User registration failed. Please try again later.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse([
            'status' => 'success',
            'message' => 'User registered successfully'
        ], Response::HTTP_CREATED);
    }

    /**
     * Handles email verification API endpoint
     *
     * @param Request $request
     * @param UserEmailVerificationHandler $handler
     * @return JsonResponse
     */
    #[Route('/api/verify/email', name: 'app_verify_email', methods: ['POST'])]
    public function verifyEmail(
        Request $request,
        UserEmailVerificationHandler $handler,
    ): JsonResponse
    {
        $data = json_decode($request->getContent());
        $token = $data->token ?? null;

        if (null === $token) {
            return new JsonResponse([
                'status' => 'error',
                'code' => 'MISSING_TOKEN',
                'message' => 'Missing verification token.'
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $handler->handle($token);
        } catch (\Exception $e) {
            $this->logger->error('Email verification failed', [
                'exception' => $e,
                'message' => $e->getMessage(),
            ]);

            return new JsonResponse([
                'status' => 'error',
                'code' => 'VERIFICATION_FAILED',
                'message' => 'Email verification failed.'
            ], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse([
            'status' => 'success',
            'message' => 'Email verified successfully.'
        ]);
    }

}

