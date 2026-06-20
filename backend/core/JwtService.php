<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;

class JwtService
{
    public static function generate(array $payload): string
    {
        $secret = $_ENV['JWT_SECRET'] ?? 'fallback_secret';
        $expiry = (int) ($_ENV['JWT_EXPIRY'] ?? 3600);
        $now    = time();

        $payload = array_merge($payload, [
            'iat' => $now,            // émis à
            'exp' => $now + $expiry,  // expire à
            'type' => $payload['type'] ?? 'user'
        ]);

        return JWT::encode($payload, $secret, 'HS256');
    }

    public static function verify(string $token): array
    {
        $secret = $_ENV['JWT_SECRET'] ?? 'fallback_secret';

        try {
            $decoded = JWT::decode($token, new Key($secret, 'HS256'));
            return (array) $decoded;
        } catch (ExpiredException $e) {
            return [];
        } catch (SignatureInvalidException $e) {
            return [];
        } catch (Exception $e) {
            return [];
        }
    }

    public static function isValid(string $token): bool
    {
        return !empty(self::verify($token));
    }
}
