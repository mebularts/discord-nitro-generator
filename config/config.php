<?php
return [
    'app' => [
        'name' => getenv('APP_NAME') ?: 'Telegram SMS Mağazası',
        'url' => getenv('APP_URL') ?: 'https://example.com',
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
            'enabled' => filter_var(getenv('PAYMENT_TELEGRAM_STARS_ENABLED') ?: 'true', FILTER_VALIDATE_BOOLEAN),
            'provider_token' => getenv('PAYMENT_TELEGRAM_PROVIDER_TOKEN') ?: '',
            'title' => getenv('PAYMENT_TELEGRAM_STARS_TITLE') ?: 'Telegram Stars ile Bakiye',
            'description' => getenv('PAYMENT_TELEGRAM_STARS_DESCRIPTION') ?: 'Telegram Stars ile hızlı bakiye yükleyin.',
            'packages' => [
                [
                    'label' => getenv('PAYMENT_TELEGRAM_STARS_PACKAGE_LABEL') ?: '100 Yıldız',
                    'stars' => (int) (getenv('PAYMENT_TELEGRAM_STARS_PACKAGE_STARS') ?: 100),
                    'price' => (float) (getenv('PAYMENT_TELEGRAM_STARS_PACKAGE_PRICE') ?: 100),
                ],
            ],
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
        'nowpayments' => [
            'enabled' => filter_var(getenv('PAYMENT_NOWPAYMENTS_ENABLED') ?: 'true', FILTER_VALIDATE_BOOLEAN),
            'api_key' => getenv('NOWPAYMENTS_API_KEY') ?: '',
            'base_url' => getenv('NOWPAYMENTS_BASE_URL') ?: 'https://api.nowpayments.io/v1',
            'price_currency' => getenv('NOWPAYMENTS_PRICE_CURRENCY') ?: 'USD',
            'pay_currency' => getenv('NOWPAYMENTS_PAY_CURRENCY') ?: 'USDT',
            'default_amount' => (float) (getenv('NOWPAYMENTS_DEFAULT_AMOUNT') ?: 100),
            'success_url' => getenv('NOWPAYMENTS_SUCCESS_URL') ?: (getenv('APP_URL') ? getenv('APP_URL') . '/payments/success' : 'https://example.com/payments/success'),
            'cancel_url' => getenv('NOWPAYMENTS_CANCEL_URL') ?: (getenv('APP_URL') ? getenv('APP_URL') . '/payments/cancel' : 'https://example.com/payments/cancel'),
            'ipn_secret' => getenv('NOWPAYMENTS_IPN_SECRET') ?: '',
        ],
    ],
    'admin' => [
        'panel_token' => getenv('ADMIN_PANEL_TOKEN') ?: 'change-me',
    ],
    'database' => [
        'path' => getenv('DATABASE_PATH') ?: (__DIR__ . '/../storage/database.sqlite'),
    ],
    'catalog' => [
        'default_price' => (float) (getenv('CATALOG_DEFAULT_PRICE') ?: 25.0),
        'markup_percent' => (float) (getenv('CATALOG_MARKUP_PERCENT') ?: 0),
        'markup_fixed' => (float) (getenv('CATALOG_MARKUP_FIXED') ?: 0),
    ],
];
