<?php
require_once __DIR__ . '/config.php';
wajib_login();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $pdo->prepare('DELETE FROM mahasiswa WHERE id = ?')->execute([(int)($_POST['id'] ?? 0)]);
    flash('Data mahasiswa berhasil dihapus.');
}
header('Location: mahasiswa.php');
