<?php
require_once __DIR__ . '/config.php';
if (!empty($_SESSION['user'])) { header('Location: index.php'); exit; }
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $s = $pdo->prepare('SELECT * FROM users WHERE username = ?');
    $s->execute([trim($_POST['username'] ?? '')]);
    $u = $s->fetch();
    if ($u && password_verify($_POST['password'] ?? '', $u['password'])) {
        session_regenerate_id(true);
        $_SESSION['user'] = $u['nama'];
        header('Location: index.php'); exit;
    }
    $err = 'Username atau password salah.';
}
?>
<!DOCTYPE html>
<html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Masuk – SIMENTA</title><link rel="stylesheet" href="assets/style.css"></head>
<body class="loginbg">
<form method="post" class="card login">
  <h1>SIMENTA<small>Sistem Informasi Kesehatan Mental Mahasiswa</small></h1>
  <?php if ($err): ?><div class="alert err"><?= e($err) ?></div><?php endif; ?>
  <?= csrf_field() ?>
  <label>Username<input name="username" required autofocus></label>
  <label>Password<input name="password" type="password" required></label>
  <button class="p" type="submit">Masuk</button>
  <p class="note" style="margin:0">Demo: <b>admin</b> / <b>admin123</b></p>
</form>
</body></html>
