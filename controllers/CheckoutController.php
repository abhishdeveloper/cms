<?php

class CheckoutController
{
    private $db;
    private $razorpay;
    private $couponService;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->razorpay = new RazorpayService();
        $this->couponService = new CouponService();
    }

    public function applyCoupon()
    {
        header('Content-Type: application/json');

        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'User not authenticated']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $cartItems = $input['cart_items'] ?? [];
        $code = $input['code'] ?? '';

        if (empty($code)) {
            http_response_code(400);
            echo json_encode(['error' => 'Coupon code is required']);
            return;
        }

        try {
            $totalAmount = $this->calculateCartTotal($cartItems);
            $result = $this->couponService->validateAndCalculate($code, $totalAmount);

            echo json_encode([
                'status' => 'success',
                'original_total' => $totalAmount,
                'discount' => $result['amount'],
                'final_total' => $totalAmount - $result['amount'],
                'message' => 'Coupon applied successfully!'
            ]);

        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function process()
    {
        header('Content-Type: application/json');

        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'User not authenticated']);
            return;
        }

        $userId = $_SESSION['user_id'];
        $input = json_decode(file_get_contents('php://input'), true);
        $cartItems = $input['cart_items'] ?? [];
        $couponCode = $input['coupon_code'] ?? null;

        if (empty($cartItems)) {
            http_response_code(400);
            echo json_encode(['error' => 'Cart is empty']);
            return;
        }

        try {
            $conn = $this->db->getConnection();
            $conn->beginTransaction();

            $totalAmount = 0;
            $orderItems = [];

            // 1. Validate Items & Calculate Total
            foreach ($cartItems as $item) {
                $productId = (int)$item['product_id'];
                $quantity = (int)$item['quantity'];

                $stmt = $this->db->query("SELECT price, stock FROM products WHERE id = ?", [$productId]);
                $product = $stmt->fetch();

                if (!$product) throw new Exception("Product ID $productId not found");
                if ($product['stock'] < $quantity) throw new Exception("Insufficient stock for product ID $productId");

                $price = $product['price'];
                $totalAmount += $price * $quantity;

                $orderItems[] = [
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'price' => $price
                ];
            }

            // 2. Apply Coupon
            $discountAmount = 0;
            $finalTotal = $totalAmount;

            if ($couponCode) {
                $couponResult = $this->couponService->validateAndCalculate($couponCode, $totalAmount);
                $discountAmount = $couponResult['amount'];
                $finalTotal = $totalAmount - $discountAmount;
            }

            // 3. Create Order
            // Check if draft exists
            $stmt = $this->db->query("SELECT id FROM orders WHERE user_id = ? AND order_status = 'draft'", [$userId]);
            $draftOrder = $stmt->fetch();

            if ($draftOrder) {
                $orderId = $draftOrder['id'];
                $this->db->query("DELETE FROM order_items WHERE order_id = ?", [$orderId]); // Clear old items
                $sql = "UPDATE orders SET total_amount = ?, discount_amount = ?, coupon_code = ?, payment_status = 'pending', order_status = 'pending', updated_at = NOW() WHERE id = ?";
                $this->db->query($sql, [$finalTotal, $discountAmount, $couponCode, $orderId]);
            } else {
                $sql = "INSERT INTO orders (user_id, total_amount, discount_amount, coupon_code, payment_status, order_status, created_at, updated_at) VALUES (?, ?, ?, ?, 'pending', 'pending', NOW(), NOW())";
                $this->db->query($sql, [$userId, $finalTotal, $discountAmount, $couponCode]);
                $orderId = $conn->lastInsertId();
            }

            // 4. Insert Items & Update Stock
            foreach ($orderItems as $item) {
                $this->db->query("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)", [$orderId, $item['product_id'], $item['quantity'], $item['price']]);
                $this->db->query("UPDATE products SET stock = stock - ? WHERE id = ?", [$item['quantity'], $item['product_id']]);
            }

            // 5. Razorpay Order
            $razorpayOrder = $this->razorpay->createOrder($finalTotal);
            $razorpayOrderId = $razorpayOrder['id'];

            $this->db->query("UPDATE orders SET razorpay_order_id = ? WHERE id = ?", [$razorpayOrderId, $orderId]);

            $conn->commit();

            echo json_encode([
                'status' => 'success',
                'order_id' => $orderId,
                'razorpay_order_id' => $razorpayOrderId,
                'amount' => $finalTotal,
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

    private function calculateCartTotal($cartItems)
    {
        $total = 0;
        foreach ($cartItems as $item) {
            $productId = (int)$item['product_id'];
            $quantity = (int)$item['quantity'];

            $stmt = $this->db->query("SELECT price FROM products WHERE id = ?", [$productId]);
            $product = $stmt->fetch();

            if ($product) {
                $total += $product['price'] * $quantity;
            }
        }
        return $total;
    }
}
