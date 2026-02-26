<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex min-h-screen">

<aside class="w-64 bg-gray-800 text-white flex flex-col">
    <div class="h-16 flex items-center justify-center font-bold text-xl border-b border-gray-700">Admin Panel</div>
    <nav class="flex-1 p-4 space-y-2">
        <a href="/admin/dashboard" class="block py-2 px-4 rounded hover:bg-gray-700">Dashboard</a>
        <a href="/admin/products" class="block py-2 px-4 rounded hover:bg-gray-700">Products</a>
        <a href="/admin/orders" class="block py-2 px-4 rounded hover:bg-gray-700">Orders</a>
        <a href="/admin/coupons" class="block py-2 px-4 rounded hover:bg-gray-700">Coupons</a>
    </nav>
    <div class="p-4 border-t border-gray-700">
        <a href="/logout" class="block py-2 px-4 text-red-400 hover:text-red-300">Logout</a>
    </div>
</aside>

<main class="flex-1 p-8">
