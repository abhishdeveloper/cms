<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - E-Commerce Store</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">

<div class="bg-white p-8 rounded-lg shadow-lg max-w-md w-full">
    <h2 class="text-2xl font-bold mb-6 text-center text-gray-800">Login</h2>

    <!-- Tabs -->
    <div class="flex mb-4 border-b">
        <button onclick="switchTab('otp')" id="tab-otp" class="w-1/2 py-2 text-center border-b-2 border-blue-600 text-blue-600 font-bold focus:outline-none">OTP Login</button>
        <button onclick="switchTab('password')" id="tab-password" class="w-1/2 py-2 text-center text-gray-500 focus:outline-none">Password Login</button>
    </div>

    <!-- OTP Form -->
    <div id="otp-form">
        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2">WhatsApp Number</label>
            <input type="tel" id="otp-phone" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="1234567890">
        </div>
        <div id="otp-verify-section" class="hidden mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2">OTP</label>
            <input type="text" id="otp-code" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="1234">
        </div>
        <button onclick="handleOtpLogin()" id="btn-otp" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
            Send OTP
        </button>
    </div>

    <!-- Password Form -->
    <div id="password-form" class="hidden">
        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2">Email</label>
            <input type="email" id="email" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="admin@example.com">
        </div>
        <div class="mb-6">
            <label class="block text-gray-700 text-sm font-bold mb-2">Password</label>
            <input type="password" id="password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="********">
        </div>
        <button onclick="handlePasswordLogin()" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
            Login
        </button>
    </div>

    <p id="error-msg" class="text-red-500 text-sm mt-4 text-center hidden"></p>
</div>

<script>
    function switchTab(tab) {
        document.getElementById('error-msg').classList.add('hidden');
        if (tab === 'otp') {
            document.getElementById('otp-form').classList.remove('hidden');
            document.getElementById('password-form').classList.add('hidden');
            document.getElementById('tab-otp').classList.add('border-blue-600', 'text-blue-600', 'font-bold', 'border-b-2');
            document.getElementById('tab-otp').classList.remove('text-gray-500');
            document.getElementById('tab-password').classList.remove('border-blue-600', 'text-blue-600', 'font-bold', 'border-b-2');
            document.getElementById('tab-password').classList.add('text-gray-500');
        } else {
            document.getElementById('otp-form').classList.add('hidden');
            document.getElementById('password-form').classList.remove('hidden');
            document.getElementById('tab-password').classList.add('border-blue-600', 'text-blue-600', 'font-bold', 'border-b-2');
            document.getElementById('tab-password').classList.remove('text-gray-500');
            document.getElementById('tab-otp').classList.remove('border-blue-600', 'text-blue-600', 'font-bold', 'border-b-2');
            document.getElementById('tab-otp').classList.add('text-gray-500');
        }
    }

    async function handleOtpLogin() {
        const phone = document.getElementById('otp-phone').value;
        const otp = document.getElementById('otp-code').value;
        const verifySection = document.getElementById('otp-verify-section');
        const btn = document.getElementById('btn-otp');
        const errorMsg = document.getElementById('error-msg');

        errorMsg.classList.add('hidden');

        if (verifySection.classList.contains('hidden')) {
            // Send OTP
            try {
                const res = await fetch('/api/login', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({ phone })
                });
                const data = await res.json();
                if (data.error) throw new Error(data.error);

                verifySection.classList.remove('hidden');
                btn.innerText = "Verify & Login";
                alert("OTP sent!");
            } catch (e) {
                errorMsg.innerText = e.message;
                errorMsg.classList.remove('hidden');
            }
        } else {
            // Verify OTP
            try {
                const res = await fetch('/api/verify', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({ phone, otp, name: 'Customer' })
                });
                const data = await res.json();
                if (data.error) throw new Error(data.error);

                window.location.href = '/user/orders';
            } catch (e) {
                errorMsg.innerText = e.message;
                errorMsg.classList.remove('hidden');
            }
        }
    }

    async function handlePasswordLogin() {
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;
        const errorMsg = document.getElementById('error-msg');

        errorMsg.classList.add('hidden');

        try {
            const res = await fetch('/api/login-password', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ email, password })
            });
            const data = await res.json();
            if (data.error) throw new Error(data.error);

            window.location.href = data.redirect;
        } catch (e) {
            errorMsg.innerText = e.message;
            errorMsg.classList.remove('hidden');
        }
    }
</script>

</body>
</html>
