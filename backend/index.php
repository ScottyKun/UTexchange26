<?php

session_start();


ini_set('display_errors', '0');
error_reporting(E_ALL);

require_once __DIR__ . '/vendor/autoload.php';


$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$angularUrl = $_ENV['ANGULAR_URL'] ?? 'http://localhost:4200';
header("Access-Control-Allow-Origin: $angularUrl");
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');


if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

LoggingMiddleware::handle();


$dispatcher = FastRoute\simpleDispatcher(require __DIR__ . '/routes.php');

$uri    = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

$route = $dispatcher->dispatch($method, $uri);

switch ($route[0]) {

    case FastRoute\Dispatcher::NOT_FOUND:
        ApiResponse::error('Route introuvable', 404);
        break;

    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        ApiResponse::error(
            'Méthode non autorisée. Acceptées : ' . implode(', ', $route[1]),
            405
        );
        break;

    case FastRoute\Dispatcher::FOUND:
        [, [$controllerClass, $action], $vars] = $route;

        if (!class_exists($controllerClass)) {
            ApiResponse::error("Controller '$controllerClass' introuvable", 500);
        }

        $controller = new $controllerClass();

        if (!method_exists($controller, $action)) {
            ApiResponse::error("Action '$action' introuvable sur $controllerClass", 500);
        }

        $controller->$action(...array_values($vars));
        break;
}