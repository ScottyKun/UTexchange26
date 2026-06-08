<?php

require_once __DIR__ . '/ApiResponse.php';
require_once __DIR__ . '/JwtMiddleware.php';

abstract class BaseApiController
{
    protected function getBody(): array
    {
        $raw = file_get_contents('php://input');
        if (empty($raw)) return [];

        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }

    protected function getQuery(): array
    {
        return $_GET ?? [];
    }

    protected function requireAuth(): array
    {
        return JwtMiddleware::verify();
    }

    protected function requireAdmin(): array
    {
        return JwtMiddleware::requireRole('Administrateur');
    }

    protected function getMethod(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD']);
    }

    protected function validateRequired(array $data, array $fields): array
    {
        $missing = [];
        foreach ($fields as $field) {
            if (!isset($data[$field]) || $data[$field] === '') {
                $missing[] = $field;
            }
        }
        return $missing;
    }

    protected function tryAuth(): ?array
    {
        $headers    = getallheaders();
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
        if (!str_starts_with($authHeader, 'Bearer ')) return null;
        $payload = JwtService::verify(substr($authHeader, 7));
        return empty($payload) ? null : $payload;
    }
}
