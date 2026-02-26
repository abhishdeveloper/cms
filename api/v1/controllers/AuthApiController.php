<?php

class AuthApiController
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function login()
    {
        header('Content-Type: application/json');

        $input = json_decode(file_get_contents('php://input'), true);
        $phone = $input['phone'] ?? '';
        $password = $input['password'] ?? '';

        if (empty($phone) || empty($password)) {
            http_response_code(400);
            echo json_encode(['error' => 'Phone and password are required']);
            return;
        }

        $stmt = $this->db->query("SELECT * FROM users WHERE phone = ?", [$phone]);
        $user = $stmt->fetch();

        if ($user && $user['password_hash'] && password_verify($password, $user['password_hash'])) {
            // Generate secure token
            $token = bin2hex(random_bytes(32));

            // Save to DB
            $this->db->query("UPDATE users SET api_token = ? WHERE id = ?", [$token, $user['id']]);

            echo json_encode([
                'success' => true,
                'token' => $token,
                'user' => [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'phone' => $user['phone'],
                    'email' => $user['email'],
                    'role' => $user['role']
                ]
            ]);
        } else {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid credentials']);
        }
    }

    public function register()
    {
        header('Content-Type: application/json');

        $input = json_decode(file_get_contents('php://input'), true);
        $name = $input['name'] ?? '';
        $phone = $input['phone'] ?? '';
        $password = $input['password'] ?? '';
        $email = $input['email'] ?? null;

        if (empty($name) || empty($phone) || empty($password)) {
            http_response_code(400);
            echo json_encode(['error' => 'Name, phone, and password are required']);
            return;
        }

        // Check if exists
        $stmt = $this->db->query("SELECT id FROM users WHERE phone = ?", [$phone]);
        if ($stmt->fetch()) {
            http_response_code(400);
            echo json_encode(['error' => 'User already exists']);
            return;
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $token = bin2hex(random_bytes(32));

        try {
            $this->db->query(
                "INSERT INTO users (name, phone, email, password_hash, api_token, is_verified, role) VALUES (?, ?, ?, ?, ?, 1, 'customer')",
                [$name, $phone, $email, $hash, $token]
            );
            $userId = $this->db->getConnection()->lastInsertId();

            echo json_encode([
                'success' => true,
                'token' => $token,
                'user' => [
                    'id' => $userId,
                    'name' => $name,
                    'phone' => $phone,
                    'email' => $email,
                    'role' => 'customer'
                ]
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Registration failed: ' . $e->getMessage()]);
        }
    }
}
