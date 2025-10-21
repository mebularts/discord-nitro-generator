<section class="settings" aria-labelledby="settings-heading">
    <h1 id="settings-heading">Profil Ayarların</h1>
    <form method="post" action="/settings" class="form card">
        <input type="hidden" name="_token" value="<?= $csrf_token; ?>">
        <h2>Profil Bilgileri</h2>
        <label for="name">Adın</label>
        <input id="name" name="name" type="text" value="<?= App\Support\Helpers::escape($user['name'] ?? ''); ?>">
        <label for="bio">Biyografi</label>
        <textarea id="bio" name="bio" maxlength="2800"><?= App\Support\Helpers::escape($user['bio'] ?? ''); ?></textarea>
        <label for="profile_color">Profil Rengi (#RRGGBB)</label>
        <input id="profile_color" name="profile_color" type="text" pattern="^#[0-9A-Fa-f]{6}$" value="<?= App\Support\Helpers::escape($user['profile_color'] ?? '#1f2937'); ?>">
        <fieldset>
            <legend>Sosyal Bağlantılar</legend>
            <?php $social = json_decode($user['social_links'] ?? '{}', true) ?: []; ?>
            <?php foreach (['instagram','twitter','youtube','tiktok','github','linkedin'] as $network): ?>
                <label for="social_<?= $network; ?>"><?= ucfirst($network); ?></label>
                <input id="social_<?= $network; ?>" name="social_links[<?= $network; ?>]" type="url" value="<?= App\Support\Helpers::escape($social[$network] ?? ''); ?>">
            <?php endforeach; ?>
        </fieldset>
        <label class="switch">
            <input type="checkbox" name="questions_public" value="1" <?= ($user['questions_public'] ?? 1) ? 'checked' : ''; ?>>
            <span>Profil cevaplarımı herkese açık göster</span>
        </label>
        <button type="submit" class="button button-primary">Kaydet</button>
    </form>

    <form method="post" action="/settings/username" class="form card">
        <input type="hidden" name="_token" value="<?= $csrf_token; ?>">
        <h2>Kullanıcı Adı</h2>
        <p>Mevcut adın: <strong>@<?= App\Support\Helpers::escape($user['username']); ?></strong></p>
        <?php if (!($username_change['allowed'] ?? false)): ?>
            <p class="form-helper">Yeni kullanıcı adına geçmek için <?= (int) ($username_change['remaining_days'] ?? 0); ?> gün daha beklemelisin.</p>
        <?php endif; ?>
        <label for="new_username">Yeni kullanıcı adı</label>
        <input id="new_username" name="new_username" type="text" pattern="^[a-z0-9_]{3,32}$" required <?= !($username_change['allowed'] ?? false) ? 'disabled' : ''; ?>>
        <button type="submit" class="button" <?= !($username_change['allowed'] ?? false) ? 'disabled' : ''; ?>>Güncelle</button>
    </form>
</section>
