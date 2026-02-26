<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Commerce Store</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <script>
        function updateCartCount() {
            const cart = JSON.parse(localStorage.getItem('cart') || '[]');
            const count = cart.reduce((acc, item) => acc + item.quantity, 0);
            const badge = document.getElementById('cart-count');
            if(badge) badge.innerText = count;
        }

        function addToCart(id, name, price) {
            let cart = JSON.parse(localStorage.getItem('cart') || '[]');
            const existing = cart.find(item => item.product_id === id);

            if (existing) {
                existing.quantity += 1;
            } else {
                cart.push({ product_id: id, name: name, price: price, quantity: 1 });
            }

            localStorage.setItem('cart', JSON.stringify(cart));
            updateCartCount();
            alert('Item added to cart!');
        }

        document.addEventListener('DOMContentLoaded', updateCartCount);
    </script>
</head>
<body class="bg-gray-100 min-h-screen">
    <nav class="bg-white shadow-md p-4 sticky top-0 z-50">
        <div class="container mx-auto flex justify-between items-center">
            <a href="/" class="text-xl font-bold text-blue-600 flex items-center">
                <span>My Store</span>
            </a>

            <div class="hidden md:flex space-x-6 items-center">
                <a href="/" class="text-gray-600 hover:text-blue-600 font-medium">Home</a>

                <!-- Category Dropdown -->
                <div class="relative group">
                    <button class="text-gray-600 hover:text-blue-600 font-medium flex items-center">
                        Categories
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <div class="absolute left-0 mt-2 w-48 bg-white border rounded shadow-lg hidden group-hover:block z-50">
                        <?php
                        // In a real app, use a View Composer or Helper. Here we try to access $categories if set, else fetch.
                        $db = Database::getInstance();
                        $catStmt = $db->query("SELECT * FROM categories ORDER BY name ASC");
                        $menuCategories = $catStmt->fetchAll();
                        foreach ($menuCategories as $cat):
                        ?>
                            <a href="/category?id=<?= $cat['id'] ?>" class="block px-4 py-2 text-gray-700 hover:bg-blue-50"><?= htmlspecialchars($cat['name']) ?></a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <a href="/search" class="text-gray-600 hover:text-blue-600 font-medium">Search</a>
            </div>

            <div class="flex items-center space-x-4">
                <form action="/search" method="GET" class="hidden md:block">
                    <input type="text" name="q" placeholder="Search products..." class="border rounded-full px-4 py-1 text-sm focus:outline-none focus:border-blue-500">
                </form>

                <a href="/cart" class="relative text-gray-700 hover:text-blue-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <span id="cart-count" class="absolute -top-2 -right-2 bg-red-500 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center">0</span>
                </a>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="/user/profile" class="text-gray-700 hover:text-blue-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    </a>
                <?php else: ?>
                    <a href="/login" class="text-sm font-medium text-blue-600 hover:underline">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    <main class="container mx-auto p-4">
