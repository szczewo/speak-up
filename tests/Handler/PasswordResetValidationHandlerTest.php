<?php

namespace App\Tests\Handler;

use App\Entity\ResetPasswordRequest;
use App\Entity\Student;
use App\Handler\PasswordResetValidationHandler;
use App\Repository\ResetPasswordRequestRepository;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

class PasswordResetValidationHandlerTest extends TestCase
{
    private ResetPasswordRequestRepository $repo;
    private PasswordResetValidationHandler $validator;

    protected function setUp(): void
    {
        $this->repo = $this->createMock(ResetPasswordRequestRepository::class);
        $this->validator = new PasswordResetValidationHandler($this->repo);
    }

    /**
     * Tests that a valid token passes validation and returns the correct request.
     */
    #[Group('handler')]
    public function testValidTokenPassesValidation(): void
    {
        $selector = 'selector-123';
        $plainToken = 'good-token';
        $hashed = password_hash($plainToken, PASSWORD_ARGON2ID);

        $user = (new Student())->setEmail('student@example.com');

        $request = new ResetPasswordRequest(
            user: $user,
            hashedToken: $hashed,
            selector: $selector,
            expiresAt: new \DateTimeImmutable('+1 hour')
        );

        $this->repo->expects($this->once())
            ->method('findOneBySelector')
            ->with($selector)
            ->willReturn($request);

        $result = $this->validator->validate($selector, $plainToken);

        $this->assertSame($request, $result);
    }

    /**
     * Tests that an invalid selector throws an InvalidArgumentException.
     */
    #[Group('handler')]
    public function testInvalidSelectorThrowsException(): void
    {
        $this->repo->method('findOneBySelector')->willReturn(null);

        $this->expectException(InvalidArgumentException::class);

        $this->validator->validate('missing', 'token');
    }

    /**
     * Tests that an expired token throws an InvalidArgumentException.
     */
    #[Group('handler')]
    public function testExpiredTokenThrowsException(): void
    {
        $selector = 'selector-123';
        $token = 'expired-token';
        $hashed = password_hash($token, PASSWORD_ARGON2ID);

        $user = new Student();

        $request = new ResetPasswordRequest(
            user: $user,
            hashedToken: $hashed,
            selector: $selector,
            expiresAt: new \DateTimeImmutable('-1 hour')
        );

        $this->repo->method('findOneBySelector')->willReturn($request);

        $this->expectException(InvalidArgumentException::class);
        $this->validator->validate($selector, $token);
    }

    /**
     * Tests that a wrong token throws an InvalidArgumentException.
     */
    #[Group('handler')]
    public function testWrongTokenThrowsException(): void
    {
        $selector = 'selector-123';
        $correct = 'secret';
        $wrong = 'bad-token';

        $hashed = password_hash($correct, PASSWORD_ARGON2ID);

        $user = new Student();

        $request = new ResetPasswordRequest(
            user: $user,
            hashedToken: $hashed,
            selector: $selector,
            expiresAt: new \DateTimeImmutable('+1 hour')
        );

        $this->repo->method('findOneBySelector')->willReturn($request);

        $this->expectException(InvalidArgumentException::class);
        $this->validator->validate($selector, $wrong);
    }
}
