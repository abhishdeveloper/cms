<?php

class CouponService
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function validateAndCalculate($code, $cartTotal)
    {
        $stmt = $this->db->query("SELECT * FROM coupons WHERE code = ? AND is_active = 1", [$code]);
        $coupon = $stmt->fetch();

        if (!$coupon) {
            throw new Exception("Invalid or inactive coupon code.");
        }

        // Check expiry
        if ($coupon['expires_at'] && strtotime($coupon['expires_at']) < time()) {
            throw new Exception("This coupon has expired.");
        }

        // Check usage limit
        if ($coupon['usage_limit'] !== null && $coupon['times_used'] >= $coupon['usage_limit']) {
            throw new Exception("This coupon has reached its usage limit.");
        }

        // Check minimum cart value
        if ($cartTotal < $coupon['min_cart_value']) {
            throw new Exception("Minimum cart value of ₹" . number_format($coupon['min_cart_value'], 2) . " required.");
        }

        // Calculate discount
        $discountAmount = 0;
        if ($coupon['type'] === 'percentage') {
            $discountAmount = ($cartTotal * $coupon['discount_value']) / 100;
        } else {
            $discountAmount = $coupon['discount_value'];
        }

        // Ensure discount doesn't exceed total
        if ($discountAmount > $cartTotal) {
            $discountAmount = $cartTotal;
        }

        return [
            'amount' => $discountAmount,
            'coupon_id' => $coupon['id'],
            'code' => $coupon['code']
        ];
    }
}
