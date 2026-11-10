<?php
require_once __DIR__ . '/config.php';
wajib_login();
$hal = basename($_SERVER['SCRIPT_NAME'], '.php');
$pesan = flash();
$aktif = fn($n) => in_array($hal, (array)$n) ? 'on' : '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($judul ?? 'SIMENTA') ?> – SIMENTA</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header>
  <h1>SIMENTA<small>Sistem Informasi Kesehatan Mental Mahasiswa</small></h1>
  <nav>
    <a class="<?= $aktif('index') ?>" href="index.php">Dashboard</a>
    <a class="<?= $aktif(['mahasiswa', 'detail']) ?>" href="mahasiswa.php">Data Mahasiswa</a>
    <a class="<?= $aktif('tambah') ?>" href="tambah.php">Skrining Baru</a>
    <a href="logout.php">Keluar (<?= e($_SESSION['user']) ?>)</a>
  </nav>
</header>
<main>
<?php if ($pesan): ?><div class="alert"><?= e($pesan) ?></div><?php endif; ?>
