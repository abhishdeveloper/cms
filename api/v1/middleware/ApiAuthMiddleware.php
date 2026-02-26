<?php

class ApiAuthMiddleware
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function authenticate()
    {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';

        if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized: Missing or invalid token']);
            exit;
        }

        $token = $matches[1];

        $stmt = $this->db->query("SELECT id, name, phone, email, role FROM users WHERE api_token = ?", [$token]);
        $user = $stmt->fetch();

        if (!$user) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized: Invalid token']);
            exit;
        }

        return $user;
    }
}
