<?php include __DIR__ . '/layout.php'; ?>

<h1 class="text-3xl font-bold mb-6 text-gray-800">My Orders</h1>

<div class="space-y-4">
    <?php foreach ($orders as $order): ?>
        <div class="bg-white p-6 rounded shadow">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h3 class="font-bold text-lg">Order #<?= $order['id'] ?></h3>
                    <p class="text-sm text-gray-500"><?= $order['created_at'] ?></p>
                </div>
                <div class="text-right">
                    <p class="font-bold text-xl text-green-600">₹<?= $order['total_amount'] ?></p>
                    <span class="px-2 py-1 rounded text-xs font-bold
                        <?= $order['order_status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' :
                           ($order['order_status'] === 'paid' ? 'bg-blue-100 text-blue-800' :
                           ($order['order_status'] === 'shipped' ? 'bg-purple-100 text-purple-800' :
                           ($order['order_status'] === 'completed' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'))) ?>">
                        <?= ucfirst($order['order_status']) ?>
                    </span>
                </div>
            </div>

            <div class="text-sm text-gray-600">
                <?php if ($order['coupon_code']): ?>
                    <p>Coupon Applied: <span class="font-bold"><?= $order['coupon_code'] ?></span> (-₹<?= $order['discount_amount'] ?>)</p>
                <?php endif; ?>
                <?php if ($order['razorpay_order_id']): ?>
                    <p>Transaction ID: <span class="font-mono"><?= $order['razorpay_order_id'] ?></span></p>
                <?php endif; ?>
                <?php if ($order['payment_status'] === 'paid'): ?>
                    <a href="/download_invoice.php?id=<?= $order['id'] ?>" class="mt-2 inline-block text-blue-600 hover:underline">Download Invoice (PDF)</a>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

</main>
</body>
</html>
