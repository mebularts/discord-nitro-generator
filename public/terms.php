<?php
declare(strict_types=1);

$pageTitle = 'Kullanım Koşulları';
$pageMeta = [
    'description' => 'SolveClone kullanım koşulları ve topluluk kurallarını inceleyin.',
];

require_once __DIR__ . '/../app/bootstrap.php';

require __DIR__ . '/partials/header.php';
?>
  <article class="card border-0 shadow-sm">
    <div class="card-body p-4">
      <h1 class="h3 fw-bold mb-3">Kullanım Koşulları</h1>
      <p class="text-muted">SolveClone'u kullanarak topluluk kurallarına uyacağınızı kabul etmiş olursunuz. Saldırgan, spam veya yasa dışı içerikler kaldırılır. Yönetim paneli gerektiğinde hesapları askıya alma hakkını saklı tutar.</p>
      <ul>
        <li>Sorular saygılı ve topluluk kurallarına uygun olmalıdır.</li>
        <li>Anonim sorular moderasyon sonrası yayından kaldırılabilir.</li>
        <li>Bildirimler güvenliğiniz için kaydedilir, dilediğinizde silebilirsiniz.</li>
      </ul>
    </div>
  </article>
<?php require __DIR__ . '/partials/footer.php';
