<?php
class LoggingMiddleware
{
    public static function handle(): void
    {
        // Routes à ne pas logger
        $ignoredPrefixes = ['/api/stats/annonces'];

        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

        foreach ($ignoredPrefixes as $prefix) {
            if (str_starts_with($uri, $prefix)) {
                return;
            }
        }

        $userId = self::extractUserId();
        $method = $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN';

        // Construire un label d'action lisible depuis méthode + URI
        $action = self::buildAction($method, $uri);

        ActivityLogService::log($action, $userId, [
            'method' => $method,
            'uri'    => $uri,
        ]);
    }

    //Helpers

    //Tente d'extraire le user_id depuis le Bearer token (sans bloquer).
    private static function extractUserId(): ?int
    {
        try {
            $headers    = getallheaders();
            $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';

            if (!str_starts_with($authHeader, 'Bearer ')) {
                return null;
            }

            $payload = JwtService::verify(substr($authHeader, 7));
            return isset($payload['user_id']) ? (int) $payload['user_id'] : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    //Construit un label d'action depuis la méthode HTTP et l'URI.
    private static function buildAction(string $method, string $uri): string
    {
        // Normaliser l'URI : remplacer les IDs numériques par {id}
        $normalized = preg_replace('/\/\d+/', '/{id}', $uri);
        return $method . ' ' . $normalized;
    }
}