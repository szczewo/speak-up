<?php

namespace App\Tests\Handler;

use App\DTO\ResetPassword;
use App\Entity\ResetPasswordRequest;
use App\Entity\Student;
use App\Handler\PasswordResetFinalHandler;
use App\Handler\PasswordResetValidationHandler;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class PasswordResetFinalHandlerTest extends TestCase
{
    /**
     * Tests that a valid password reset request successfully resets the password and removes the request.
     */
    #[Group('handler')]
    public function testPasswordIsResetAndResetRequestIsRemoved(): void
    {
        $dto = new ResetPassword(
            selector: 'selector123',
            token: 'token123',
            password: 'NewPassword123!',
            passwordConfirmation: 'NewPassword123!'
        );

        $user = (new Student())->setEmail('student@example.com');

        $resetRequest = $this->getMockBuilder(ResetPasswordRequest::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getUser'])
            ->getMock();

        $resetRequest->method('getUser')->willReturn($user);

        $validator = $this->createMock(PasswordResetValidationHandler::class);
        $validator->expects($this->once())
            ->method('validate')
            ->with($dto->selector, $dto->token)
            ->willReturn($resetRequest);

        $hasher = $this->createMock(UserPasswordHasherInterface::class);
        $hasher->expects($this->once())
            ->method('hashPassword')
            ->with($user, $dto->password)
            ->willReturn('hashed-password');

        $em = $this->createMock(EntityManagerInterface::class);

        $em->expects($this->once())
            ->method('remove')
            ->with($resetRequest);

        $em->expects($this->once())
            ->method('flush');

        $handler = new PasswordResetFinalHandler(
            em: $em,
            validator: $validator,
            hasher: $hasher
        );

        $handler->handle($dto);

        $this->assertSame('hashed-password', $user->getPassword());
    }

    /**
     * Tests that an invalid token during validation throws an InvalidArgumentException.
     */
    #[Group('handler')]
    public function testInvalidTokenThrowsException(): void
    {
        $dto = new ResetPassword(
            selector: 'selector123',
            token: 'invalid-token',
            password: 'NewPassword123!',
            passwordConfirmation: 'NewPassword123!'
        );

        $validator = $this->createMock(PasswordResetValidationHandler::class);

        $validator->expects($this->once())
            ->method('validate')
            ->with($dto->selector, $dto->token)
            ->willThrowException(new InvalidArgumentException('Invalid reset password token.'));

        $hasher = $this->createMock(UserPasswordHasherInterface::class);

        $em = $this->createMock(EntityManagerInterface::class);

        $handler = new PasswordResetFinalHandler(
            em: $em,
            validator: $validator,
            hasher: $hasher
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid reset password token.');

        $handler->handle($dto);
    }

    /**
     * Tests that a failure during flush throws a RuntimeException.
     */
    #[Group('handler')]
    public function testThrowsRuntimeExceptionOnFlushFailure(): void
    {
       $dto = new ResetPassword(
            selector: 'selector123',
            token: 'token123',
            password: 'NewPassword123!',
            passwordConfirmation: 'NewPassword123!'
        );
        $user = (new Student())->setEmail('student@example.com');

        $resetRequest = $this->getMockBuilder(ResetPasswordRequest::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getUser'])
            ->getMock();
        $resetRequest->method('getUser')->willReturn($user);

        $validator = $this->createMock(PasswordResetValidationHandler::class);
        $validator->method('validate')->willReturn($resetRequest);

        $hasher = $this->createMock(UserPasswordHasherInterface::class);
        $hasher->method('hashPassword')->willReturn('hashed-password');

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('remove')->with($resetRequest);
        $em->expects($this->once())
            ->method('flush')
            ->willThrowException(new \Exception('DB error'));

        $handler = new PasswordResetFinalHandler(
            em: $em,
            validator: $validator,
            hasher: $hasher
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed to reset password: DB error');

        $handler->handle($dto);
    }




}
