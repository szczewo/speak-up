<?php

namespace App\Controller;


use App\Handler\PasswordResetHandler;
use Exception;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;



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
        Request  $request,
        PasswordResetHandler $handler,
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

}

