<?php
declare(strict_types=1);
?>
<?php if (!empty($flash)): ?>
    <div class="alert alert-success"><?= htmlspecialchars($flash, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>
<div class="card bg-dark border-0 shadow-lg">
    <div class="card-header bg-transparent border-0">
        <h5 class="mb-0">General settings</h5>
    </div>
    <div class="card-body">
        <form method="post" class="row g-3">
            <?= csrf_input() ?>
            <div class="col-md-6">
                <label class="form-label">App name</label>
                <input type="text" class="form-control" name="app_name" value="<?= htmlspecialchars($settings['app_name'] ?? 'SolveClone', ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">Theme color</label>
                <input type="color" class="form-control form-control-color" name="theme_color" value="<?= htmlspecialchars($settings['theme_color'] ?? '#0ea5e9', ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">Meta title</label>
                <input type="text" class="form-control" name="meta_title" value="<?= htmlspecialchars($settings['meta_title'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">Default locale</label>
                <select name="default_locale" class="form-select">
                    <?php foreach (available_locales() as $locale): ?>
                        <option value="<?= $locale ?>" <?= (($settings['default_locale'] ?? APP_LOCALE) === $locale) ? 'selected' : '' ?>><?= strtoupper($locale) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12">
                <label class="form-label">Meta description</label>
                <textarea name="meta_description" class="form-control" rows="3"><?= htmlspecialchars($settings['meta_description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>
            <div class="col-12">
                <label class="form-label">DeepL API key</label>
                <input type="text" class="form-control" name="deepl_auth_key" value="<?= htmlspecialchars($settings['deepl_auth_key'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-12">
                <label class="form-label">Custom head HTML</label>
                <textarea name="custom_head_html" class="form-control" rows="4"><?= htmlspecialchars($settings['custom_head_html'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>
            <div class="col-12">
                <label class="form-label">Custom body HTML</label>
                <textarea name="custom_body_html" class="form-control" rows="4"><?= htmlspecialchars($settings['custom_body_html'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>
            <div class="col-12 text-end">
                <button type="submit" class="btn btn-primary">Save settings</button>
            </div>
        </form>
    </div>
</div>
