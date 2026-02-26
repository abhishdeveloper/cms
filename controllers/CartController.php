<?php

class CartController
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function track()
    {
        header('Content-Type: application/json');

        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'User not authenticated']);
            return;
        }

        $userId = $_SESSION['user_id'];
        $input = json_decode(file_get_contents('php://input'), true);

        if (!isset($input['cart_items']) || empty($input['cart_items'])) {
            // If cart is empty, maybe we should mark existing as converted or deleted?
            // For now, let's just ignore empty payloads
            echo json_encode(['status' => 'ignored']);
            return;
        }

        $cartData = json_encode($input['cart_items']);

        try {
            // Check if there's a pending abandoned cart for this user
            $stmt = $this->db->query("SELECT id FROM abandoned_carts WHERE user_id = ? AND status = 'pending'", [$userId]);
            $existing = $stmt->fetch();

            if ($existing) {
                // Update existing
                $this->db->query("UPDATE abandoned_carts SET cart_data = ?, updated_at = NOW() WHERE id = ?", [$cartData, $existing['id']]);
            } else {
                // Create new
                $this->db->query("INSERT INTO abandoned_carts (user_id, cart_data, status) VALUES (?, ?, 'pending')", [$userId, $cartData]);
            }

            echo json_encode(['status' => 'tracked']);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}
