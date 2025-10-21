<section class="auth-section" aria-labelledby="register-heading">
    <div class="card">
        <h1 id="register-heading">SolveClone'a Katıl</h1>
        <p class="section-description">Kendi profilini oluştur, sorular al ve cevaplarını paylaş.</p>
        <form method="post" action="/register" class="form" novalidate>
            <input type="hidden" name="_token" value="<?= $csrf_token; ?>">
            <label for="email">E-posta</label>
            <input id="email" name="email" type="email" required value="<?= App\Support\Helpers::escape($old['email'] ?? ''); ?>">
            <label for="username">Kullanıcı adı</label>
            <input id="username" name="username" type="text" pattern="^[a-z0-9_]{3,32}$" required value="<?= App\Support\Helpers::escape($old['username'] ?? ''); ?>">
            <label for="password">Parola</label>
            <input id="password" name="password" type="password" minlength="8" required>
            <button type="submit" class="button button-primary">Kayıt Ol</button>
        </form>
        <p class="section-description">Zaten hesabın var mı? <a href="/login">Giriş yap.</a></p>
    </div>
</section>
