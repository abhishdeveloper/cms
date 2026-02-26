<?php include __DIR__ . '/layout.php'; ?>

<h1 class="text-3xl font-bold mb-6 text-gray-800">Manage Coupons</h1>

<form action="/admin/coupons/store" method="POST" class="bg-white p-6 rounded shadow mb-8">
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-bold mb-2">Code</label>
            <input type="text" name="code" class="w-full border p-2 rounded" required>
        </div>
        <div>
            <label class="block text-sm font-bold mb-2">Type</label>
            <select name="type" class="w-full border p-2 rounded">
                <option value="percentage">Percentage</option>
                <option value="fixed">Fixed Amount</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-bold mb-2">Value</label>
            <input type="number" step="0.01" name="value" class="w-full border p-2 rounded" required>
        </div>
        <div>
            <label class="block text-sm font-bold mb-2">Min Cart Value</label>
            <input type="number" step="0.01" name="min_cart_value" class="w-full border p-2 rounded" value="0">
        </div>
        <div>
            <label class="block text-sm font-bold mb-2">Expiry Date</label>
            <input type="datetime-local" name="expires_at" class="w-full border p-2 rounded">
        </div>
        <div>
            <label class="block text-sm font-bold mb-2">Usage Limit</label>
            <input type="number" name="usage_limit" class="w-full border p-2 rounded">
        </div>
    </div>
    <button type="submit" class="mt-4 bg-blue-600 text-white px-4 py-2 rounded">Create Coupon</button>
</form>

<table class="w-full bg-white rounded shadow text-left">
    <thead class="bg-gray-200">
        <tr>
            <th class="p-3">Code</th>
            <th class="p-3">Type</th>
            <th class="p-3">Value</th>
            <th class="p-3">Expiry</th>
            <th class="p-3">Usage</th>
            <th class="p-3">Action</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($coupons as $coupon): ?>
            <tr class="border-b hover:bg-gray-50">
                <td class="p-3 font-bold"><?= htmlspecialchars($coupon['code']) ?></td>
                <td class="p-3"><?= ucfirst($coupon['type']) ?></td>
                <td class="p-3"><?= $coupon['type'] === 'percentage' ? $coupon['discount_value'] . '%' : '₹' . $coupon['discount_value'] ?></td>
                <td class="p-3"><?= $coupon['expires_at'] ?></td>
                <td class="p-3"><?= $coupon['times_used'] ?> / <?= $coupon['usage_limit'] ?: '∞' ?></td>
                <td class="p-3">
                    <form action="/admin/coupons/delete" method="POST" onsubmit="return confirm('Delete?');">
                        <input type="hidden" name="id" value="<?= $coupon['id'] ?>">
                        <button type="submit" class="text-red-600">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

</main>
</body>
</html>
