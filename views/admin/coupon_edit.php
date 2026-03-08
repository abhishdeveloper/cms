<?php include __DIR__ . '/layout.php'; ?>

<h1 class="text-3xl font-bold mb-6 text-gray-800">Edit Coupon</h1>

<form action="/admin/coupons/update" method="POST" class="bg-white p-6 rounded shadow mb-8">
    <input type="hidden" name="id" value="<?= $coupon['id'] ?>">
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-bold mb-2">Code</label>
            <input type="text" name="code" value="<?= htmlspecialchars($coupon['code']) ?>" class="w-full border p-2 rounded" required>
        </div>
        <div>
            <label class="block text-sm font-bold mb-2">Type</label>
            <select name="type" class="w-full border p-2 rounded">
                <option value="percentage" <?= $coupon['type'] === 'percentage' ? 'selected' : '' ?>>Percentage</option>
                <option value="fixed" <?= $coupon['type'] === 'fixed' ? 'selected' : '' ?>>Fixed Amount</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-bold mb-2">Value</label>
            <input type="number" step="0.01" name="value" value="<?= $coupon['discount_value'] ?>" class="w-full border p-2 rounded" required>
        </div>
        <div>
            <label class="block text-sm font-bold mb-2">Min Cart Value</label>
            <input type="number" step="0.01" name="min_cart_value" value="<?= $coupon['min_cart_value'] ?>" class="w-full border p-2 rounded">
        </div>
        <div>
            <label class="block text-sm font-bold mb-2">Expiry Date</label>
            <?php
                $expiry = $coupon['expires_at'] ? date('Y-m-d\TH:i', strtotime($coupon['expires_at'])) : '';
            ?>
            <input type="datetime-local" name="expires_at" value="<?= $expiry ?>" class="w-full border p-2 rounded">
        </div>
        <div>
            <label class="block text-sm font-bold mb-2">Usage Limit</label>
            <input type="number" name="usage_limit" value="<?= $coupon['usage_limit'] ?>" class="w-full border p-2 rounded">
        </div>
    </div>
    <div class="mt-4 flex gap-4">
        <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded">Update Coupon</button>
        <a href="/admin/coupons" class="bg-gray-500 text-white px-4 py-2 rounded">Cancel</a>
    </div>
</form>

</main>
</body>
</html>
