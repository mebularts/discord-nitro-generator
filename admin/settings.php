<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();
    $settings = [
        'app_name'          => $_POST['app_name'] ?? 'SolveClone',
        'meta_title'        => $_POST['meta_title'] ?? '',
        'meta_description'  => $_POST['meta_description'] ?? '',
        'theme_color'       => $_POST['theme_color'] ?? '#0ea5e9',
        'default_locale'    => $_POST['default_locale'] ?? 'en',
        'deepl_auth_key'    => $_POST['deepl_auth_key'] ?? '',
        'custom_head_html'  => $_POST['custom_head_html'] ?? '',
        'custom_body_html'  => $_POST['custom_body_html'] ?? '',
    ];

    foreach ($settings as $key => $value) {
        App\Models\Setting::set($key, $value);
    }

    flash('success', 'Settings updated');
    redirect('/admin/settings.php');
}

$all = App\Models\Setting::all();

echo admin_view('settings', [
    'title' => 'Settings',
    'active' => 'settings',
    'settings' => $all,
    'flash' => flash('success'),
]);
