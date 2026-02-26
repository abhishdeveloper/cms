<?php include __DIR__ . '/layout.php'; ?>

<h1 class="text-3xl font-bold mb-6 text-gray-800">Order Details #<?= $order['id'] ?></h1>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
    <div class="bg-white p-6 rounded shadow">
        <h2 class="text-xl font-bold text-gray-600 mb-4">Customer Info</h2>
        <p><strong>Name:</strong> <?= htmlspecialchars($order['user_name']) ?></p>
        <p><strong>Phone:</strong> <?= htmlspecialchars($order['user_phone']) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($order['user_email'] ?: 'N/A') ?></p>
    </div>

    <div class="bg-white p-6 rounded shadow">
        <h2 class="text-xl font-bold text-gray-600 mb-4">Order Info</h2>
        <p><strong>Date:</strong> <?= $order['created_at'] ?></p>
        <p><strong>Status:</strong>
            <span class="px-2 py-1 rounded text-xs font-bold
                <?= $order['order_status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' :
                   ($order['order_status'] === 'paid' ? 'bg-blue-100 text-blue-800' :
                   ($order['order_status'] === 'shipped' ? 'bg-purple-100 text-purple-800' :
                   ($order['order_status'] === 'completed' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'))) ?>">
                <?= ucfirst($order['order_status']) ?>
            </span>
        </p>
        <p><strong>Payment:</strong> <?= $order['payment_status'] ?></p>
        <?php if ($order['razorpay_order_id']): ?>
            <p><strong>Transaction ID:</strong> <?= $order['razorpay_order_id'] ?></p>
        <?php endif; ?>

        <form action="/admin/orders/update-status" method="POST" class="mt-4">
            <input type="hidden" name="id" value="<?= $order['id'] ?>">
            <label class="block text-sm font-bold mb-1">Update Status:</label>
            <div class="flex space-x-2">
                <select name="status" class="border rounded px-2 py-1">
                    <option value="pending" <?= $order['order_status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="processing" <?= $order['order_status'] === 'processing' ? 'selected' : '' ?>>Processing</option>
                    <option value="shipped" <?= $order['order_status'] === 'shipped' ? 'selected' : '' ?>>Shipped</option>
                    <option value="completed" <?= $order['order_status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                    <option value="cancelled" <?= $order['order_status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                </select>
                <button type="submit" class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700">Update</button>
            </div>
        </form>
    </div>
</div>

<h2 class="text-2xl font-bold text-gray-800 mb-4">Order Items</h2>
<table class="w-full bg-white rounded shadow text-left mb-8">
    <thead class="bg-gray-200">
        <tr>
            <th class="p-3">Product</th>
            <th class="p-3">Price</th>
            <th class="p-3">Quantity</th>
            <th class="p-3">Total</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($items as $item): ?>
            <tr class="border-b hover:bg-gray-50">
                <td class="p-3 flex items-center space-x-3">
                    <?php if ($item['image_url']): ?>
                        <img src="<?= $item['image_url'] ?>" class="w-10 h-10 object-cover rounded">
                    <?php endif; ?>
                    <span><?= htmlspecialchars($item['product_name']) ?></span>
                </td>
                <td class="p-3">₹<?= $item['price'] ?></td>
                <td class="p-3"><?= $item['quantity'] ?></td>
                <td class="p-3">₹<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
    <tfoot class="bg-gray-50 font-bold">
        <tr>
            <td colspan="3" class="p-3 text-right">Subtotal:</td>
            <td class="p-3">₹<?= number_format($order['total_amount'] + $order['discount_amount'], 2) ?></td>
        </tr>
        <?php if ($order['discount_amount'] > 0): ?>
        <tr>
            <td colspan="3" class="p-3 text-right text-green-600">Discount (<?= $order['coupon_code'] ?>):</td>
            <td class="p-3 text-green-600">-₹<?= $order['discount_amount'] ?></td>
        </tr>
        <?php endif; ?>
        <tr class="text-lg">
            <td colspan="3" class="p-3 text-right">Total:</td>
            <td class="p-3">₹<?= $order['total_amount'] ?></td>
        </tr>
    </tfoot>
</table>

<a href="/admin/orders" class="text-blue-600 hover:underline">&larr; Back to Orders</a>

</main>
</body>
</html>
