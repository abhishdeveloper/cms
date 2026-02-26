<?php include 'header.php'; ?>

    <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-lg p-6 mt-6">
        <h1 class="text-3xl font-bold mb-6 text-gray-800">Your Cart</h1>

        <div id="cart-items" class="space-y-4">
            <!-- Items populated by JS -->
        </div>

        <div id="empty-cart-msg" class="hidden text-center text-gray-500 py-10">
            Your cart is empty. <a href="/" class="text-blue-600 hover:underline">Start shopping</a>
        </div>

        <div id="cart-summary" class="mt-8 border-t pt-6 hidden">
            <div class="flex justify-between items-center text-xl font-bold mb-6">
                <span>Total</span>
                <span id="cart-total" class="text-green-600">₹0.00</span>
            </div>

            <div class="bg-gray-50 p-6 rounded-lg border border-gray-200">
                <h2 class="text-lg font-semibold mb-4">Checkout Details</h2>

                <div class="mb-4">
                    <label for="phone" class="block text-gray-700 text-sm font-bold mb-2">WhatsApp Number</label>
                    <div class="flex">
                        <input type="tel" id="phone" placeholder="9876543210" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline mr-2">
                        <button onclick="sendOTP()" id="btn-send-otp" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            Send OTP
                        </button>
                    </div>
                </div>

                <div id="otp-section" class="mb-4 hidden">
                    <label for="otp" class="block text-gray-700 text-sm font-bold mb-2">Enter OTP</label>
                    <div class="flex">
                        <input type="text" id="otp" placeholder="1234" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline mr-2">
                        <button onclick="verifyOTP()" id="btn-verify-otp" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            Verify
                        </button>
                    </div>
                </div>

                <div id="name-section" class="mb-4 hidden">
                     <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Full Name (New Users)</label>
                     <input type="text" id="name" placeholder="John Doe" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>

                <button onclick="proceedToCheckout()" id="btn-checkout" disabled class="w-full bg-gray-400 text-white font-bold py-3 px-4 rounded focus:outline-none focus:shadow-outline cursor-not-allowed transition-colors">
                    Proceed to Payment
                </button>
            </div>
        </div>
    </div>

    <script>
        let cart = JSON.parse(localStorage.getItem('cart')) || [];
        let userId = null;
        const razorpayKey = "<?= getenv('RAZORPAY_KEY_ID') ?>"; // In production, pass securely

        function renderCart() {
            const container = document.getElementById('cart-items');
            const summary = document.getElementById('cart-summary');
            const emptyMsg = document.getElementById('empty-cart-msg');
            const totalEl = document.getElementById('cart-total');

            container.innerHTML = '';
            let total = 0;

            if (cart.length === 0) {
                summary.classList.add('hidden');
                emptyMsg.classList.remove('hidden');
                return;
            }

            summary.classList.remove('hidden');
            emptyMsg.classList.add('hidden');

            cart.forEach((item, index) => {
                const itemTotal = item.price * item.quantity;
                total += itemTotal;

                const div = document.createElement('div');
                div.className = "flex justify-between items-center bg-white p-4 rounded shadow-sm border";
                div.innerHTML = `
                    <div>
                        <h3 class="font-semibold text-lg">${item.name}</h3>
                        <p class="text-gray-600">₹${item.price} x ${item.quantity}</p>
                    </div>
                    <div class="flex items-center">
                        <span class="font-bold mr-4">₹${itemTotal.toFixed(2)}</span>
                        <button onclick="removeItem(${index})" class="text-red-500 hover:text-red-700">Remove</button>
                    </div>
                `;
                container.appendChild(div);
            });

            totalEl.innerText = '₹' + total.toFixed(2);
        }

        function removeItem(index) {
            cart.splice(index, 1);
            localStorage.setItem('cart', JSON.stringify(cart));
            renderCart();
        }

        async function sendOTP() {
            const phone = document.getElementById('phone').value;
            if (!phone) return alert('Please enter phone number');

            try {
                const res = await fetch('/api/login', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({ phone })
                });
                const data = await res.json();

                if (data.error) throw new Error(data.error);

                document.getElementById('otp-section').classList.remove('hidden');
                document.getElementById('name-section').classList.remove('hidden');
                alert('OTP sent via WhatsApp!');
            } catch (e) {
                alert(e.message);
            }
        }

        async function verifyOTP() {
            const phone = document.getElementById('phone').value;
            const otp = document.getElementById('otp').value;
            const name = document.getElementById('name').value;

            if (!phone || !otp) return alert('Enter Phone and OTP');

            try {
                const res = await fetch('/api/verify', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({ phone, otp, name })
                });
                const data = await res.json();

                if (data.error) throw new Error(data.error);

                userId = data.user_id;
                const btn = document.getElementById('btn-checkout');
                btn.disabled = false;
                btn.classList.remove('bg-gray-400', 'cursor-not-allowed');
                btn.classList.add('bg-blue-600', 'hover:bg-blue-800');

                document.getElementById('otp-section').classList.add('hidden');
                document.getElementById('btn-send-otp').classList.add('hidden');
                document.getElementById('phone').disabled = true;

                alert('Verified! You can now checkout.');
            } catch (e) {
                alert(e.message);
            }
        }

        async function proceedToCheckout() {
            if (!userId) return alert('Please verify first');

            try {
                const res = await fetch('/checkout', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({ cart_items: cart })
                });

                const data = await res.json();
                if (data.error) throw new Error(data.error);

                const options = {
                    "key": razorpayKey,
                    "amount": data.amount * 100,
                    "currency": data.currency,
                    "name": "E-Commerce Store",
                    "description": "Order #" + data.order_id,
                    "order_id": data.razorpay_order_id,
                    "handler": function (response){
                        // Payment Success
                        localStorage.removeItem('cart');
                        window.location.href = `/success?order_id=${data.order_id}&payment_id=${response.razorpay_payment_id}`;
                    },
                    "theme": {
                        "color": "#2563EB"
                    }
                };

                const rzp1 = new Razorpay(options);
                rzp1.open();

            } catch (e) {
                alert('Checkout Failed: ' + e.message);
            }
        }

        renderCart();
    </script>

<?php include 'footer.php'; ?>
