<?php

class RazorpayService
{
    private $config;

    public function __construct()
    {
        $services = require __DIR__ . '/../config/services.php';
        $this->config = $services['razorpay'];
    }

    public function createOrder($amount, $currency = 'INR')
    {
        $url = $this->config['api_base_url'] . 'orders';
        $keyId = $this->config['key_id'];
        $keySecret = $this->config['key_secret'];

        $data = [
            'amount' => $amount * 100, // Amount in paise
            'currency' => $currency,
            'payment_capture' => 1
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $keyId . ':' . $keySecret);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new Exception("Razorpay API Error: " . $error);
        }

        return json_decode($response, true);
    }

    public function verifySignature($attributes)
    {
        if (empty($attributes['razorpay_order_id']) || empty($attributes['razorpay_payment_id']) || empty($attributes['razorpay_signature'])) {
            return false;
        }

        $expectedSignature = hash_hmac(
            'sha256',
            $attributes['razorpay_order_id'] . '|' . $attributes['razorpay_payment_id'],
            $this->config['key_secret']
        );

        return hash_equals($expectedSignature, $attributes['razorpay_signature']);
    }
}
