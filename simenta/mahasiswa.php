<?php
require_once __DIR__ . '/config.php';
wajib_login();
$judul = 'Data Mahasiswa';

$q = trim($_GET['q'] ?? ''); $fak = $_GET['fakultas'] ?? ''; $risk = $_GET['risiko'] ?? ''; $sts = $_GET['status'] ?? '';
$cols = ['nim', 'nama', 'fakultas', 'semester', 'phq9', 'gad7', 'tingkat', 'konseling', 'tanggal'];
$s = in_array($_GET['s'] ?? '', $cols, true) ? $_GET['s'] : 'nama';
$d = ($_GET['d'] ?? 'asc') === 'desc' ? 'DESC' : 'ASC';

$where = []; $p = [];
if ($q !== '') { $where[] = '(nama LIKE ? OR nim LIKE ?)'; $p[] = "%$q%"; $p[] = "%$q%"; }
if ($fak !== '') { $where[] = 'fakultas = ?'; $p[] = $fak; }
if ($risk !== '' && ctype_digit($risk)) { $where[] = 'tingkat = ?'; $p[] = (int)$risk; }
if (in_array($sts, STATUS, true)) { $where[] = 'konseling = ?'; $p[] = $sts; }
$w = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$per = 15; $page = max(1, (int)($_GET['page'] ?? 1));
$c = $pdo->prepare("SELECT COUNT(*) FROM v_terakhir $w"); $c->execute($p);
$total = (int)$c->fetchColumn();
$pages = max(1, (int)ceil($total / $per)); $page = min($page, $pages);
$off = ($page - 1) * $per;
$st = $pdo->prepare("SELECT * FROM v_terakhir $w ORDER BY $s $d, id LIMIT $per OFFSET $off"); $st->execute($p);
$rows = $st->fetchAll();

function th($col, $label) {
    global $s, $d;
    $nd = ($s === $col && $d === 'ASC') ? 'desc' : 'asc';
    $arrow = $s === $col ? ($d === 'ASC' ? ' ▲' : ' ▼') : '';
    return '<th><a href="' . e(qs(['s' => $col, 'd' => $nd, 'page' => 1])) . '">' . $label . $arrow . '</a></th>';
}
require __DIR__ . '/header.php';
?>
<form method="get" class="tools">
  <input name="q" value="<?= e($q) ?>" placeholder="Cari nama / NIM…" style="flex:1;min-width:180px">
  <select name="fakultas"><option value="">Semua fakultas</option>
    <?php foreach (array_keys(FAKULTAS) as $f): ?><option <?= $f === $fak ? 'selected' : '' ?>><?= e($f) ?></option><?php endforeach; ?></select>
  <select name="risiko"><option value="">Semua risiko</option>
    <?php foreach (TINGKAT as $i => $t): ?><option value="<?= $i ?>" <?= $risk !== '' && (int)$risk === $i ? 'selected' : '' ?>><?= $t ?></option><?php endforeach; ?></select>
  <select name="status"><option value="">Semua status konseling</option>
    <?php foreach (STATUS as $t): ?><option <?= $t === $sts ? 'selected' : '' ?>><?= $t ?></option><?php endforeach; ?></select>
  <button class="p" type="submit">Filter</button>
  <a class="btn" href="mahasiswa.php">Reset</a>
  <a class="btn p" href="tambah.php">+ Mahasiswa</a>
</form>
<div class="card"><div class="tw"><table>
  <thead><tr><?= th('nim', 'NIM') . th('nama', 'Nama') . th('fakultas', 'Fakultas') . th('semester', 'Smt') . th('phq9', 'PHQ-9') . th('gad7', 'GAD-7') . th('tingkat', 'Risiko') . th('konseling', 'Konseling') . th('tanggal', 'Skrining Terakhir') ?></tr></thead>
  <tbody>
  <?php foreach ($rows as $r): ?>
    <tr onclick="location='detail.php?id=<?= $r['id'] ?>'">
      <td><?= e($r['nim']) ?></td><td><a href="detail.php?id=<?= $r['id'] ?>"><?= e($r['nama']) ?></a></td><td><?= e($r['fakultas']) ?></td>
      <td><?= $r['semester'] ?></td><td><?= $r['phq9'] ?></td><td><?= $r['gad7'] ?></td><td><?= badge((int)$r['tingkat']) ?></td>
      <td><?= e($r['konseling']) ?></td><td><?= e($r['tanggal']) ?></td></tr>
  <?php endforeach; if (!$rows) echo '<tr><td colspan="9">Tidak ada data.</td></tr>'; ?>
  </tbody></table></div>
  <div class="pg"><span><?= $total ?> data · halaman <?= $page ?>/<?= $pages ?></span>
    <?php if ($page > 1): ?><a class="btn" href="<?= e(qs(['page' => $page - 1])) ?>">‹ Sebelumnya</a><?php endif; ?>
    <?php if ($page < $pages): ?><a class="btn" href="<?= e(qs(['page' => $page + 1])) ?>">Berikutnya ›</a><?php endif; ?></div>
</div>
<?php require __DIR__ . '/footer.php'; ?>
