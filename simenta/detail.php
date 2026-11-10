<?php
require_once __DIR__ . '/config.php';
wajib_login();
$id = (int)($_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $status = $_POST['status'] ?? '';
    if (in_array($status, STATUS, true)) {
        $pdo->prepare('INSERT INTO konseling (mahasiswa_id, status, catatan) VALUES (?,?,?) ON DUPLICATE KEY UPDATE status=VALUES(status), catatan=VALUES(catatan)')
            ->execute([$id, $status, trim($_POST['catatan'] ?? '')]);
        flash('Data konseling berhasil diperbarui.');
    }
    header('Location: detail.php?id=' . $id); exit;
}
$st = $pdo->prepare('SELECT * FROM v_terakhir WHERE id = ?'); $st->execute([$id]);
$m = $st->fetch();
if (!$m) { flash('Data mahasiswa tidak ditemukan.'); header('Location: mahasiswa.php'); exit; }
$rw = $pdo->prepare('SELECT * FROM skrining WHERE mahasiswa_id = ? ORDER BY tanggal DESC, id DESC'); $rw->execute([$id]);
$riwayat = $rw->fetchAll();
$judul = $m['nama'];
require __DIR__ . '/header.php';
?>
<div class="grid g2">
  <div class="card">
    <h2 style="margin:0"><?= e($m['nama']) ?> <?= badge((int)$m['tingkat']) ?></h2>
    <p class="mu"><?= e($m['nim']) ?> · <?= e($m['jenis_kelamin']) ?> · <?= e($m['prodi']) ?>, <?= e($m['fakultas']) ?> · Semester <?= $m['semester'] ?></p>
    <?= bar('PHQ-9 (depresi)', $m['phq9'], 27, '/27') ?>
    <?= bar('GAD-7 (kecemasan)', $m['gad7'], 21, '/21') ?>
    <p>Tidur rata-rata <b><?= e($m['jam_tidur']) ?> jam</b> · IPK <b><?= e($m['ipk']) ?></b> · Skrining terakhir <b><?= e($m['tanggal']) ?></b><br>Keluhan: <?= e($m['keluhan']) ?></p>
    <div class="rec"><b>Rekomendasi:</b> <?= e(REKOMENDASI[(int)$m['tingkat']]) ?></div>
    <a class="btn" href="tambah.php?id=<?= $id ?>">+ Skrining ulang</a>
  </div>
  <div class="card"><h3>Tindak lanjut konseling</h3>
    <form method="post" class="one">
      <?= csrf_field() ?>
      <label>Status konseling<select name="status"><?php foreach (STATUS as $s): ?><option <?= $s === $m['konseling'] ? 'selected' : '' ?>><?= $s ?></option><?php endforeach; ?></select></label>
      <label>Catatan konselor<textarea name="catatan" rows="4"><?= e($m['catatan']) ?></textarea></label>
      <button class="p" type="submit">Simpan</button>
    </form>
    <form method="post" action="hapus.php" onsubmit="return confirm('Hapus mahasiswa ini beserta seluruh riwayat skriningnya?')" style="margin-top:12px">
      <?= csrf_field() ?><input type="hidden" name="id" value="<?= $id ?>"><button class="danger" type="submit">Hapus mahasiswa</button>
    </form>
  </div>
</div>
<div class="card" style="margin-top:14px"><h3>Riwayat skrining</h3><div class="tw"><table>
  <thead><tr><th>Tanggal</th><th>PHQ-9</th><th>GAD-7</th><th>Jam tidur</th><th>Risiko</th><th>Keluhan</th></tr></thead><tbody>
  <?php foreach ($riwayat as $r): ?>
    <tr><td><?= e($r['tanggal']) ?></td><td><?= $r['phq9'] ?></td><td><?= $r['gad7'] ?></td><td><?= e($r['jam_tidur']) ?></td><td><?= badge((int)$r['tingkat']) ?></td><td><?= e($r['keluhan']) ?></td></tr>
  <?php endforeach; ?></tbody></table></div></div>
<?php require __DIR__ . '/footer.php'; ?>
