<?php
// ====== Konfigurasi database (default XAMPP) ======
define('DB_HOST', 'localhost');
define('DB_NAME', 'simenta');
define('DB_USER', 'root');
define('DB_PASS', '');

if (session_status() === PHP_SESSION_NONE) session_start();

try {
    $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $ex) {
    die('<h3>Koneksi database gagal.</h3><p>Pastikan MySQL di XAMPP sudah Start dan database <b>simenta</b> sudah diimport dari <code>database/simenta.sql</code>.</p>');
}

const TINGKAT = ['Rendah', 'Sedang', 'Tinggi', 'Kritis'];
const STATUS = ['Belum', 'Terjadwal', 'Berjalan', 'Selesai'];
const WARNA = ['#5aa88f', '#e0b44c', '#e48a4a', '#cf4f4f'];
const REKOMENDASI = [
    'Pertahankan pola hidup sehat. Skrining ulang dalam 3 bulan.',
    'Tawarkan psikoedukasi dan kelompok dukungan sebaya. Pantau dalam 1 bulan.',
    'Jadwalkan konseling dengan psikolog kampus dalam 2 minggu.',
    'PRIORITAS: hubungi mahasiswa segera, lakukan asesmen risiko, dan rujuk ke profesional bila perlu.',
];
const FAKULTAS = [
    'Teknik' => ['Teknik Informatika', 'Teknik Sipil', 'Teknik Industri'],
    'Ekonomi dan Bisnis' => ['Manajemen', 'Akuntansi'],
    'Kedokteran' => ['Pendidikan Dokter', 'Keperawatan'],
    'Psikologi' => ['Psikologi'],
    'Hukum' => ['Ilmu Hukum'],
    'FISIP' => ['Ilmu Komunikasi', 'Hubungan Internasional'],
    'Sains' => ['Biologi', 'Matematika'],
];

function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

// Tingkat risiko: ambil yang terparah dari kategori PHQ-9 dan GAD-7
function hitung_risiko(int $phq, int $gad): int {
    $a = $phq < 5 ? 0 : ($phq < 10 ? 1 : ($phq < 15 ? 2 : ($phq < 20 ? 3 : 4)));
    $b = $gad < 5 ? 0 : ($gad < 10 ? 1 : ($gad < 15 ? 2 : 3));
    $m = max($a, $b);
    return $m <= 1 ? 0 : $m - 1;
}
function badge(int $t): string { return '<span class="bd r' . $t . '">' . TINGKAT[$t] . '</span>'; }
function bar($label, $v, $max, $suf = ''): string {
    $w = $max > 0 ? round($v / $max * 100) : 0;
    return '<div class="bar"><span class="l">' . e($label) . '</span><span class="t"><i style="width:' . $w . '%"></i></span><span class="v">' . e($v) . $suf . '</span></div>';
}
function csrf(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
    return $_SESSION['csrf'];
}
function csrf_field(): string { return '<input type="hidden" name="csrf" value="' . csrf() . '">'; }
function csrf_check(): void {
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) { http_response_code(400); die('Token keamanan tidak valid. Kembali dan coba lagi.'); }
}
function wajib_login(): void { if (empty($_SESSION['user'])) { header('Location: login.php'); exit; } }
function flash($m = null) {
    if ($m !== null) { $_SESSION['flash'] = $m; return null; }
    $f = $_SESSION['flash'] ?? null; unset($_SESSION['flash']); return $f;
}
function qs(array $o): string { return '?' . http_build_query(array_merge($_GET, $o)); }
