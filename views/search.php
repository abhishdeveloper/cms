<?php include __DIR__ . '/header.php'; ?>

<main class="container mx-auto p-4 flex flex-col md:flex-row gap-8">

    <!-- Sidebar Filters -->
    <aside class="w-full md:w-64 bg-white p-4 rounded shadow h-fit">
        <h2 class="font-bold text-lg mb-4">Filters</h2>

        <form action="/search" method="GET">
            <?php if (!empty($_GET['q'])): ?>
                <input type="hidden" name="q" value="<?= htmlspecialchars($_GET['q']) ?>">
            <?php endif; ?>

            <div class="mb-4">
                <label class="block text-sm font-bold mb-1">Category</label>
                <select name="category" class="w-full border rounded p-2 text-sm">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= (isset($_GET['category']) && $_GET['category'] == $cat['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-bold mb-1">Price Range</label>
                <div class="flex gap-2">
                    <input type="number" name="min_price" placeholder="Min" value="<?= htmlspecialchars($_GET['min_price'] ?? '') ?>" class="w-1/2 border rounded p-2 text-sm">
                    <input type="number" name="max_price" placeholder="Max" value="<?= htmlspecialchars($_GET['max_price'] ?? '') ?>" class="w-1/2 border rounded p-2 text-sm">
                </div>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-bold mb-1">Sort By</label>
                <select name="sort" class="w-full border rounded p-2 text-sm">
                    <option value="newest" <?= (isset($_GET['sort']) && $_GET['sort'] == 'newest') ? 'selected' : '' ?>>Newest First</option>
                    <option value="price_asc" <?= (isset($_GET['sort']) && $_GET['sort'] == 'price_asc') ? 'selected' : '' ?>>Price: Low to High</option>
                    <option value="price_desc" <?= (isset($_GET['sort']) && $_GET['sort'] == 'price_desc') ? 'selected' : '' ?>>Price: High to Low</option>
                </select>
            </div>

            <button type="submit" class="w-full bg-blue-600 text-white font-bold py-2 rounded hover:bg-blue-700">Apply Filters</button>
            <a href="/search" class="block text-center text-sm text-gray-500 mt-2 hover:underline">Clear Filters</a>
        </form>
    </aside>

    <!-- Results -->
    <div class="flex-1">
        <h1 class="text-2xl font-bold mb-6">
            <?php if (!empty($_GET['q'])): ?>
                Search results for "<?= htmlspecialchars($_GET['q']) ?>"
            <?php elseif (!empty($_GET['category'])): ?>
                Category: <?= htmlspecialchars($categories[array_search($_GET['category'], array_column($categories, 'id'))]['name'] ?? 'Unknown') ?>
            <?php else: ?>
                All Products
            <?php endif; ?>
            <span class="text-gray-500 text-base font-normal">(<?= count($products) ?> items)</span>
        </h1>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
            <?php if (empty($products)): ?>
                <div class="col-span-full text-center py-10 text-gray-500">
                    No products found matching your criteria.
                </div>
            <?php else: ?>
                <?php foreach ($products as $product): ?>
                    <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow group">
                        <a href="/product?id=<?= $product['id'] ?>">
                            <img src="<?= htmlspecialchars($product['image_url'] ?: 'https://via.placeholder.com/300') ?>" class="w-full h-48 object-cover group-hover:scale-105 transition-transform">
                        </a>
                        <div class="p-4">
                            <a href="/product?id=<?= $product['id'] ?>" class="block">
                                <h2 class="text-lg font-semibold mb-2 group-hover:text-blue-600 line-clamp-1"><?= htmlspecialchars($product['name']) ?></h2>
                            </a>
                            <div class="flex justify-between items-center mt-4">
                                <span class="text-lg font-bold text-green-600">₹<?= number_format($product['price'], 2) ?></span>
                                <button class="add-to-cart-btn bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700 transition-colors"
                                        data-id="<?= $product['id'] ?>"
                                        data-name="<?= htmlspecialchars($product['name']) ?>"
                                        data-price="<?= $product['price'] ?>">
                                    Add
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

</main>

<script>
    document.querySelectorAll('.add-to-cart-btn').forEach(button => {
        button.addEventListener('click', function() {
            const id = parseInt(this.getAttribute('data-id'));
            const name = this.getAttribute('data-name');
            const price = parseFloat(this.getAttribute('data-price'));
            addToCart(id, name, price);
        });
    });
</script>

<?php include __DIR__ . '/footer.php'; ?>
