<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

use App\Models\Setting;

$alerts = [];

$fields = [
    'site.name',
    'site.logo_url',
    'site.url',
    'theme.primary_color',
    'seo.meta_description',
    'footer.html',
    'ads.header',
    'ads.sidebar',
    'ads.inline',
    'ads.footer',
    'custom.head_css',
    'custom.head_js',
    'custom.body_css',
    'custom.body_js',
    'i18n.default_locale',
    'i18n.enabled_locales',
    'deepl.api_key',
    'pwa.theme_color',
    'pwa.background_color',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    foreach ($fields as $field) {
        $key = str_replace('.', '_', $field);
        Setting::set($field, $_POST[$key] ?? '');
    }
    Setting::reset();
    $alerts[] = ['type' => 'success', 'message' => __('admin.settings.updated', 'Settings saved successfully.')];
}

$values = [];
foreach ($fields as $field) {
    $values[$field] = Setting::get($field, '');
}

admin_layout(
    __('admin.settings.title', 'Site settings'),
    function () use ($values) {
        ?>
        <form method="post" class="card shadow-sm">
          <div class="card-body row g-3">
            <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
            <div class="col-md-4">
              <label class="form-label">Site name</label>
              <input name="site_name" class="form-control" value="<?= h($values['site.name'] ?? '') ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label">Logo URL</label>
              <input name="site_logo_url" class="form-control" value="<?= h($values['site.logo_url'] ?? '') ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label">Site URL</label>
              <input name="site_url" class="form-control" value="<?= h($values['site.url'] ?? '') ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label">Primary color</label>
              <input name="theme_primary_color" class="form-control" value="<?= h($values['theme.primary_color'] ?? '#4f46e5') ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label">Theme color</label>
              <input name="pwa_theme_color" class="form-control" value="<?= h($values['pwa.theme_color'] ?? '#4f46e5') ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label">Background color</label>
              <input name="pwa_background_color" class="form-control" value="<?= h($values['pwa.background_color'] ?? '#ffffff') ?>">
            </div>
            <div class="col-12">
              <label class="form-label">Meta description</label>
              <textarea name="seo_meta_description" class="form-control" rows="2"><?= h($values['seo.meta_description'] ?? '') ?></textarea>
            </div>
            <div class="col-12">
              <label class="form-label">Footer HTML</label>
              <textarea name="footer_html" class="form-control" rows="2"><?= h($values['footer.html'] ?? '') ?></textarea>
            </div>
            <div class="col-md-6">
              <label class="form-label">Header ad</label>
              <textarea name="ads_header" class="form-control" rows="2"><?= h($values['ads.header'] ?? '') ?></textarea>
            </div>
            <div class="col-md-6">
              <label class="form-label">Sidebar ad</label>
              <textarea name="ads_sidebar" class="form-control" rows="2"><?= h($values['ads.sidebar'] ?? '') ?></textarea>
            </div>
            <div class="col-md-6">
              <label class="form-label">Inline ad</label>
              <textarea name="ads_inline" class="form-control" rows="2"><?= h($values['ads.inline'] ?? '') ?></textarea>
            </div>
            <div class="col-md-6">
              <label class="form-label">Footer ad</label>
              <textarea name="ads_footer" class="form-control" rows="2"><?= h($values['ads.footer'] ?? '') ?></textarea>
            </div>
            <div class="col-md-6">
              <label class="form-label">Custom head CSS</label>
              <textarea name="custom_head_css" class="form-control" rows="2"><?= h($values['custom.head_css'] ?? '') ?></textarea>
            </div>
            <div class="col-md-6">
              <label class="form-label">Custom head JS</label>
              <textarea name="custom_head_js" class="form-control" rows="2"><?= h($values['custom.head_js'] ?? '') ?></textarea>
            </div>
            <div class="col-md-6">
              <label class="form-label">Custom body CSS</label>
              <textarea name="custom_body_css" class="form-control" rows="2"><?= h($values['custom.body_css'] ?? '') ?></textarea>
            </div>
            <div class="col-md-6">
              <label class="form-label">Custom body JS</label>
              <textarea name="custom_body_js" class="form-control" rows="2"><?= h($values['custom.body_js'] ?? '') ?></textarea>
            </div>
            <div class="col-md-4">
              <label class="form-label">Default locale</label>
              <input name="i18n_default_locale" class="form-control" value="<?= h($values['i18n.default_locale'] ?? 'en') ?>">
            </div>
            <div class="col-md-8">
              <label class="form-label">Enabled locales (JSON)</label>
              <input name="i18n_enabled_locales" class="form-control" value="<?= h($values['i18n.enabled_locales'] ?? '["en","tr"]') ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">DeepL API key</label>
              <input name="deepl_api_key" class="form-control" value="<?= h($values['deepl.api_key'] ?? '') ?>">
            </div>
            <div class="col-12 text-end">
              <button class="btn btn-primary"><?= __('admin.forms.save', 'Save') ?></button>
            </div>
          </div>
        </form>
        <?php
    },
    [
        'active' => 'settings',
        'alerts' => $alerts,
    ]
);
