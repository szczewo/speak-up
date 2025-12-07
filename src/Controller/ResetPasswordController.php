<?php

namespace App\Controller;


use App\DTO\ResetPassword;
use App\Handler\PasswordResetFinalHandler;
use App\Handler\PasswordResetRequestHandler;
use App\Handler\PasswordResetValidationHandler;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Exception\InvalidArgumentException;
use Symfony\Component\Validator\Validator\ValidatorInterface;


class ResetPasswordController extends AbstractController
{
    public function __construct(
        private LoggerInterface $logger
    ){}

    /**
     * Handles password reset request API endpoint
     *
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('/api/request-reset-password', name: 'api_request_reset_password', methods: ['POST'])]
    public function requestResetPassword(
        Request                     $request,
        PasswordResetRequestHandler $handler,
    ): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $email = $data['email'] ?? null;

        try {
            $handler->handle($email);
        } catch (InvalidArgumentException $e) {
            $this->logger->info('Password reset requested for non-existing email.', [
                'email' => $email,
            ]);
        } catch (Exception $e) {
            $this->logger->error('Password reset request failed', [
                'exception' => $e,
                'message' => $e->getMessage(),
            ]);
        }

        return new JsonResponse([
            'status' => 'success',
            'message' => 'If an account with that email exists, a password reset link has been sent.'
        ], Response::HTTP_OK);
    }

    #[Route('/api/validate-reset-token', name: 'api_validate_reset_token', methods: ['GET'])]
    public function validateToken(
        Request $request,
        PasswordResetValidationHandler $validator,
    ) : JsonResponse
    {
        $token = $request->get('token');
        $selector = $request->get('selector');

        if (!$token || !$selector) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Missing token or selector.'
            ], Response::HTTP_BAD_REQUEST);
        }

        try {

            $resetPasswordRequest = $validator->validate(
                $selector,
                $token
            );

        } catch (InvalidArgumentException $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Invalid or expired token.'
            ], Response::HTTP_BAD_REQUEST);

        }

         return new JsonResponse([
             'status' => 'success',
             'message' => 'Token is valid.'
         ], Response::HTTP_OK);
    }


    #[Route('/api/reset-password', name: 'api_reset_password', methods: ['POST'])]
    public function resetPassword(
        Request  $request,
        SerializerInterface  $serializer,
        PasswordResetFinalHandler $handler,
        ValidatorInterface $validator,
    ) : JsonResponse
    {
        try{
            $dto = $serializer->deserialize(
                $request->getContent(),
                ResetPassword::class,
                'json');
        } catch (InvalidArgumentException | \ValueError $e) {
            return new JsonResponse([
                'status' => 'error',
                'code' => 'INVALID_PAYLOAD',
                'message' => 'Invalid request data format.'
            ], Response::HTTP_BAD_REQUEST);
        }

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
        } catch (InvalidArgumentException $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Invalid reset password request: ' . $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        } catch (Exception $e) {
            $this->logger->error('Password reset failed', [
                'exception' => $e,
                'message' => $e->getMessage(),
            ]);
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Failed to reset password.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        return new JsonResponse([
            'status' => 'success',
            'message' => 'Password has been reset successfully.'
        ], Response::HTTP_OK);
    }

}

