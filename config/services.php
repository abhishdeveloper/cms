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
];
