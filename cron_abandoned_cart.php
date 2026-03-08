<?php

// Run this script via cron
// php cron_abandoned_cart.php

require_once __DIR__ . '/autoload.php';

try {
    $db = Database::getInstance();
    $whatsapp = new WhatsAppService();

    // Find carts abandoned > 1 hour ago and not yet notified
    // For testing, let's say 1 hour. In production, maybe longer?
    // User asked for "older than 1 hour"

    $stmt = $db->query("
        SELECT o.id, o.user_id, u.phone, u.name
        FROM orders o
        JOIN users u ON o.user_id = u.id
        WHERE o.order_status = 'draft'
        AND o.recovery_message_sent = 0
        AND o.updated_at < DATE_SUB(NOW(), INTERVAL 1 HOUR)
    ");

    $orders = $stmt->fetchAll();

    foreach ($orders as $order) {
        $orderId = $order['id'];
        $userId = $order['user_id'];
        $phone = $order['phone'];
        $name = $order['name'];

        // Send Notification
        $cartLink = "https://yourstore.com/cart"; // In reality, this would be a link that restores the cart session
        $message = "Hi {$name}, you left something great in your cart! Complete your purchase here: {$cartLink}";

        try {
            $whatsapp->sendMessage($phone, $message);

            // Mark as notified
            $db->query("UPDATE orders SET recovery_message_sent = 1 WHERE id = ?", [$orderId]);

            echo "Sent notification to {$phone} for order #{$orderId}\n";
        } catch (Exception $e) {
            echo "Failed to send to {$phone}: " . $e->getMessage() . "\n";
        }
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
