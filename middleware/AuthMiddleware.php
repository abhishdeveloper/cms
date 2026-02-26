<?php

class AuthMiddleware
{
    public static function requireLogin()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
    }

    public static function requireAdmin()
    {
        self::requireLogin();

        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            http_response_code(403);
            echo "<h1>403 Forbidden</h1>";
            exit;
        }
    }
}
