<?php

class UserController
{
    private $db;

    public function __construct()
    {
        AuthMiddleware::requireLogin();
        $this->db = Database::getInstance();
    }

    public function orders()
    {
        $userId = $_SESSION['user_id'];
        $stmt = $this->db->query("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC", [$userId]);
        $orders = $stmt->fetchAll();
        require __DIR__ . '/../views/user/orders.php';
    }

    public function profile()
    {
        $userId = $_SESSION['user_id'];
        $stmt = $this->db->query("SELECT * FROM users WHERE id = ?", [$userId]);
        $user = $stmt->fetch();
        require __DIR__ . '/../views/user/profile.php';
    }

    public function updateProfile()
    {
        $userId = $_SESSION['user_id'];
        $name = $_POST['name'];
        $phone = $_POST['phone'];
        $email = $_POST['email'];

        $this->db->query("UPDATE users SET name = ?, phone = ?, email = ? WHERE id = ?", [$name, $phone, $email, $userId]);

        // Update session phone if changed
        $_SESSION['user_phone'] = $phone;

        header('Location: /user/profile');
    }
}
