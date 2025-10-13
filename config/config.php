<?php
return [
    'app' => [
        'name' => getenv('APP_NAME') ?: 'Telegram SMS Mağazası',
    ],
    'telegram' => [
        'bot_token' => getenv('TELEGRAM_BOT_TOKEN') ?: '',
        'admin_chat_id' => getenv('TELEGRAM_ADMIN_CHAT_ID') ?: '',
        'webhook_secret' => getenv('TELEGRAM_WEBHOOK_SECRET') ?: '',
    ],
    'providers' => [
        'preferred' => getenv('SMS_PROVIDER') ?: '5sim',
        '5sim' => [
            'api_key' => getenv('FIVESIM_API_KEY') ?: '',
            'base_url' => 'https://5sim.net/v1',
        ],
        'sms_activate' => [
            'api_key' => getenv('SMS_ACTIVATE_API_KEY') ?: '',
            'base_url' => 'https://api.sms-activate.org',
        ],
    ],
    'payments' => [
        'telegram_stars' => [
            'enabled' => true,
        ],
        'iban' => [
            'enabled' => true,
            'iban' => getenv('PAYMENT_IBAN') ?: '',
            'holder' => getenv('PAYMENT_IBAN_HOLDER') ?: '',
        ],
        'crypto' => [
            'enabled' => true,
            'address' => getenv('PAYMENT_CRYPTO_ADDRESS') ?: '',
        ],
        'online_crypto' => [
            'enabled' => true,
            'provider' => getenv('PAYMENT_ONLINE_CRYPTO_PROVIDER') ?: '',
        ],
    ],
    'admin' => [
        'panel_token' => getenv('ADMIN_PANEL_TOKEN') ?: 'change-me',
    ],
    'database' => [
        'path' => getenv('DATABASE_PATH') ?: (__DIR__ . '/../storage/database.sqlite'),
    ],
];
