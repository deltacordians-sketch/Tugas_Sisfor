<?php
require_once __DIR__ . '/config.php';
wajib_login();
$judul = 'Dashboard';

$total = (int)$pdo->query('SELECT COUNT(*) FROM mahasiswa')->fetchColumn();
$avg = $pdo->query('SELECT ROUND(AVG(phq9),1) p, ROUND(AVG(gad7),1) g FROM v_terakhir')->fetch();
$dist = [0, 0, 0, 0];
foreach ($pdo->query('SELECT tingkat, COUNT(*) n FROM v_terakhir GROUP BY tingkat') as $r) $dist[(int)$r['tingkat']] = (int)$r['n'];
$kons = array_fill_keys(STATUS, 0);
foreach ($pdo->query('SELECT konseling, COUNT(*) n FROM v_terakhir GROUP BY konseling') as $r) $kons[$r['konseling']] = (int)$r['n'];
$perlu = (int)$pdo->query("SELECT COUNT(*) FROM v_terakhir WHERE tingkat>=1 AND konseling='Belum'")->fetchColumn();
$fak = $pdo->query('SELECT fakultas, COUNT(*) n, SUM(tingkat>=2) hi FROM v_terakhir GROUP BY fakultas')->fetchAll();
usort($fak, fn($a, $b) => ($b['hi'] / $b['n']) <=> ($a['hi'] / $a['n']));
$tren = $pdo->query("SELECT DATE_FORMAT(tanggal,'%Y-%m') bln, COUNT(*) n, ROUND(AVG(phq9),1) p FROM skrining GROUP BY bln ORDER BY bln")->fetchAll();
$urg = $pdo->query("SELECT id, nama, fakultas, phq9, gad7, konseling FROM v_terakhir WHERE tingkat=3 AND konseling IN ('Belum','Terjadwal') ORDER BY phq9 DESC LIMIT 8")->fetchAll();

$c = 0; $grad = [];
foreach ($dist as $i => $v) { $a = $total ? $c / $total * 100 : 0; $c += $v; $grad[] = WARNA[$i] . " {$a}% " . ($total ? $c / $total * 100 : 0) . '%'; }
$tinggi = $total ? round(($dist[2] + $dist[3]) / $total * 100) : 0;

require __DIR__ . '/header.php';
?>
<div class="grid g4">
  <div class="card stat"><b><?= $total ?></b><span>Mahasiswa terskrining</span></div>
  <div class="card stat"><b><?= e($avg['p'] ?? 0) ?></b><span>Rata-rata PHQ-9</span></div>
  <div class="card stat"><b><?= e($avg['g'] ?? 0) ?></b><span>Rata-rata GAD-7</span></div>
  <div class="card stat"><b><?= $tinggi ?>%</b><span>Risiko tinggi/kritis</span></div>
  <div class="card stat"><b style="color:#cf4f4f"><?= $perlu ?></b><span>Risiko ≥ sedang, belum konseling</span></div>
</div>
<div class="grid g2" style="margin-top:14px">
  <div class="card"><h3>Distribusi tingkat risiko</h3>
    <div class="donut"><div class="ring" style="background:conic-gradient(<?= implode(',', $grad) ?>)"></div>
      <div class="lg"><?php foreach ($dist as $i => $v): ?>
        <div><span class="dot" style="background:<?= WARNA[$i] ?>"></span><?= TINGKAT[$i] ?>: <b><?= $v ?></b> (<?= $total ? round($v / $total * 100) : 0 ?>%)</div>
      <?php endforeach; ?></div></div></div>
  <div class="card"><h3>Status konseling</h3>
    <?php foreach ($kons as $k => $v) echo bar($k, $v, max($kons) ?: 1); ?></div>
  <div class="card"><h3>% risiko tinggi/kritis per fakultas</h3>
    <?php foreach ($fak as $f) echo bar($f['fakultas'], round($f['hi'] / $f['n'] * 100), 100, '%'); ?></div>
  <div class="card"><h3>Rata-rata PHQ-9 per bulan (jumlah skrining)</h3>
    <?php foreach ($tren as $t) echo bar($t['bln'] . ' (' . $t['n'] . ')', $t['p'], 27); ?></div>
</div>
<div class="card" style="margin-top:14px"><h3>Perlu tindak lanjut segera (risiko kritis, belum berjalan konselingnya)</h3>
  <?php if (!$urg): ?><p>Tidak ada.</p><?php else: ?>
  <div class="tw"><table><thead><tr><th>Nama</th><th>Fakultas</th><th>PHQ-9</th><th>GAD-7</th><th>Konseling</th><th></th></tr></thead><tbody>
  <?php foreach ($urg as $u): ?>
    <tr><td><?= e($u['nama']) ?></td><td><?= e($u['fakultas']) ?></td><td><?= $u['phq9'] ?></td><td><?= $u['gad7'] ?></td><td><?= e($u['konseling']) ?></td>
    <td><a href="detail.php?id=<?= $u['id'] ?>">Lihat</a></td></tr>
  <?php endforeach; ?></tbody></table></div><?php endif; ?>
</div>
<?php require __DIR__ . '/footer.php'; ?>
