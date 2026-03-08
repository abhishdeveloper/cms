<?php include __DIR__ . '/layout.php'; ?>

<h1 class="text-3xl font-bold mb-6 text-gray-800">Edit Category</h1>

<form action="/admin/categories/update" method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded shadow mb-8">
    <input type="hidden" name="id" value="<?= $category['id'] ?>">
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-bold mb-2">Name</label>
            <input type="text" name="name" value="<?= htmlspecialchars($category['name']) ?>" class="w-full border p-2 rounded" required>
        </div>
        <div>
            <label class="block text-sm font-bold mb-2">Image (Leave blank to keep)</label>
            <input type="file" name="image" class="w-full border p-2 rounded">
            <?php if ($category['image_url']): ?>
                <p class="text-xs text-gray-500 mt-1">Current: <a href="<?= $category['image_url'] ?>" target="_blank" class="text-blue-500">View</a></p>
            <?php endif; ?>
        </div>
    </div>
    <div class="mt-4 flex gap-4">
        <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded">Update Category</button>
        <a href="/admin/categories" class="bg-gray-500 text-white px-4 py-2 rounded">Cancel</a>
    </div>
</form>

</main>
</body>
</html>
