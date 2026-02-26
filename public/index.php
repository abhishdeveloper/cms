<?php

session_start();

require_once __DIR__ . '/../autoload.php';

// Simple Router
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Remove trailing slash if not root
if ($requestUri !== '/' && substr($requestUri, -1) === '/') {
    $requestUri = rtrim($requestUri, '/');
}

// Basic Routing Logic
switch ($requestUri) {
    case '/':
        $controller = new HomeController();
        $controller->index();
        break;

    case '/cart':
        require_once __DIR__ . '/../views/cart.php';
        break;

    case '/success':
        require_once __DIR__ . '/../views/success.php';
        break;

    case '/api/login':
        if ($requestMethod === 'POST') {
            $controller = new AuthController();
            $controller->login();
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Method Not Allowed']);
        }
        break;

    case '/api/verify':
        if ($requestMethod === 'POST') {
            $controller = new AuthController();
            $controller->verify();
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Method Not Allowed']);
        }
        break;

    case '/api/track-cart':
        if ($requestMethod === 'POST') {
            $controller = new CartController();
            $controller->track();
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Method Not Allowed']);
        }
        break;

    case '/api/apply-coupon':
        if ($requestMethod === 'POST') {
            $controller = new CheckoutController();
            $controller->applyCoupon();
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Method Not Allowed']);
        }
        break;

    case '/checkout':
        if ($requestMethod === 'POST') {
            $controller = new CheckoutController();
            $controller->process();
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Method Not Allowed']);
        }
        break;

    case '/webhook':
        if ($requestMethod === 'POST') {
            require_once __DIR__ . '/webhook.php';
        } else {
            http_response_code(405);
            echo "Method Not Allowed";
        }
        break;

    default:
        http_response_code(404);
        echo "<h1>404 Not Found</h1>";
        break;
}
