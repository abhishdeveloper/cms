<?php include __DIR__ . '/layout.php'; ?>

<h1 class="text-3xl font-bold mb-6 text-gray-800">Manage Quotations</h1>

<table class="w-full bg-white rounded shadow text-left">
    <thead class="bg-gray-200">
        <tr>
            <th class="p-3">ID</th>
            <th class="p-3">Product</th>
            <th class="p-3">Customer</th>
            <th class="p-3">Budget</th>
            <th class="p-3">Requirements</th>
            <th class="p-3">Status</th>
            <th class="p-3">Action</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($quotations as $quote): ?>
            <tr class="border-b hover:bg-gray-50">
                <td class="p-3">#<?= $quote['id'] ?></td>
                <td class="p-3 font-bold"><?= htmlspecialchars($quote['product_name']) ?></td>
                <td class="p-3">
                    <div><?= htmlspecialchars($quote['user_name']) ?></div>
                    <div class="text-xs text-gray-500"><?= htmlspecialchars($quote['user_phone']) ?></div>
                    <div class="text-xs text-gray-500"><?= htmlspecialchars($quote['user_email']) ?></div>
                </td>
                <td class="p-3"><?= htmlspecialchars($quote['budget_range']) ?></td>
                <td class="p-3 text-sm max-w-xs truncate" title="<?= htmlspecialchars($quote['requirements']) ?>">
                    <?= htmlspecialchars($quote['requirements']) ?>
                </td>
                <td class="p-3">
                    <span class="px-2 py-1 rounded text-xs font-bold
                        <?= $quote['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' :
                           ($quote['status'] === 'reviewed' ? 'bg-blue-100 text-blue-800' :
                           ($quote['status'] === 'converted' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800')) ?>">
                        <?= ucfirst($quote['status']) ?>
                    </span>
                </td>
                <td class="p-3">
                    <form action="/admin/quotations/update" method="POST" class="flex space-x-2">
                        <input type="hidden" name="id" value="<?= $quote['id'] ?>">
                        <select name="status" class="border rounded px-2 py-1 text-xs">
                            <option value="pending" <?= $quote['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="reviewed" <?= $quote['status'] === 'reviewed' ? 'selected' : '' ?>>Reviewed</option>
                            <option value="converted" <?= $quote['status'] === 'converted' ? 'selected' : '' ?>>Converted</option>
                            <option value="rejected" <?= $quote['status'] === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                        </select>
                        <button type="submit" class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700 text-xs">Update</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

</main>
</body>
</html>
