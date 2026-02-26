<?php include __DIR__ . '/layout.php'; ?>

<h1 class="text-3xl font-bold mb-6 text-gray-800">Manage Categories</h1>

<form action="/admin/categories/store" method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded shadow mb-8">
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-bold mb-2">Name</label>
            <input type="text" name="name" class="w-full border p-2 rounded" required>
        </div>
        <div>
            <label class="block text-sm font-bold mb-2">Image</label>
            <input type="file" name="image" class="w-full border p-2 rounded">
        </div>
    </div>
    <button type="submit" class="mt-4 bg-blue-600 text-white px-4 py-2 rounded">Add Category</button>
</form>

<table class="w-full bg-white rounded shadow text-left">
    <thead class="bg-gray-200">
        <tr>
            <th class="p-3">ID</th>
            <th class="p-3">Image</th>
            <th class="p-3">Name</th>
            <th class="p-3">Slug</th>
            <th class="p-3">Action</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($categories as $cat): ?>
            <tr class="border-b hover:bg-gray-50">
                <td class="p-3"><?= $cat['id'] ?></td>
                <td class="p-3">
                    <?php if ($cat['image_url']): ?>
                        <img src="<?= $cat['image_url'] ?>" class="w-10 h-10 object-cover">
                    <?php endif; ?>
                </td>
                <td class="p-3"><?= htmlspecialchars($cat['name']) ?></td>
                <td class="p-3"><?= htmlspecialchars($cat['slug']) ?></td>
                <td class="p-3">
                    <form action="/admin/categories/delete" method="POST" onsubmit="return confirm('Delete?');">
                        <input type="hidden" name="id" value="<?= $cat['id'] ?>">
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
