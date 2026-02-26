<?php

class CheckoutApiController
{
    private $db;
    private $razorpay;
    private $auth;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->razorpay = new RazorpayService();
        $this->auth = new ApiAuthMiddleware();
    }

    public function initiate()
    {
        header('Content-Type: application/json');

        // Authenticate User
        $user = $this->auth->authenticate();
        $userId = $user['id'];

        $input = json_decode(file_get_contents('php://input'), true);
        $items = $input['items'] ?? []; // Array of {product_id, quantity}

        if (empty($items) || !is_array($items)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid items payload']);
            return;
        }

        $totalAmount = 0;
        $orderItems = [];

        try {
            $conn = $this->db->getConnection();
            $conn->beginTransaction();

            foreach ($items as $item) {
                $productId = (int)$item['product_id'];
                $quantity = (int)$item['quantity'];

                if ($quantity <= 0) continue;

                $stmt = $this->db->query("SELECT price, stock FROM products WHERE id = ?", [$productId]);
                $product = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$product) {
                    throw new Exception("Product ID $productId not found");
                }
                if ($product['stock'] < $quantity) {
                    throw new Exception("Insufficient stock for product ID $productId");
                }

                $price = (float)$product['price'];
                $totalAmount += $price * $quantity;

                $orderItems[] = [
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'price' => $price
                ];
            }

            if ($totalAmount <= 0) {
                throw new Exception("Total amount must be greater than 0");
            }

            // Create Order in DB (Pending)
            $this->db->query(
                "INSERT INTO orders (user_id, total_amount, payment_status, order_status, created_at, updated_at) VALUES (?, ?, 'pending', 'pending', NOW(), NOW())",
                [$userId, $totalAmount]
            );
            $orderId = $conn->lastInsertId();

            // Insert Items and Deduct Stock
            foreach ($orderItems as $item) {
                $this->db->query(
                    "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)",
                    [$orderId, $item['product_id'], $item['quantity'], $item['price']]
                );

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

            // Update DB with Razorpay ID
            $this->db->query("UPDATE orders SET razorpay_order_id = ? WHERE id = ?", [$razorpayOrderId, $orderId]);

            $conn->commit();

            echo json_encode([
                'success' => true,
                'razorpay_order_id' => $razorpayOrderId,
                'total_amount' => $totalAmount,
                'internal_order_id' => $orderId,
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
