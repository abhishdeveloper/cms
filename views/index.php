<?php include 'header.php'; ?>

<h1 class="text-3xl font-bold mb-6 text-gray-800">Our Products</h1>

<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
    <?php if (empty($products)): ?>
        <p class="text-gray-500">No products available.</p>
    <?php else: ?>
        <?php foreach ($products as $product): ?>
            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                <img src="<?= htmlspecialchars($product['image_url'] ?: 'https://via.placeholder.com/300') ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="w-full h-48 object-cover">
                <div class="p-4">
                    <h2 class="text-xl font-semibold mb-2"><?= htmlspecialchars($product['name']) ?></h2>
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

<?php include 'footer.php'; ?>
