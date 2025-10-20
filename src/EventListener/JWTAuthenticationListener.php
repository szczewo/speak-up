<?php

namespace App\EventListener;

use App\Entity\Student;
use App\Entity\Teacher;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationFailureEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserInterface;

class JWTAuthenticationListener
{
    /**
     * @param AuthenticationSuccessEvent $event
     * @return void
     */
    public function onAuthenticationSuccess(AuthenticationSuccessEvent $event): void
    {
        $user = $event->getUser();

        if (!$user instanceof UserInterface) {
            return;
        }
        $type = null;
        if ($user instanceof Student){
            $type = 'student';
        } elseif ($user instanceof Teacher){
            $type = 'teacher';
        } else {
            $type = 'user';
        }

        $userData = [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'name' => $user->getName(),
            'lastName' => $user->getLastName(),
            'type' => $type,
        ];

        $data = $event->getData();
        $data['user'] = $userData;
        $event->setData($data);
    }

    public function onAuthenticationFailure(AuthenticationFailureEvent $event): void
    {
        $exception = $event->getException();
        $message = 'Authentication failed.';

        if ($exception instanceof BadCredentialsException){
            $message = 'Invalid credentials';
        } elseif ($exception instanceof CustomUserMessageAccountStatusException){
           $message = $exception->getMessageKey();
        }

        $response = new JsonResponse(['error' => $message], JsonResponse::HTTP_UNAUTHORIZED);
        $event->setResponse($response);
    }
}
