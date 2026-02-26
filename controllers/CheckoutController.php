<?php

class CheckoutController
{
    private $db;
    private $razorpay;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->razorpay = new RazorpayService();
    }

    public function process()
    {
        header('Content-Type: application/json');

        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'User not authenticated']);
            return;
        }

        $userId = $_SESSION['user_id'];
        $input = json_decode(file_get_contents('php://input'), true);

        // Basic validation
        if (!isset($input['cart_items']) || !is_array($input['cart_items']) || empty($input['cart_items'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid cart items']);
            return;
        }

        $totalAmount = 0;
        $orderItems = [];

        // Calculate total securely from DB
        try {
            $conn = $this->db->getConnection();
            $conn->beginTransaction();

            foreach ($input['cart_items'] as $item) {
                if (!isset($item['product_id']) || !isset($item['quantity'])) {
                    throw new Exception("Invalid item structure");
                }

                $productId = (int)$item['product_id'];
                $quantity = (int)$item['quantity'];

                if ($quantity <= 0) {
                    throw new Exception("Invalid quantity for product ID $productId");
                }

                $stmt = $this->db->query("SELECT price, stock FROM products WHERE id = ?", [$productId]);
                $product = $stmt->fetch();

                if (!$product) {
                    throw new Exception("Product ID $productId not found");
                }

                if ($product['stock'] < $quantity) {
                    throw new Exception("Insufficient stock for product ID $productId");
                }

                $price = $product['price'];
                $lineTotal = $price * $quantity;
                $totalAmount += $lineTotal;

                $orderItems[] = [
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'price' => $price
                ];
            }

            // Create Order in DB
            $stmt = $this->db->query(
                "INSERT INTO orders (user_id, total_amount, payment_status, order_status, created_at) VALUES (?, ?, 'pending', 'pending', NOW())",
                [$userId, $totalAmount]
            );
            $orderId = $conn->lastInsertId();

            // Insert Order Items
            foreach ($orderItems as $item) {
                $this->db->query(
                    "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)",
                    [$orderId, $item['product_id'], $item['quantity'], $item['price']]
                );

                // Decrement stock
                $this->db->query(
                    "UPDATE products SET stock = stock - ? WHERE id = ?",
                    [$item['quantity'], $item['product_id']]
                );
            }

            // Create Razorpay Order
            $razorpayOrder = $this->razorpay->createOrder($totalAmount);

            if (!isset($razorpayOrder['id'])) {
                throw new Exception("Failed to create Razorpay order");
            }

            $razorpayOrderId = $razorpayOrder['id'];

            // Update Order with Razorpay ID
            $this->db->query(
                "UPDATE orders SET razorpay_order_id = ? WHERE id = ?",
                [$razorpayOrderId, $orderId]
            );

            $conn->commit();

            echo json_encode([
                'status' => 'success',
                'order_id' => $orderId,
                'razorpay_order_id' => $razorpayOrderId,
                'amount' => $totalAmount,
                'currency' => 'INR'
            ]);

        } catch (Exception $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}
