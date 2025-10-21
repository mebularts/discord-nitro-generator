<section class="auth-section" aria-labelledby="login-heading">
    <div class="card">
        <h1 id="login-heading">Giriş Yap</h1>
        <p class="section-description">Topluluğa katılmak için bilgilerini gir.</p>
        <form method="post" action="/login" class="form">
            <input type="hidden" name="_token" value="<?= $csrf_token; ?>">
            <label for="identifier">E-posta veya kullanıcı adı</label>
            <input id="identifier" name="identifier" type="text" required autocomplete="username">
            <label for="password">Parola</label>
            <input id="password" name="password" type="password" required autocomplete="current-password">
            <button type="submit" class="button button-primary">Giriş Yap</button>
        </form>
        <p class="section-description">Hesabın yok mu? <a href="/register">Hemen kayıt ol.</a></p>
    </div>
</section>
