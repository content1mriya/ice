<?php
return [
    'site_name' => 'IceKlimat',
    'site_description' => 'Каталог кондиціонерів з підбором, консультацією та заявкою на замовлення.',
    'site_phone' => '+38 (000) 000-00-00',
    'site_email' => 'your-email@example.com',
    'site_city' => 'Україна',

    // Заміни ключ перед запуском імпорту.
    'import_key' => 'change_this_secret_key',

    // Для Telegram-заявок заповнити на наступному етапі.
    'telegram_bot_token' => '8648395324:AAGoh0v9JHH81ZPqv-SN3O5Xvmc0hsvgDyg',
    'telegram_chat_id' => '7738320348',

    'brands' => [
        'tcl' => [
            'name' => 'TCL',
            'title' => 'Кондиціонери TCL',
            'description' => 'Окрема сторінка бренду TCL. Тут будуть показані всі активні товари цього бренду з Excel-файлу.',
        ],
        'hisense' => [
            'name' => 'Hisense',
            'title' => 'Кондиціонери Hisense',
            'description' => 'Окрема сторінка бренду Hisense. Тут будуть показані всі активні товари цього бренду з Excel-файлу.',
        ],
        'skylux' => [
            'name' => 'SkyLux',
            'title' => 'Кондиціонери SkyLux',
            'description' => 'Окрема сторінка бренду SkyLux. Тут будуть показані всі активні товари цього бренду з Excel-файлу.',
        ],
    ],
];
