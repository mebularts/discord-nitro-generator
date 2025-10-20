<?php require __DIR__.'/_auth.php'; require_once __DIR__.'/../app/db.php'; require_once __DIR__.'/../app/helpers.php';
$pdo=db(); $msg='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  csrf_check();
  if(($_POST['action']??'')==='bulk'){
    $count=max(1,(int)$_POST['count']); $role=$_POST['role']?:'editor';
    $prefix=trim($_POST['prefix']?:'test');
    for($i=1;$i<=$count;$i++){
      $email=$prefix.$i.'@example.com';
      $pass=password_hash('pass'.$i, PASSWORD_BCRYPT);
      $pdo->prepare("INSERT IGNORE INTO users(email,password_hash,name,role) VALUES(?,?,?,?)")
          ->execute([$email,$pass,'Test '.$i,$role]);
    }
    $msg="$count adet test kullanıcı eklendi.";
  }
}
$list=$pdo->query("SELECT id,email,name,role,is_active,created_at FROM users ORDER BY id DESC LIMIT 200")->fetchAll();
?>
<!doctype html><html lang="tr"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<title>Kullanıcılar</title></head><body>
<div class="container py-4">
  <h1 class="h4 mb-3">Kullanıcılar</h1>
  <?php if($msg) echo '<div class="alert alert-success">'.$msg.'</div>'; ?>
  <div class="card mb-4"><div class="card-body">
    <form method="post" class="row g-3">
      <input type="hidden" name="csrf" value="<?= csrf_token() ?>"><input type="hidden" name="action" value="bulk">
      <div class="col-md-3"><label class="form-label">Adet</label><input type="number" name="count" class="form-control" value="10"></div>
      <div class="col-md-3"><label class="form-label">Rol</label>
        <select name="role" class="form-select"><option>editor</option><option>moderator</option><option>admin</option></select>
      </div>
      <div class="col-md-3"><label class="form-label">E-posta Öneki</label><input name="prefix" class="form-control" value="test"></div>
      <div class="col-md-3 d-flex align-items-end"><button class="btn btn-primary w-100">Toplu Oluştur</button></div>
    </form>
  </div></div>

  <table class="table table-striped">
    <thead><tr><th>ID</th><th>Email</th><th>Ad</th><th>Rol</th><th>Aktif</th><th>Oluşturuldu</th></tr></thead>
    <tbody>
      <?php foreach($list as $u): ?>
        <tr>
          <td><?= (int)$u['id'] ?></td>
          <td><?= h($u['email']) ?></td>
          <td><?= h($u['name']) ?></td>
          <td><?= h($u['role']) ?></td>
          <td><?= $u['is_active']?'Evet':'Hayır' ?></td>
          <td><?= h($u['created_at']) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
</body></html>
