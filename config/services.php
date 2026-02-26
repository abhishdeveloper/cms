<?php

return [
    'whatsapp' => [
        'api_url' => getenv('WHATSAPP_API_URL') ?: 'https://api.whatsapp-gateway.com/send',
        'api_token' => getenv('WHATSAPP_API_TOKEN') ?: '',
    ],
    'razorpay' => [
        'key_id' => getenv('RAZORPAY_KEY_ID') ?: '',
        'key_secret' => getenv('RAZORPAY_KEY_SECRET') ?: '',
        'api_base_url' => 'https://api.razorpay.com/v1/',
    ],
];
