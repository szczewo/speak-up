<?php

namespace App\Entity;

use App\Repository\ResetPasswordRequestRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordRequestInterface;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordRequestTrait;

#[ORM\Entity(repositoryClass: ResetPasswordRequestRepository::class)]
class ResetPasswordRequest
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;


    #[ORM\Column(type: 'string', length: 255)]
    private string $hashedToken;

    #[ORM\Column(type: 'string', length: 255)]
    private string $selector;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $expiresAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    private ?string $plainToken = null;

    public function __construct(
        User $user,
        string $hashedToken,
        string $selector,
        DateTimeImmutable $expiresAt,
    )
    {
        $this->user = $user;
        $this->hashedToken = $hashedToken;
        $this->selector = $selector;
        $this->expiresAt = $expiresAt;
        $this->createdAt = new DateTimeImmutable();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getSelector(): string
    {
        return $this->selector;
    }

    public function getHashedToken(): string
    {
        return $this->hashedToken;
    }

    public function isExpired(): bool
    {
        return $this->expiresAt <= new DateTimeImmutable();
    }

    public function setPlainToken(string $plainToken) : self
    {
        $this->plainToken = $plainToken;
        return $this;
    }

    public function getPlainToken(): ?string
    {
        return $this->plainToken;
    }

    public function getExpiresAt() : DateTimeImmutable
    {
        return $this->expiresAt;
    }
}
