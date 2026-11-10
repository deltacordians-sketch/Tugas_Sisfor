# SIMENTA – Sistem Informasi Kesehatan Mental Mahasiswa

Aplikasi PHP + MySQL (PDO) dengan 120 data mahasiswa fiktif, 240+ riwayat skrining PHQ-9 dan GAD-7.

## Cara pasang di XAMPP
1. Ekstrak folder `simenta` ke `C:\xampp\htdocs\` (macOS/Linux: `htdocs` XAMPP).
2. Buka XAMPP Control Panel, lalu Start **Apache** dan **MySQL**.
3. Buka http://localhost/phpmyadmin, pilih tab **Import**, pilih file `database/simenta.sql`, lalu klik **Import**.
   Database `simenta` akan dibuat otomatis.
4. Buka http://localhost/simenta/ dan masuk dengan **admin / admin123**.

Jika password MySQL Anda bukan kosong, ubah `DB_USER` dan `DB_PASS` di `config.php`. Butuh PHP 7.4 atau lebih baru.

## Fitur
- Login admin, dashboard statistik (distribusi risiko, status konseling, per fakultas, tren bulanan)
- Data mahasiswa: cari, filter, urutkan, paginasi
- Detail mahasiswa: riwayat skrining, rekomendasi, status dan catatan konseling
- Input mahasiswa baru, skrining ulang, hapus data (proteksi CSRF dan prepared statement)

## Tingkat risiko
Diambil dari yang terparah antara kategori PHQ-9 dan GAD-7: Rendah, Sedang, Tinggi, Kritis.

Seluruh data adalah fiktif. Aplikasi ini alat bantu pemantauan, bukan alat diagnosis.
