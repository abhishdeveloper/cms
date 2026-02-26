<?php include __DIR__ . '/layout.php'; ?>

<h1 class="text-3xl font-bold mb-6 text-gray-800">Manage Products</h1>

<form action="/admin/products/store" method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded shadow mb-8">
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-bold mb-2">Name</label>
            <input type="text" name="name" class="w-full border p-2 rounded" required>
        </div>
        <div>
            <label class="block text-sm font-bold mb-2">Price</label>
            <input type="number" step="0.01" name="price" class="w-full border p-2 rounded" required>
        </div>
        <div class="col-span-2">
            <label class="block text-sm font-bold mb-2">Description</label>
            <textarea name="description" class="w-full border p-2 rounded"></textarea>
        </div>
        <div>
            <label class="block text-sm font-bold mb-2">Category</label>
            <select name="category_id" class="w-full border p-2 rounded">
                <option value="">Select Category</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="block text-sm font-bold mb-2">Stock</label>
            <input type="number" name="stock" class="w-full border p-2 rounded" required>
        </div>
        <div>
            <label class="block text-sm font-bold mb-2">Main Image</label>
            <input type="file" name="image" class="w-full border p-2 rounded">
        </div>
        <div>
            <label class="block text-sm font-bold mb-2">Gallery Images</label>
            <input type="file" name="gallery[]" multiple class="w-full border p-2 rounded">
        </div>
    </div>
    <button type="submit" class="mt-4 bg-blue-600 text-white px-4 py-2 rounded">Add Product</button>
</form>

<table class="w-full bg-white rounded shadow text-left">
    <thead class="bg-gray-200">
        <tr>
            <th class="p-3">ID</th>
            <th class="p-3">Image</th>
            <th class="p-3">Name</th>
            <th class="p-3">Price</th>
            <th class="p-3">Stock</th>
            <th class="p-3">Action</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($products as $product): ?>
            <tr class="border-b hover:bg-gray-50">
                <td class="p-3"><?= $product['id'] ?></td>
                <td class="p-3"><img src="<?= $product['image_url'] ?>" class="w-10 h-10 object-cover"></td>
                <td class="p-3"><?= htmlspecialchars($product['name']) ?></td>
                <td class="p-3">₹<?= $product['price'] ?></td>
                <td class="p-3"><?= $product['stock'] ?></td>
                <td class="p-3">
                    <form action="/admin/products/delete" method="POST" onsubmit="return confirm('Delete?');">
                        <input type="hidden" name="id" value="<?= $product['id'] ?>">
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
