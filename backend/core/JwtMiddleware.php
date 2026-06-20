<?php

require_once __DIR__ . '/JwtService.php';
require_once __DIR__ . '/ApiResponse.php';

class JwtMiddleware
{
    // Extrait et vérifie le Bearer token dans le header Authorization.
     
    public static function verify(): array
    {
        $headers     = getallheaders();
        $authHeader  = $headers['Authorization'] ?? $headers['authorization'] ?? '';

        // Vérifier présence du Bearer
        if (!str_starts_with($authHeader, 'Bearer ')) {
            ApiResponse::error('Token manquant', 401);
        }

        $token   = substr($authHeader, 7);
        $payload = JwtService::verify($token);

        if (empty($payload)) {
            ApiResponse::error('Token invalide ou expiré', 401);
        }

        if (($payload['type'] ?? 'user') === 'bi') {
            return $payload;
        }

        return $payload;
    }

    public static function requireRole(string $role): array
    {
        $payload = self::verify();

        if (($payload['role_name'] ?? '') !== $role) {
            ApiResponse::error('Accès refusé : permissions insuffisantes', 403);
        }

        return $payload;
    }

}
