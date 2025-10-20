
<?php require __DIR__.'/_auth.php'; require_once __DIR__.'/../app/db.php'; require_once __DIR__.'/../app/helpers.php';
$pdo=db();
if($_SERVER['REQUEST_METHOD']==='POST'){ csrf_check();
  $slug=trim($_POST['slug']); $title=$_POST['title']; $body=$_POST['body'];
  $st=$pdo->prepare("INSERT INTO pages(slug,title,body,updated_at) VALUES(?,?,?,NOW()) ON DUPLICATE KEY UPDATE title=VALUES(title), body=VALUES(body), updated_at=NOW()");
  $st->execute([$slug,$title,$body]);
}
$list=$pdo->query("SELECT * FROM pages ORDER BY slug")->fetchAll();
?>
<!doctype html><html lang="tr"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<title>Sayfalar</title></head><body>
<div class="container py-4">
  <h1 class="h4 mb-3">Sayfalar</h1>
  <div class="card mb-4"><div class="card-body">
    <form method="post" class="row g-3">
      <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
      <div class="col-md-3"><label class="form-label">Slug</label><input name="slug" class="form-control" placeholder="terms, privacy..." required></div>
      <div class="col-md-9"><label class="form-label">Başlık</label><input name="title" class="form-control" required></div>
      <div class="col-12"><label class="form-label">İçerik (HTML)</label><textarea name="body" rows="6" class="form-control"></textarea></div>
      <div class="col-12"><button class="btn btn-primary">Kaydet</button></div>
    </form>
  </div></div>
  <table class="table"><thead><tr><th>Slug</th><th>Başlık</th><th>Güncel</th></tr></thead><tbody>
    <?php foreach($list as $p): ?><tr><td><?= h($p['slug']) ?></td><td><?= h($p['title']) ?></td><td><?= h($p['updated_at']) ?></td></tr><?php endforeach; ?>
  </tbody></table>
</div>
</body></html>
