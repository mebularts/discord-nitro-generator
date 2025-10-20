<?php
declare(strict_types=1);
session_start();
require_once __DIR__.'/../app/db.php';
require_once __DIR__.'/../app/helpers.php';
if($_SERVER['REQUEST_METHOD']==='POST'){
  csrf_check();
  $email=trim($_POST['email']??''); $pass=$_POST['password']??'';
  $st=db()->prepare("SELECT * FROM users WHERE email=? AND is_active=1"); $st->execute([$email]);
  $u=$st->fetch();
  if($u && password_verify($pass, $u['password_hash'])){
    $_SESSION['admin']=$u['id'];
    header('Location: /admin/dashboard.php'); exit;
  } else { $err='Geçersiz bilgiler'; }
}
?>
<!doctype html><html lang="tr"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<title>Admin Giriş</title>
</head><body class="bg-light">
<div class="container py-5">
  <div class="row justify-content-center"><div class="col-md-4">
    <div class="card shadow-sm">
      <div class="card-body">
        <h5 class="mb-3">Yönetim Girişi</h5>
        <?php if(!empty($err)) echo '<div class="alert alert-danger">'.$err.'</div>'; ?>
        <form method="post">
          <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
          <div class="mb-3"><label class="form-label">E-posta</label><input class="form-control" name="email" required></div>
          <div class="mb-3"><label class="form-label">Parola</label><input type="password" class="form-control" name="password" required></div>
          <button class="btn btn-primary w-100">Giriş Yap</button>
        </form>
        <hr>
        <a href="/admin/setup.php" class="small">İlk kurulum</a>
      </div>
    </div>
  </div></div>
</div>
</body></html>
