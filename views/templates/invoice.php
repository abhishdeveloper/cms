<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice #<?= $order['id'] ?></title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 14px; line-height: 1.6; color: #333; }
        .invoice-box { max-width: 800px; margin: auto; padding: 30px; border: 1px solid #eee; box-shadow: 0 0 10px rgba(0, 0, 0, .15); }
        .header { margin-bottom: 20px; }
        .header h1 { margin: 0; color: #333; }
        .details { margin-bottom: 30px; }
        .details table { width: 100%; }
        .details td { vertical-align: top; }
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .items-table th, .items-table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .items-table th { background-color: #f2f2f2; }
        .totals { text-align: right; }
        .footer { margin-top: 50px; text-align: center; font-size: 12px; color: #777; }
    </style>
</head>
<body>
    <div class="invoice-box">
        <div class="header">
            <table style="width: 100%;">
                <tr>
                    <td>
                        <h1>INVOICE</h1>
                        <p><strong>Order ID:</strong> #<?= $order['id'] ?></p>
                        <p><strong>Date:</strong> <?= date('M d, Y', strtotime($order['created_at'])) ?></p>
                    </td>
                    <td style="text-align: right;">
                        <h2>My Store</h2>
                        <p>123 Commerce St.<br>City, State, Zip<br>support@mystore.com</p>
                    </td>
                </tr>
            </table>
        </div>

        <div class="details">
            <strong>Bill To:</strong><br>
            <?= htmlspecialchars($order['name']) ?><br>
            <?= htmlspecialchars($order['phone']) ?><br>
            <?= htmlspecialchars($order['email']) ?>
        </div>

        <table class="items-table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Price</th>
                    <th>Qty</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                <tr>
                    <td><?= htmlspecialchars($item['product_name']) ?></td>
                    <td>₹<?= number_format($item['price'], 2) ?></td>
                    <td><?= $item['quantity'] ?></td>
                    <td>₹<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="totals">
            <?php
            $subtotal = $order['total_amount'] + ($order['discount_amount'] ?? 0);
            ?>
            <p><strong>Subtotal:</strong> ₹<?= number_format($subtotal, 2) ?></p>
            <?php if (!empty($order['discount_amount']) && $order['discount_amount'] > 0): ?>
                <p style="color: green;"><strong>Discount:</strong> -₹<?= number_format($order['discount_amount'], 2) ?></p>
            <?php endif; ?>
            <p style="font-size: 18px;"><strong>Total:</strong> ₹<?= number_format($order['total_amount'], 2) ?></p>
        </div>

        <div class="footer">
            <p>Thank you for your business!</p>
        </div>
    </div>
</body>
</html>
