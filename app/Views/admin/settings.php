<section class="admin-settings" aria-labelledby="admin-settings-heading">
    <h1 id="admin-settings-heading">Site Ayarları</h1>
    <form method="post" action="/admin/settings" class="form card">
        <input type="hidden" name="_token" value="<?= $csrf_token; ?>">
        <label for="site_name">Site Adı</label>
        <input id="site_name" name="site_name" type="text" value="<?= App\Support\Helpers::escape($settings['site_name'] ?? 'SolveClone'); ?>">
        <label for="default_language">Varsayılan Dil</label>
        <input id="default_language" name="default_language" type="text" value="<?= App\Support\Helpers::escape($settings['default_language'] ?? 'tr'); ?>">
        <label for="theme">Varsayılan Tema</label>
        <select id="theme" name="theme">
            <option value="system" <?= (($settings['theme'] ?? 'system') === 'system') ? 'selected' : ''; ?>>Sistem</option>
            <option value="light" <?= (($settings['theme'] ?? 'system') === 'light') ? 'selected' : ''; ?>>Açık</option>
            <option value="dark" <?= (($settings['theme'] ?? 'system') === 'dark') ? 'selected' : ''; ?>>Koyu</option>
        </select>
        <button type="submit" class="button button-primary">Kaydet</button>
    </form>
</section>
