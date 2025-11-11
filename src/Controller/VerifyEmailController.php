<?php

namespace App\Controller;



use App\Handler\UserEmailVerificationHandler;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
class VerifyEmailController extends AbstractController
{

    public function __construct(
        private LoggerInterface $logger
    ){}

    /**
     * Handles email verification API endpoint
     *
     * @param Request $request
     * @param UserEmailVerificationHandler $handler
     * @return JsonResponse
     */
    #[Route('/api/verify-email', name: 'app_verify_email', methods: ['POST'])]
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

