<?php

namespace App\Service;

interface TokenGeneratorInterface
{
    /**
     * Generate a secure random token.
     */
    public function generateToken(int $length = 32): string;

    /**
     * Generate a token with expiration timestamp.
     */
    public function generateExpiringToken(int $length = 32, int $ttlSeconds = 3600): array;


}