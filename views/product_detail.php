<?php include __DIR__ . '/header.php'; ?>

<main class="container mx-auto p-6">
    <div class="flex flex-col md:flex-row gap-8">
        <!-- Gallery -->
        <div class="w-full md:w-1/2">
            <div class="mb-4 aspect-square rounded-lg overflow-hidden border">
                <img id="main-image" src="<?= htmlspecialchars($product['image_url'] ?: 'https://via.placeholder.com/600') ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="w-full h-full object-contain">
            </div>

            <?php if (!empty($gallery)): ?>
                <div class="flex gap-2 overflow-x-auto pb-2">
                    <?php foreach ($gallery as $img): ?>
                        <button onclick="document.getElementById('main-image').src='<?= $img ?>'" class="border rounded p-1 w-20 h-20 flex-shrink-0 hover:border-blue-500 focus:outline-none">
                            <img src="<?= $img ?>" class="w-full h-full object-cover">
                        </button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Details -->
        <div class="w-full md:w-1/2">
            <div class="mb-2 text-sm text-gray-500">
                <a href="/" class="hover:text-blue-600">Home</a> &rsaquo;
                <a href="/category?id=<?= $product['category_id'] ?>" class="hover:text-blue-600"><?= htmlspecialchars($product['category_name']) ?></a>
            </div>

            <h1 class="text-3xl font-bold text-gray-900 mb-2"><?= htmlspecialchars($product['name']) ?></h1>

            <!-- Rating Summary -->
            <div class="flex items-center mb-4">
                <div class="flex text-yellow-400">
                    <?php for($i=1; $i<=5; $i++): ?>
                        <svg class="w-5 h-5 <?= $i <= $avgRating ? 'fill-current' : 'text-gray-300' ?>" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    <?php endfor; ?>
                </div>
                <span class="ml-2 text-gray-600 text-sm">(<?= count($reviews) ?> reviews)</span>
            </div>

            <p class="text-3xl font-bold text-green-600 mb-4">₹<?= number_format($product['price'], 2) ?></p>

            <!-- Stock Status -->
            <div class="mb-6">
                <?php if ($product['stock'] > 5): ?>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">In Stock</span>
                <?php elseif ($product['stock'] > 0): ?>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-orange-100 text-orange-800">Only <?= $product['stock'] ?> left!</span>
                <?php else: ?>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">Out of Stock</span>
                <?php endif; ?>
            </div>

            <p class="text-gray-700 leading-relaxed mb-8"><?= nl2br(htmlspecialchars($product['description'])) ?></p>

            <div class="flex items-center gap-4">
                <?php if ($product['stock'] > 0): ?>
                    <button class="add-to-cart-btn bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-lg shadow transition-colors"
                            data-id="<?= $product['id'] ?>"
                            data-name="<?= htmlspecialchars($product['name']) ?>"
                            data-price="<?= $product['price'] ?>">
                        Add to Cart
                    </button>
                <?php else: ?>
                    <button disabled class="bg-gray-400 text-white font-bold py-3 px-8 rounded-lg cursor-not-allowed">
                        Out of Stock
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Related Products -->
    <?php if (!empty($related)): ?>
        <div class="mt-16 border-t pt-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">You might also like</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
                <?php foreach ($related as $rel): ?>
                    <a href="/product?id=<?= $rel['id'] ?>" class="group block bg-white rounded-lg shadow hover:shadow-lg transition-shadow overflow-hidden">
                        <div class="aspect-square bg-gray-100 overflow-hidden">
                            <img src="<?= htmlspecialchars($rel['image_url'] ?: 'https://via.placeholder.com/300') ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform">
                        </div>
                        <div class="p-4">
                            <h3 class="font-bold text-gray-800 group-hover:text-blue-600 line-clamp-1"><?= htmlspecialchars($rel['name']) ?></h3>
                            <p class="text-green-600 font-bold mt-1">₹<?= number_format($rel['price'], 2) ?></p>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Reviews Section -->
    <div class="mt-16 border-t pt-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Customer Reviews</h2>

        <?php if ($canReview): ?>
            <div class="bg-gray-50 p-6 rounded-lg mb-8 border">
                <h3 class="font-bold text-lg mb-4">Write a Review</h3>
                <form action="/product/review" method="POST">
                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                    <div class="mb-4">
                        <label class="block text-sm font-bold mb-1">Rating</label>
                        <select name="rating" class="border rounded p-2 w-full max-w-xs" required>
                            <option value="5">⭐⭐⭐⭐⭐ (Excellent)</option>
                            <option value="4">⭐⭐⭐⭐ (Good)</option>
                            <option value="3">⭐⭐⭐ (Average)</option>
                            <option value="2">⭐⭐ (Poor)</option>
                            <option value="1">⭐ (Terrible)</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-bold mb-1">Comment</label>
                        <textarea name="comment" rows="3" class="w-full border rounded p-2" required></textarea>
                    </div>
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded font-bold hover:bg-blue-700">Submit Review</button>
                </form>
            </div>
        <?php elseif (isset($_SESSION['user_id'])): ?>
            <!-- Logged in but not eligible -->
        <?php else: ?>
            <p class="mb-8"><a href="/login" class="text-blue-600 font-bold hover:underline">Login</a> to write a review.</p>
        <?php endif; ?>

        <div class="space-y-6">
            <?php foreach ($reviews as $rev): ?>
                <div class="border-b pb-6">
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-bold text-gray-800"><?= htmlspecialchars($rev['user_name']) ?></span>
                        <span class="text-xs text-gray-500"><?= date('M d, Y', strtotime($rev['created_at'])) ?></span>
                    </div>
                    <div class="flex text-yellow-400 mb-2">
                        <?php for($i=1; $i<=5; $i++): ?>
                            <svg class="w-4 h-4 <?= $i <= $rev['rating'] ? 'fill-current' : 'text-gray-300' ?>" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        <?php endfor; ?>
                    </div>
                    <p class="text-gray-700"><?= nl2br(htmlspecialchars($rev['comment'])) ?></p>
                </div>
            <?php endforeach; ?>
            <?php if (empty($reviews)): ?>
                <p class="text-gray-500 italic">No reviews yet.</p>
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
