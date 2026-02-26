<?php

// Run this script via cron
// php cron_review_request.php

require_once __DIR__ . '/autoload.php';

try {
    $db = Database::getInstance();
    $whatsapp = new WhatsAppService();

    // Find orders delivered 5 days ago and not yet requested for review
    // For testing, let's say 5 minutes. In production, 5 days.
    // User requested "exactly 5 days ago (use INTERVAL 5 DAY)"

    $stmt = $db->query("
        SELECT o.id, o.user_id, u.phone, u.name,
               (SELECT p.name FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = o.id LIMIT 1) as product_name
        FROM orders o
        JOIN users u ON o.user_id = u.id
        WHERE o.order_status = 'delivered'
        AND o.review_requested = 0
        AND o.delivered_at IS NOT NULL
        AND o.delivered_at <= DATE_SUB(NOW(), INTERVAL 5 DAY)
    ");

    $orders = $stmt->fetchAll();

    foreach ($orders as $order) {
        $orderId = $order['id'];
        $phone = $order['phone'];
        $name = $order['name'];
        $productName = $order['product_name'];

        $message = "Hi {$name}! We hope you are enjoying your recent purchase of {$productName}. Please reply to this message with a number from 1 to 5 to rate your experience!";

        try {
            $whatsapp->sendMessage($phone, $message);

            // Mark as requested
            $db->query("UPDATE orders SET review_requested = 1 WHERE id = ?", [$orderId]);

            echo "Sent review request to {$phone} for order #{$orderId}\n";
        } catch (Exception $e) {
            echo "Failed to send to {$phone}: " . $e->getMessage() . "\n";
        }
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
