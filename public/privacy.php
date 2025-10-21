<?php
declare(strict_types=1);

$pageTitle = 'Gizlilik Politikası';
$pageMeta = [
    'description' => 'SolveClone gizlilik politikası: kullanıcı verilerinin nasıl işlendiğini ve korunduğunu öğrenin.',
];

require_once __DIR__ . '/../app/bootstrap.php';

require __DIR__ . '/partials/header.php';
?>
  <article class="card border-0 shadow-sm">
    <div class="card-body p-4">
      <h1 class="h3 fw-bold mb-3">Gizlilik Politikası</h1>
      <p class="text-muted">SolveClone kullanıcı bilgilerinin güvenliğini önemser. Toplanan veriler yalnızca hizmetin sunulması ve iyileştirilmesi için kullanılır. Parolalar BCRYPT ile hashlenir, profil verileri kullanıcı kontrolündedir.</p>
      <p>Profil ayarlarınızdan görünürlüğü dilediğiniz zaman değiştirebilirsiniz. Daha fazla bilgi için <a href="/settings.php">profil ayarları</a> sayfasını ziyaret edin.</p>
    </div>
  </article>
<?php require __DIR__ . '/partials/footer.php';
