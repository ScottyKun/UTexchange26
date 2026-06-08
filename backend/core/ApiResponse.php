<?php

class ApiResponse
{
    public static function success(mixed $data = null, string $message = 'OK', int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    public static function error(string $message, int $code = 400, mixed $errors = null): void
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'message' => $message,
            'errors'  => $errors,
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    public static function paginated(array $data, int $total, int $page, int $perPage, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success'    => true,
            'data'       => $data,
            'pagination' => [
                'total'    => $total,
                'page'     => $page,
                'per_page' => $perPage,
                'pages'    => (int) ceil($total / max($perPage, 1)),
            ],
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}
