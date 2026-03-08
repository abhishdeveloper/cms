<?php

class AuthController
{
    private $db;
    private $whatsapp;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->whatsapp = new WhatsAppService();
    }

    public function login()
    {
        header('Content-Type: application/json');

        $input = json_decode(file_get_contents('php://input'), true);
        $phone = $input['phone'] ?? '';

        if (empty($phone)) {
            http_response_code(400);
            echo json_encode(['error' => 'Phone number is required']);
            return;
        }

        try {
            // Generate and send OTP
            $this->whatsapp->sendOTP($phone);

            echo json_encode(['status' => 'success', 'message' => 'OTP sent successfully']);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to send OTP: ' . $e->getMessage()]);
        }
    }

    public function verify()
    {
        header('Content-Type: application/json');

        $input = json_decode(file_get_contents('php://input'), true);
        $phone = $input['phone'] ?? '';
        $otp = $input['otp'] ?? '';
        $name = $input['name'] ?? 'Guest User'; // Default name if not provided

        if (empty($phone) || empty($otp)) {
            http_response_code(400);
            echo json_encode(['error' => 'Phone and OTP are required']);
            return;
        }

        // Verify OTP from session
        if (!isset($_SESSION["otp_{$phone}"]) || $_SESSION["otp_{$phone}"] != $otp) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid or expired OTP']);
            return;
        }

        try {
            $conn = $this->db->getConnection();

            // Check if user exists
            $stmt = $this->db->query("SELECT * FROM users WHERE phone = ?", [$phone]);
            $user = $stmt->fetch();

            if ($user) {
                $userId = $user['id'];
                $role = $user['role'];
            } else {
                // Create new user (default customer)
                $this->db->query("INSERT INTO users (phone, name, is_verified, role) VALUES (?, ?, 1, 'customer')", [$phone, $name]);
                $userId = $conn->lastInsertId();
                $role = 'customer';
            }

            // Set session
            $_SESSION['user_id'] = $userId;
            $_SESSION['user_phone'] = $phone;
            $_SESSION['role'] = $role;

            // Clear OTP
            unset($_SESSION["otp_{$phone}"]);

            echo json_encode(['status' => 'success', 'user_id' => $userId, 'role' => $role]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
        }
    }

    public function loginPassword()
    {
        header('Content-Type: application/json');

        $input = json_decode(file_get_contents('php://input'), true);
        $email = $input['email'] ?? '';
        $password = $input['password'] ?? '';

        if (empty($email) || empty($password)) {
            http_response_code(400);
            echo json_encode(['error' => 'Email and password are required']);
            return;
        }

        try {
            $stmt = $this->db->query("SELECT * FROM users WHERE email = ?", [$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_phone'] = $user['phone'];
                $_SESSION['role'] = $user['role'];

                echo json_encode(['status' => 'success', 'redirect' => $user['role'] === 'admin' ? '/admin/dashboard' : '/']);
            } else {
                http_response_code(401);
                echo json_encode(['error' => 'Invalid credentials']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function logout()
    {
        session_destroy();
        header('Location: /');
        exit;
    }
}
