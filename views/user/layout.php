<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">

<nav class="bg-white shadow-md p-4 sticky top-0 z-50">
    <div class="container mx-auto flex justify-between items-center">
        <a href="/" class="text-xl font-bold text-blue-600">My Store</a>
        <div class="space-x-4">
            <a href="/user/orders" class="text-gray-700 hover:text-blue-600">My Orders</a>
            <a href="/user/profile" class="text-gray-700 hover:text-blue-600">Profile</a>
            <a href="/logout" class="text-red-500 hover:text-red-700">Logout</a>
        </div>
    </div>
</nav>

<main class="container mx-auto p-8">
