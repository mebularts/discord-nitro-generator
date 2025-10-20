
<?php require __DIR__.'/_auth.php'; require_once __DIR__.'/../app/db.php'; require_once __DIR__.'/../app/helpers.php';
$pdo=db();
if($_SERVER['REQUEST_METHOD']==='POST'){
  csrf_check();
  if(($_POST['action']??'')==='create'){
    $slug=strtolower(preg_replace('/[^a-z0-9-]+/','-', $_POST['slug']?:$_POST['title']));
    $st=$pdo->prepare("INSERT INTO riddles(slug,title,body,answer,difficulty,length,status,published_at) VALUES(?,?,?,?,?,?,?,NOW())");
    $st->execute([$slug, $_POST['title'], $_POST['body'], $_POST['answer'], $_POST['difficulty'], $_POST['length'], 'published']);
  }
}
$list=$pdo->query("SELECT id,slug,title,difficulty,length,status FROM riddles ORDER BY id DESC LIMIT 200")->fetchAll();
?>
<!doctype html><html lang="tr"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<title>Sorular</title></head><body>
<div class="container py-4">
  <h1 class="h4 mb-3">Sorular</h1>
  <div class="card mb-4"><div class="card-body">
    <form method="post">
      <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
      <input type="hidden" name="action" value="create">
      <div class="row g-3">
        <div class="col-md-6"><label class="form-label">Başlık</label><input name="title" class="form-control" required></div>
        <div class="col-md-6"><label class="form-label">Slug</label><input name="slug" class="form-control" placeholder="boş bırakırsanız başlıktan"></div>
        <div class="col-12"><label class="form-label">Soru</label><textarea name="body" class="form-control" rows="3" required></textarea></div>
        <div class="col-12"><label class="form-label">Cevap</label><textarea name="answer" class="form-control" rows="2" required></textarea></div>
        <div class="col-md-3"><label class="form-label">Zorluk</label>
          <select name="difficulty" class="form-select">
            <option>easy</option><option>medium</option><option>hard</option><option>difficult</option>
          </select>
        </div>
        <div class="col-md-3"><label class="form-label">Uzunluk</label>
          <select name="length" class="form-select">
            <option>short</option><option>long</option><option>simple</option>
          </select>
        </div>
        <div class="col-md-3 d-flex align-items-end"><button class="btn btn-primary w-100">Ekle</button></div>
      </div>
    </form>
  </div></div>

  <table class="table table-striped">
    <thead><tr><th>ID</th><th>Başlık</th><th>Zorluk</th><th>Uzunluk</th><th>URL</th></tr></thead>
    <tbody>
      <?php foreach($list as $r): ?>
        <tr>
          <td><?= (int)$r['id'] ?></td>
          <td><?= h($r['title']) ?></td>
          <td><?= h($r['difficulty']) ?></td>
          <td><?= h($r['length']) ?></td>
          <td><a target="_blank" href="/riddle/<?= h($r['slug']) ?>">/riddle/<?= h($r['slug']) ?></a></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
</body></html>
