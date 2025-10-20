
<?php
declare(strict_types=1);
require_once __DIR__.'/../app/db.php';
require_once __DIR__.'/../app/helpers.php';

$schema = file_get_contents(__DIR__.'/../install/install.sql');
$pdo = db();
$okMsg='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  csrf_check();
  $pdo->exec($schema);
  $email=trim($_POST['email']); $pass=$_POST['password']; $name='Admin';
  $hash=password_hash($pass, PASSWORD_BCRYPT);
  $st=$pdo->prepare("INSERT INTO users(email,password_hash,name,role) VALUES(?,?,?, 'admin')");
  $st->execute([$email,$hash,$name]);
  $okMsg='Kurulum tamamlandı. Admin girişi yapılabilir.';
}
?>
<!doctype html><html lang="tr"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<title>Kurulum</title></head><body class="bg-light">
<div class="container py-5">
  <div class="row justify-content-center"><div class="col-md-6">
    <div class="card shadow-sm"><div class="card-body">
      <h5 class="mb-3">İlk Kurulum</h5>
      <?php if($okMsg) echo '<div class="alert alert-success">'.$okMsg.'</div>'; ?>
      <p>Veritabanı tabloları oluşturulacak ve admin kullanıcı eklenecek.</p>
      <form method="post">
        <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
        <div class="mb-3"><label class="form-label">Admin E-posta</label><input class="form-control" name="email" value="admin@example.com" required></div>
        <div class="mb-3"><label class="form-label">Admin Parola</label><input type="password" class="form-control" name="password" value="admin123" required></div>
        <button class="btn btn-primary">Kurulumu Başlat</button>
      </form>
      <hr>
      <a href="/admin/login.php">Giriş Sayfasına Dön</a>
    </div></div>
  </div></div>
</div>
</body></html>
