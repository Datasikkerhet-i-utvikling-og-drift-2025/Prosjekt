<?php

namespace managers;

use helpers\Logger;
use Exception;
use JsonException;

/**
 * Class JWTManager
 * Handles secure generation and validation of JWT tokens using HMAC (HS256).
 */
class JWTManager
{
    private string $secretKey;
    private string $algorithm = 'HS256';
    private int $tokenLifetime = 3600; // 1 hour
    private int $clockSkew = 60; // Allow 60 seconds clock skew
    private string $issuer = 'secure-feedback-app';
    private string $audience = 'secure-feedback-users';

    public function __construct()
    {
        $this->secretKey = $_ENV['JWT_SECRET'] ?? 'CHANGE_THIS_SECRET_IN_PRODUCTION';
    }

    /**
     * Generates a JWT token with secure claims.
     *
     * @param array $customPayload Additional claims to add to the token.
     * @return string Encoded JWT token.
     * @throws Exception
     */
    public function generateToken(array $customPayload): string
    {
        $header = [
            'alg' => $this->algorithm,
            'typ' => 'JWT'
        ];

        $issuedAt = time();
        $notBefore = $issuedAt - 10; // allow 10s tolerance before valid
        $expiresAt = $issuedAt + $this->tokenLifetime;
        $jwtId = bin2hex(random_bytes(16));

        $payload = array_merge($customPayload, [
            'iss' => $this->issuer,
            'aud' => $this->audience,
            'iat' => $issuedAt,
            'nbf' => $notBefore,
            'exp' => $expiresAt,
            'jti' => $jwtId
        ]);

        $segments = [
            $this->base64UrlEncode(json_encode($header, JSON_THROW_ON_ERROR)),
            $this->base64UrlEncode(json_encode($payload, JSON_THROW_ON_ERROR))
        ];

        $signature = hash_hmac('sha256', implode('.', $segments), $this->secretKey, true);
        $segments[] = $this->base64UrlEncode($signature);

        return implode('.', $segments);
    }

    /**
     * Validates a JWT token and returns its payload if valid.
     *
     * @param string $token
     * @return array|null Payload if valid, otherwise null.
     * @throws JsonException
     */
    public function validateToken(string $token): ?array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            Logger::warning("JWT structure invalid.");
            return null;
        }

        [$encodedHeader, $encodedPayload, $encodedSignature] = $parts;

        $header = json_decode($this->base64UrlDecode($encodedHeader), true, 512, JSON_THROW_ON_ERROR);
        $payload = json_decode($this->base64UrlDecode($encodedPayload), true, 512, JSON_THROW_ON_ERROR);
        $signature = $this->base64UrlDecode($encodedSignature);

        if (!is_array($header) || !is_array($payload)) {
            Logger::warning("JWT decode failure.");
            return null;
        }

        // Verify algorithm
        if (($header['alg'] ?? '') !== $this->algorithm) {
            Logger::warning("JWT algorithm mismatch.");
            return null;
        }

        // Recalculate and verify signature (constant time comparison)
        $expectedSignature = hash_hmac('sha256', "$encodedHeader.$encodedPayload", $this->secretKey, true);
        if (!hash_equals($expectedSignature, $signature)) {
            Logger::warning("JWT signature mismatch.");
            return null;
        }

        // Validate claims
        $now = time();
        if (
            ($payload['nbf'] ?? 0) > $now + $this->clockSkew ||
            ($payload['iat'] ?? 0) > $now + $this->clockSkew ||
            ($payload['exp'] ?? 0) < $now - $this->clockSkew
        ) {
            Logger::warning("JWT timing claim violation (nbf, iat, or exp).");
            return null;
        }

        if (($payload['iss'] ?? '') !== $this->issuer) {
            Logger::warning("JWT issuer mismatch.");
            return null;
        }

        if (($payload['aud'] ?? '') !== $this->audience) {
            Logger::warning("JWT audience mismatch.");
            return null;
        }

        return $payload;
    }

    /**
     * Encodes data using base64 URL-safe encoding.
     *
     * @param string $data
     * @return string
     */
    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Decodes base64 URL-safe encoded data.
     *
     * @param string $data
     * @return string
     */
    private function base64UrlDecode(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/'));
    }
}
