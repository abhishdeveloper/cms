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

        if (!isset($input['cart_items']) || !is_array($input['cart_items'])) {
            echo json_encode(['status' => 'ignored']);
            return;
        }

        $cartItems = $input['cart_items'];
        $totalAmount = 0;

        try {
            $conn = $this->db->getConnection();
            $conn->beginTransaction();

            // Check for existing DRAFT order
            $stmt = $this->db->query(
                "SELECT id FROM orders WHERE user_id = ? AND order_status = 'draft'",
                [$userId]
            );
            $existingOrder = $stmt->fetch();

            if ($existingOrder) {
                $orderId = $existingOrder['id'];

                // Clear existing items for this draft order
                $this->db->query("DELETE FROM order_items WHERE order_id = ?", [$orderId]);
            } else {
                // Create new DRAFT order
                $this->db->query(
                    "INSERT INTO orders (user_id, total_amount, payment_status, order_status, created_at, updated_at)
                     VALUES (?, 0, 'pending', 'draft', NOW(), NOW())",
                    [$userId]
                );
                $orderId = $conn->lastInsertId();
            }

            // Insert new items and calculate total
            foreach ($cartItems as $item) {
                $productId = (int)$item['product_id'];
                $quantity = (int)$item['quantity'];

                // Validate product exists and get price
                $prodStmt = $this->db->query("SELECT price FROM products WHERE id = ?", [$productId]);
                $product = $prodStmt->fetch();

                if ($product) {
                    $price = $product['price'];
                    $lineTotal = $price * $quantity;
                    $totalAmount += $lineTotal;

                    $this->db->query(
                        "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)",
                        [$orderId, $productId, $quantity, $price]
                    );
                }
            }

            // Update order total and timestamp
            $this->db->query(
                "UPDATE orders SET total_amount = ?, updated_at = NOW(), recovery_message_sent = 0 WHERE id = ?",
                [$totalAmount, $orderId]
            );

            $conn->commit();
            echo json_encode(['status' => 'tracked', 'order_id' => $orderId]);

        } catch (Exception $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}
