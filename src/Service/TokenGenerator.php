<?php

namespace App\Service;

use Exception;

/**
 * Service for generating secure tokens.
 */
class TokenGenerator implements TokenGeneratorInterface
{

    /**
     * @param int $length
     * @return string
     * @throws Exception
     */
    public function generateToken(int $length = 32): string
    {
        return bin2hex(random_bytes($length));
    }

    /**
     * @throws Exception
     */
    public function generateExpiringToken(int $length = 32, int $ttlSeconds = 3600): array
    {
        $token = $this->generateToken($length);
        $expiresAt = (new \DateTimeImmutable())->modify("+{$ttlSeconds} seconds");

        return [
            'token' => $token,
            'expiresAt' => $expiresAt,
        ];
    }

    public function generateSelector(int $length = 16): string
    {
        return bin2hex(random_bytes($length));
    }
}