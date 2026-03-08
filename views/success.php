<?php
$orderId = $_GET['order_id'] ?? '';
$paymentId = $_GET['payment_id'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Success - E-Commerce Store</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-6">
    <div class="bg-white rounded-lg shadow-xl p-8 max-w-md w-full text-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-20 w-20 text-green-500 mx-auto mb-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>

        <h1 class="text-3xl font-bold text-gray-800 mb-4">Payment Successful!</h1>
        <p class="text-gray-600 mb-6">Thank you for your purchase.</p>

        <?php if ($orderId): ?>
            <div class="bg-gray-50 rounded p-4 mb-6">
                <div class="flex justify-between mb-2">
                    <span class="text-gray-600">Order ID:</span>
                    <span class="font-bold">#<?= htmlspecialchars($orderId) ?></span>
                </div>
                <?php if ($paymentId): ?>
                <div class="flex justify-between">
                    <span class="text-gray-600">Payment ID:</span>
                    <span class="font-mono text-sm"><?= htmlspecialchars($paymentId) ?></span>
                </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <a href="/" class="block w-full bg-blue-600 text-white font-bold py-3 rounded hover:bg-blue-700 transition-colors">
            Continue Shopping
        </a>
    </div>
</body>
</html>
