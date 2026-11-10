<?php
require_once __DIR__ . '/config.php';
wajib_login();

// ?id=... berarti skrining ulang untuk mahasiswa yang sudah ada
$id = (int)($_GET['id'] ?? 0);
$mhs = null;
if ($id) {
    $s = $pdo->prepare('SELECT * FROM mahasiswa WHERE id = ?'); $s->execute([$id]);
    $mhs = $s->fetch();
    if (!$mhs) { header('Location: mahasiswa.php'); exit; }
}
$err = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $phq = (int)($_POST['phq9'] ?? -1); $gad = (int)($_POST['gad7'] ?? -1); $tdr = (float)($_POST['jam_tidur'] ?? 0);
    if ($phq < 0 || $phq > 27) $err[] = 'Skor PHQ-9 harus 0–27.';
    if ($gad < 0 || $gad > 21) $err[] = 'Skor GAD-7 harus 0–21.';
    if ($tdr < 0 || $tdr > 24) $err[] = 'Jam tidur tidak valid.';
    if (!$mhs) {
        $nama = trim($_POST['nama'] ?? ''); $nim = trim($_POST['nim'] ?? '');
        $fak = $_POST['fakultas'] ?? ''; $prodi = $_POST['prodi'] ?? ''; $jk = $_POST['jenis_kelamin'] ?? '';
        $smt = (int)($_POST['semester'] ?? 0); $ipk = (float)($_POST['ipk'] ?? 0);
        if ($nama === '' || $nim === '') $err[] = 'Nama dan NIM wajib diisi.';
        if (!isset(FAKULTAS[$fak]) || !in_array($prodi, FAKULTAS[$fak] ?? [], true)) $err[] = 'Fakultas/prodi tidak valid.';
        if (!in_array($jk, ['Laki-laki', 'Perempuan'], true)) $err[] = 'Jenis kelamin tidak valid.';
        if ($smt < 1 || $smt > 14) $err[] = 'Semester harus 1–14.';
        if ($ipk < 0 || $ipk > 4) $err[] = 'IPK harus 0–4.';
        if (!$err) {
            $cek = $pdo->prepare('SELECT COUNT(*) FROM mahasiswa WHERE nim = ?'); $cek->execute([$nim]);
            if ($cek->fetchColumn()) $err[] = 'NIM sudah terdaftar.';
        }
    }
    if (!$err) {
        $pdo->beginTransaction();
        if (!$mhs) {
            $pdo->prepare('INSERT INTO mahasiswa (nim,nama,jenis_kelamin,fakultas,prodi,semester,ipk) VALUES (?,?,?,?,?,?,?)')->execute([$nim, $nama, $jk, $fak, $prodi, $smt, $ipk]);
            $id = (int)$pdo->lastInsertId();
            $pdo->prepare("INSERT INTO konseling (mahasiswa_id,status) VALUES (?, 'Belum')")->execute([$id]);
        }
        $kel = trim($_POST['keluhan'] ?? '') ?: 'Tidak ada keluhan berarti';
        $tg = hitung_risiko($phq, $gad);
        $pdo->prepare('INSERT INTO skrining (mahasiswa_id,tanggal,phq9,gad7,jam_tidur,keluhan,tingkat) VALUES (?,?,?,?,?,?,?)')->execute([$id, date('Y-m-d'), $phq, $gad, $tdr, $kel, $tg]);
        $pdo->commit();
        flash('Skrining tersimpan. Tingkat risiko: ' . TINGKAT[$tg] . '. ' . REKOMENDASI[$tg]);
        header('Location: detail.php?id=' . $id); exit;
    }
}
$v = fn($k, $d = '') => e($_POST[$k] ?? $d);
$judul = $mhs ? 'Skrining Ulang' : 'Skrining Baru';
require __DIR__ . '/header.php';
?>
<div class="card"><h3><?= $mhs ? 'Skrining ulang: ' . e($mhs['nama']) . ' (' . e($mhs['nim']) . ')' : 'Input mahasiswa & hasil skrining baru' ?></h3>
<?php foreach ($err as $x): ?><div class="alert err"><?= e($x) ?></div><?php endforeach; ?>
<form method="post" class="fg">
  <?= csrf_field() ?>
  <?php if (!$mhs): ?>
  <label>Nama<input name="nama" value="<?= $v('nama') ?>" required></label>
  <label>NIM<input name="nim" value="<?= $v('nim') ?>" required></label>
  <label>Jenis kelamin<select name="jenis_kelamin"><option>Perempuan</option><option <?= ($_POST['jenis_kelamin'] ?? '') === 'Laki-laki' ? 'selected' : '' ?>>Laki-laki</option></select></label>
  <label>Fakultas<select name="fakultas" id="fak"><?php foreach (array_keys(FAKULTAS) as $f): ?><option <?= ($_POST['fakultas'] ?? '') === $f ? 'selected' : '' ?>><?= e($f) ?></option><?php endforeach; ?></select></label>
  <label>Program studi<select name="prodi" id="prodi" data-sel="<?= $v('prodi') ?>"></select></label>
  <label>Semester<input name="semester" type="number" min="1" max="14" value="<?= $v('semester', 1) ?>"></label>
  <label>IPK<input name="ipk" type="number" step="0.01" min="0" max="4" value="<?= $v('ipk', '3.00') ?>"></label>
  <?php endif; ?>
  <label>Skor PHQ-9 (0–27)<input name="phq9" type="number" min="0" max="27" value="<?= $v('phq9', 0) ?>" required></label>
  <label>Skor GAD-7 (0–21)<input name="gad7" type="number" min="0" max="21" value="<?= $v('gad7', 0) ?>" required></label>
  <label>Rata-rata jam tidur<input name="jam_tidur" type="number" step="0.5" min="0" max="24" value="<?= $v('jam_tidur', 7) ?>"></label>
  <label class="full">Keluhan utama<textarea name="keluhan" rows="2"><?= $v('keluhan') ?></textarea></label>
  <div class="full"><button class="p" type="submit">Simpan skrining</button></div>
</form></div>
<script>
const F = <?= json_encode(FAKULTAS, JSON_UNESCAPED_UNICODE) ?>;
const fak = document.getElementById('fak'), prodi = document.getElementById('prodi');
function isi() { if (!fak) return; prodi.innerHTML = F[fak.value].map(p => `<option${p === prodi.dataset.sel ? ' selected' : ''}>${p}</option>`).join(''); }
if (fak) { fak.addEventListener('change', () => { prodi.dataset.sel = ''; isi(); }); isi(); }
</script>
<?php require __DIR__ . '/footer.php'; ?>
