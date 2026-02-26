<?php include __DIR__ . '/header.php'; ?>

<!-- Categories Section -->
<?php if (!empty($categories)): ?>
<h2 class="text-2xl font-bold mb-4 text-gray-800">Shop by Category</h2>
<div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4 mb-10">
    <?php foreach ($categories as $cat): ?>
        <a href="/category?id=<?= $cat['id'] ?>" class="block group">
            <div class="bg-white rounded-lg shadow overflow-hidden aspect-square flex items-center justify-center">
                <?php if ($cat['image_url']): ?>
                    <img src="<?= $cat['image_url'] ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform">
                <?php else: ?>
                    <span class="text-gray-400 font-bold"><?= htmlspecialchars($cat['name']) ?></span>
                <?php endif; ?>
            </div>
            <p class="text-center mt-2 font-medium text-gray-700 group-hover:text-blue-600"><?= htmlspecialchars($cat['name']) ?></p>
        </a>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<h1 class="text-3xl font-bold mb-6 text-gray-800">Featured Products</h1>

<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
    <?php if (empty($products)): ?>
        <p class="text-gray-500">No products available.</p>
    <?php else: ?>
        <?php foreach ($products as $product): ?>
            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow group">
                <a href="/product?id=<?= $product['id'] ?>">
                    <img src="<?= htmlspecialchars($product['image_url'] ?: 'https://via.placeholder.com/300') ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="w-full h-48 object-cover group-hover:scale-105 transition-transform">
                </a>
                <div class="p-4">
                    <a href="/product?id=<?= $product['id'] ?>" class="block">
                        <h2 class="text-xl font-semibold mb-2 group-hover:text-blue-600"><?= htmlspecialchars($product['name']) ?></h2>
                    </a>
                    <p class="text-gray-600 text-sm mb-4 line-clamp-2"><?= htmlspecialchars($product['description']) ?></p>
                    <div class="flex justify-between items-center">
                        <span class="text-lg font-bold text-green-600">₹<?= number_format($product['price'], 2) ?></span>
                        <button class="add-to-cart-btn bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition-colors"
                                data-id="<?= $product['id'] ?>"
                                data-name="<?= htmlspecialchars($product['name']) ?>"
                                data-price="<?= $product['price'] ?>">
                            Add to Cart
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

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
