<?php include __DIR__ . '/layout.php'; ?>

<h1 class="text-3xl font-bold mb-6 text-gray-800">Manage Orders</h1>

<table class="w-full bg-white rounded shadow text-left text-sm">
    <thead class="bg-gray-200">
        <tr>
            <th class="p-3">Order ID</th>
            <th class="p-3">Customer</th>
            <th class="p-3">Total</th>
            <th class="p-3">Status</th>
            <th class="p-3">Payment</th>
            <th class="p-3">Action</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($orders as $order): ?>
            <tr class="border-b hover:bg-gray-50">
                <td class="p-3">
                    <a href="/admin/orders/view?id=<?= $order['id'] ?>" class="text-blue-600 font-bold hover:underline">
                        #<?= $order['id'] ?>
                    </a>
                </td>
                <td class="p-3">
                    <div class="font-bold"><?= htmlspecialchars($order['user_name']) ?></div>
                    <div class="text-xs text-gray-500"><?= $order['user_phone'] ?></div>
                </td>
                <td class="p-3">₹<?= $order['total_amount'] ?></td>
                <td class="p-3">
                    <span class="px-2 py-1 rounded text-xs font-bold
                        <?= $order['order_status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' :
                           ($order['order_status'] === 'paid' ? 'bg-blue-100 text-blue-800' :
                           ($order['order_status'] === 'shipped' ? 'bg-purple-100 text-purple-800' :
                           ($order['order_status'] === 'completed' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'))) ?>">
                        <?= ucfirst($order['order_status']) ?>
                    </span>
                </td>
                <td class="p-3"><?= $order['payment_status'] ?></td>
                <td class="p-3">
                    <form action="/admin/orders/update-status" method="POST" class="flex items-center space-x-2">
                        <input type="hidden" name="id" value="<?= $order['id'] ?>">
                        <select name="status" class="border rounded px-2 py-1 text-xs">
                            <option value="pending" <?= $order['order_status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="processing" <?= $order['order_status'] === 'processing' ? 'selected' : '' ?>>Processing</option>
                            <option value="shipped" <?= $order['order_status'] === 'shipped' ? 'selected' : '' ?>>Shipped</option>
                            <option value="delivered" <?= $order['order_status'] === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                            <option value="completed" <?= $order['order_status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                            <option value="cancelled" <?= $order['order_status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                        </select>
                        <button type="submit" class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700">Update</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

</main>
</body>
</html>
