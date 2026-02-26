<?php include __DIR__ . '/layout.php'; ?>

<h1 class="text-3xl font-bold mb-6 text-gray-800">Dashboard</h1>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
    <div class="bg-white p-6 rounded-lg shadow">
        <h2 class="text-xl font-bold text-gray-600 mb-2">Total Revenue</h2>
        <p class="text-3xl font-bold text-green-600">₹<?= number_format($revenue, 2) ?></p>
    </div>
    <div class="bg-white p-6 rounded-lg shadow">
        <h2 class="text-xl font-bold text-gray-600 mb-2">Pending Orders</h2>
        <p class="text-3xl font-bold text-orange-500"><?= $pending ?></p>
    </div>
    <div class="bg-white p-6 rounded-lg shadow">
        <h2 class="text-xl font-bold text-gray-600 mb-2">Recent Orders</h2>
        <ul class="space-y-2">
            <?php foreach ($recentOrders as $order): ?>
                <li class="flex justify-between text-sm">
                    <span class="font-bold">#<?= $order['id'] ?></span>
                    <span class="text-gray-500">₹<?= $order['total_amount'] ?></span>
                    <span class="text-blue-500"><?= $order['order_status'] ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>

</main>
</body>
</html>
