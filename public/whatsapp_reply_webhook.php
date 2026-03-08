<?php

// public/whatsapp_reply_webhook.php

require_once __DIR__ . '/../autoload.php';

$config = require __DIR__ . '/../config/services.php';
// Webhook endpoint to receive replies from WhatsApp Gateway

// Assuming the gateway sends JSON payload
$payload = file_get_contents('php://input');
$data = json_decode($payload, true);

if (empty($data) || empty($data['from']) || empty($data['message'])) {
    http_response_code(400);
    exit;
}

$phone = $data['from']; // Sender's phone number
$message = trim($data['message']); // Reply text

// Verify Rating
if (!preg_match('/^[1-5]$/', $message)) {
    // Send fallback message
    $whatsapp = new WhatsAppService();
    $whatsapp->sendMessage($phone, "Please reply with only a number from 1 to 5.");
    http_response_code(200);
    exit;
}

$rating = (int)$message;

try {
    $db = Database::getInstance();

    // Find User
    $stmt = $db->query("SELECT id FROM users WHERE phone = ?", [$phone]);
    $user = $stmt->fetch();

    if ($user) {
        $userId = $user['id'];

        // Find the most recent delivered order for which review was requested
        // And for which user has NOT yet submitted a review for the primary product
        // This avoids double-rating the same order
        $stmt = $db->query("
            SELECT o.id, oi.product_id
            FROM orders o
            JOIN order_items oi ON o.id = oi.order_id
            WHERE o.user_id = ?
            AND o.order_status = 'delivered'
            AND o.review_requested = 1
            AND NOT EXISTS (SELECT 1 FROM reviews r WHERE r.user_id = ? AND r.product_id = oi.product_id)
            ORDER BY o.delivered_at DESC
            LIMIT 1
        ", [$userId, $userId]);

        $order = $stmt->fetch();

        if ($order) {
            $productId = $order['product_id'];

            // Insert Review
            $db->query(
                "INSERT INTO reviews (user_id, product_id, rating, comment) VALUES (?, ?, ?, 'Automated Rating')",
                [$userId, $productId, $rating]
            );

            // Send Thank You
            $whatsapp = new WhatsAppService();
            $whatsapp->sendMessage($phone, "Thank you for your {$rating}-star rating! Your feedback helps us grow.");
        } else {
            // No pending review found for this user
            // Maybe they already rated everything?
            $whatsapp = new WhatsAppService();
            $whatsapp->sendMessage($phone, "We couldn't find a pending review for your recent orders. But thank you!");
        }
    }

    http_response_code(200);

} catch (Exception $e) {
    http_response_code(500);
    error_log("WhatsApp Webhook Error: " . $e->getMessage());
}
