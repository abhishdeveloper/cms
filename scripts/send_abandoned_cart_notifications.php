<?php

// Run this script via cron every 5 minutes
// php scripts/send_abandoned_cart_notifications.php

require_once __DIR__ . '/../autoload.php';

try {
    $db = Database::getInstance();
    $whatsapp = new WhatsAppService();

    // Find carts abandoned > 5 minutes ago and not yet notified
    // For testing, let's say 5 minutes. In production, maybe 1 hour?
    // User asked for "after a few minute"

    $stmt = $db->query("
        SELECT ac.id, ac.user_id, ac.cart_data, u.phone, u.name
        FROM abandoned_carts ac
        JOIN users u ON ac.user_id = u.id
        WHERE ac.status = 'pending'
        AND ac.updated_at < DATE_SUB(NOW(), INTERVAL 5 MINUTE)
    ");

    $carts = $stmt->fetchAll();

    foreach ($carts as $cart) {
        $cartId = $cart['id'];
        $userId = $cart['user_id'];
        $phone = $cart['phone'];
        $name = $cart['name'];

        // Check if user has placed an order SINCE the cart was last updated
        // This avoids notifying if they actually bought something else or the same thing in a different session
        $orderStmt = $db->query("
            SELECT id FROM orders
            WHERE user_id = ?
            AND created_at > (SELECT updated_at FROM abandoned_carts WHERE id = ?)
        ", [$userId, $cartId]);

        if ($orderStmt->fetch()) {
            // User placed an order, mark as converted
            $db->query("UPDATE abandoned_carts SET status = 'converted' WHERE id = ?", [$cartId]);
            continue;
        }

        // Send Notification
        $message = "Hey {$name}, you left items in your cart! Complete your purchase now: https://yourstore.com/cart";

        try {
            $whatsapp->sendMessage($phone, $message);

            // Mark as notified
            $db->query("UPDATE abandoned_carts SET status = 'notified' WHERE id = ?", [$cartId]);

            echo "Sent notification to {$phone}\n";
        } catch (Exception $e) {
            echo "Failed to send to {$phone}: " . $e->getMessage() . "\n";
        }
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
