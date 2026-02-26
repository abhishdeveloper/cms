<?php

// public/webhook.php

// Ensure database and services are available
require_once __DIR__ . '/../autoload.php';

header('Content-Type: application/json');

$config = require __DIR__ . '/../config/services.php';
$razorpaySecret = $config['razorpay']['webhook_secret'];
$adminPhone = $config['whatsapp']['admin_phone'];

$payload = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_RAZORPAY_SIGNATURE'] ?? '';

if (empty($payload) || empty($signature)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

// Verify Signature
$expectedSignature = hash_hmac('sha256', $payload, $razorpaySecret);

if (!hash_equals($expectedSignature, $signature)) {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid signature']);
    exit;
}

$data = json_decode($payload, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON']);
    exit;
}

// Process Event
if (isset($data['event']) && $data['event'] === 'payment.captured') {
    try {
        $payment = $data['payload']['payment']['entity'];
        $razorpayOrderId = $payment['order_id'];
        $amount = $payment['amount'] / 100; // Convert from paise

        $db = Database::getInstance();

        // Find order
        $stmt = $db->query("SELECT id, user_id, coupon_code FROM orders WHERE razorpay_order_id = ?", [$razorpayOrderId]);
        $order = $stmt->fetch();

        if ($order) {
            $orderId = $order['id'];
            $userId = $order['user_id'];
            $couponCode = $order['coupon_code'] ?? null;

            // Update Order Status
            $db->query("UPDATE orders SET payment_status = 'paid', order_status = 'processing' WHERE id = ?", [$orderId]);

            // Increment Coupon Usage
            if (!empty($couponCode)) {
                $db->query("UPDATE coupons SET times_used = times_used + 1 WHERE code = ?", [$couponCode]);
            }

            // Generate Invoice & Send Email
            try {
                $invoiceService = new InvoiceService();
                $pdfPath = $invoiceService->generateInvoice($orderId);

                $stmt = $db->query("SELECT phone, name, email FROM users WHERE id = ?", [$userId]);
                $user = $stmt->fetch();

                if ($user) {
                    $emailService = new EmailService();
                    $subject = "Order #{$orderId} Confirmed";
                    $body = "<h1>Thank you for your order!</h1><p>Your payment has been received. Please find your invoice attached.</p>";
                    if (!empty($user['email'])) {
                        $emailService->sendOrderUpdate($user['email'], $user['name'], $subject, $body, $pdfPath);
                    }
                }
            } catch (Exception $e) {
                error_log("Invoice/Email Error: " . $e->getMessage());
            }

            // Get User Phone
            $stmt = $db->query("SELECT phone, name FROM users WHERE id = ?", [$userId]);
            $user = $stmt->fetch();

            if ($user) {
                $userPhone = $user['phone'];
                $userName = $user['name'];

                $whatsapp = new WhatsAppService();

                // Notify User
                $userMessage = "Hello {$userName}, your order #{$orderId} is confirmed! Total amount: {$amount}";
                $whatsapp->sendMessage($userPhone, $userMessage);

                // Notify Admin
                if (!empty($adminPhone)) {
                    $adminMessage = "New Order Received: Order #{$orderId} from {$userName} ({$userPhone}). Amount: {$amount}";
                    $whatsapp->sendMessage($adminPhone, $adminMessage);
                }
            }
        }

        http_response_code(200);
        echo json_encode(['status' => 'ok']);

    } catch (Exception $e) {
        http_response_code(500);
        error_log("Webhook Error: " . $e->getMessage());
        echo json_encode(['error' => 'Internal Server Error']);
    }
} else {
    // Other events are ignored but acknowledged
    http_response_code(200);
    echo json_encode(['status' => 'ignored']);
}
