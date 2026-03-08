<?php

return [
    'whatsapp' => [
        'api_url' => getenv('WHATSAPP_API_URL') ?: 'https://api.whatsapp-gateway.com/send',
        'api_token' => getenv('WHATSAPP_API_TOKEN') ?: '',
        'admin_phone' => getenv('ADMIN_PHONE') ?: '',
    ],
    'razorpay' => [
        'key_id' => getenv('RAZORPAY_KEY_ID') ?: '',
        'key_secret' => getenv('RAZORPAY_KEY_SECRET') ?: '',
        'webhook_secret' => getenv('RAZORPAY_WEBHOOK_SECRET') ?: '',
        'api_base_url' => 'https://api.razorpay.com/v1/',
    ],
    'smtp' => [
        'host' => getenv('MAIL_HOST') ?: 'smtp.example.com',
        'port' => getenv('MAIL_PORT') ?: 587,
        'username' => getenv('MAIL_USER') ?: '',
        'password' => getenv('MAIL_PASS') ?: '',
        'from_email' => getenv('MAIL_FROM_ADDRESS') ?: 'no-reply@ecommerce.com',
        'from_name' => getenv('MAIL_FROM_NAME') ?: 'E-Commerce Store',
    ],
];
