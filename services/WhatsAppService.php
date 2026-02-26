<?php

class WhatsAppService
{
    private $config;

    public function __construct()
    {
        $services = require __DIR__ . '/../config/services.php';
        $this->config = $services['whatsapp'];
    }

    public function sendMessage($phone, $message)
    {
        $url = $this->config['api_url'];
        $token = $this->config['api_token'];

        $payload = json_encode([
            'phone' => $phone,
            'message' => $message
        ]);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token
        ]);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new Exception("WhatsApp API Error: " . $error);
        }

        return json_decode($response, true);
    }

    public function sendOTP($phone)
    {
        $otp = random_int(1000, 9999);

        // Store OTP in session
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION["otp_{$phone}"] = $otp;

        $message = "Your verification code is: {$otp}";

        return $this->sendMessage($phone, $message);
    }
}
