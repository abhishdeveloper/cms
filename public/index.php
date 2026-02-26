<?php

session_start();

require_once __DIR__ . '/../autoload.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

// Simple Router
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Remove trailing slash if not root
if ($requestUri !== '/' && substr($requestUri, -1) === '/') {
    $requestUri = rtrim($requestUri, '/');
}

// API V1 Routing
if (strpos($requestUri, '/api/v1/') === 0) {
    $path = substr($requestUri, 7); // Remove /api/v1

    // Auth Routes
    if ($path === '/login' && $requestMethod === 'POST') {
        $controller = new AuthApiController();
        $controller->login();
        exit;
    }
    if ($path === '/register' && $requestMethod === 'POST') {
        $controller = new AuthApiController();
        $controller->register();
        exit;
    }

    // Product Routes
    if ($path === '/products' && $requestMethod === 'GET') {
        $controller = new ProductApiController();
        $controller->index();
        exit;
    }
    if (preg_match('#^/products/(\d+)$#', $path, $matches) && $requestMethod === 'GET') {
        $controller = new ProductApiController();
        $controller->show($matches[1]);
        exit;
    }

    // Checkout Routes
    if ($path === '/checkout/initiate' && $requestMethod === 'POST') {
        $controller = new CheckoutApiController();
        $controller->initiate();
        exit;
    }

    http_response_code(404);
    echo json_encode(['error' => 'API endpoint not found']);
    exit;
}

// Basic Routing Logic
switch ($requestUri) {
    case '/':
        $controller = new HomeController();
        $controller->index();
        break;

    case '/product':
        $controller = new ProductController();
        $controller->show();
        break;

    case '/product/review':
        if ($requestMethod === 'POST') {
            $controller = new ProductController();
            $controller->storeReview();
        } else {
            http_response_code(405);
            echo "Method Not Allowed";
        }
        break;

    case '/webhook/whatsapp':
        if ($requestMethod === 'POST') {
            require_once __DIR__ . '/whatsapp_reply_webhook.php';
        } else {
            http_response_code(405);
            echo "Method Not Allowed";
        }
        break;

    case '/search':
        $controller = new SearchController();
        $controller->index();
        break;

    case '/category':
        $controller = new SearchController();
        $controller->category();
        break;

    case '/quote/submit':
        if ($requestMethod === 'POST') {
            $controller = new QuotationController();
            $controller->submit();
        } else {
            http_response_code(405);
            echo "Method Not Allowed";
        }
        break;

    case '/cart':
        require_once __DIR__ . '/../views/cart.php';
        break;

    case '/success':
        require_once __DIR__ . '/../views/success.php';
        break;

    case '/login':
        if ($requestMethod === 'GET') {
            require_once __DIR__ . '/../views/login.php';
        }
        break;

    case '/logout':
        $controller = new AuthController();
        $controller->logout();
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

    case '/api/login-password':
        if ($requestMethod === 'POST') {
            $controller = new AuthController();
            $controller->loginPassword();
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

    // Admin Routes
    case '/admin/dashboard':
        $controller = new AdminController();
        $controller->dashboard();
        break;
    case '/admin/categories':
        $controller = new AdminController();
        $controller->categories();
        break;
    case '/admin/categories/store':
        $controller = new AdminController();
        $controller->storeCategory();
        break;
    case '/admin/categories/delete':
        $controller = new AdminController();
        $controller->deleteCategory();
        break;
    case '/admin/products':
        $controller = new AdminController();
        $controller->products();
        break;
    case '/admin/products/store':
        $controller = new AdminController();
        $controller->storeProduct();
        break;
    case '/admin/products/delete':
        $controller = new AdminController();
        $controller->deleteProduct();
        break;
    case '/admin/orders':
        $controller = new AdminController();
        $controller->orders();
        break;
    case '/admin/orders/view':
        $controller = new AdminController();
        $controller->orderDetails();
        break;
    case '/admin/orders/update-status':
        $controller = new AdminController();
        $controller->updateOrderStatus();
        break;
    case '/admin/quotations':
        $controller = new AdminController();
        $controller->quotations();
        break;
    case '/admin/quotations/update':
        $controller = new AdminController();
        $controller->updateQuotationStatus();
        break;
    case '/admin/coupons':
        $controller = new AdminController();
        $controller->coupons();
        break;
    case '/admin/coupons/store':
        $controller = new AdminController();
        $controller->storeCoupon();
        break;
    case '/admin/coupons/delete':
        $controller = new AdminController();
        $controller->deleteCoupon();
        break;

    // User Routes
    case '/user/orders':
        $controller = new UserController();
        $controller->orders();
        break;
    case '/user/profile':
        $controller = new UserController();
        $controller->profile();
        break;
    case '/user/profile/update':
        $controller = new UserController();
        $controller->updateProfile();
        break;

    default:
        http_response_code(404);
        echo "<h1>404 Not Found</h1>";
        break;
}
