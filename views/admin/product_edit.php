<?php include __DIR__ . '/layout.php'; ?>

<h1 class="text-3xl font-bold mb-6 text-gray-800">Edit Product</h1>

<form action="/admin/products/update" method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded shadow mb-8">
    <input type="hidden" name="id" value="<?= $product['id'] ?>">
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-bold mb-2">Name</label>
            <input type="text" name="name" value="<?= htmlspecialchars($product['name']) ?>" class="w-full border p-2 rounded" required>
        </div>
        <div>
            <label class="block text-sm font-bold mb-2">Price</label>
            <input type="number" step="0.01" name="price" value="<?= $product['price'] ?>" class="w-full border p-2 rounded" required>
        </div>
        <div class="col-span-2">
            <label class="block text-sm font-bold mb-2">Description</label>
            <textarea name="description" class="w-full border p-2 rounded"><?= htmlspecialchars($product['description']) ?></textarea>
        </div>
        <div>
            <label class="block text-sm font-bold mb-2">Category</label>
            <select name="category_id" class="w-full border p-2 rounded">
                <option value="">Select Category</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= $product['category_id'] == $cat['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="flex items-center mt-6">
            <input type="checkbox" name="is_quote_only" id="is_quote_only" class="mr-2 h-5 w-5" <?= $product['is_quote_only'] ? 'checked' : '' ?>>
            <label for="is_quote_only" class="text-sm font-bold">Quote Only (B2B)</label>
        </div>
        <div>
            <label class="block text-sm font-bold mb-2">Stock</label>
            <input type="number" name="stock" value="<?= $product['stock'] ?>" class="w-full border p-2 rounded" required>
        </div>
        <div>
            <label class="block text-sm font-bold mb-2">Main Image (Leave blank to keep)</label>
            <input type="file" name="image" class="w-full border p-2 rounded">
            <?php if ($product['image_url']): ?>
                <p class="text-xs text-gray-500 mt-1">Current: <a href="<?= $product['image_url'] ?>" target="_blank" class="text-blue-500">View</a></p>
            <?php endif; ?>
        </div>
        <div>
            <label class="block text-sm font-bold mb-2">Add Gallery Images</label>
            <input type="file" name="gallery[]" multiple class="w-full border p-2 rounded">
            <div class="flex gap-2 mt-2">
                <?php foreach ($product['gallery_images'] as $img): ?>
                    <img src="<?= $img ?>" class="w-10 h-10 object-cover border rounded">
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <div class="mt-4 flex gap-4">
        <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded">Update Product</button>
        <a href="/admin/products" class="bg-gray-500 text-white px-4 py-2 rounded">Cancel</a>
    </div>
</form>

</main>
</body>
</html>
