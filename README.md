# Register Proposal dan PO - PHP murni

PHP murni (tanpa framework), jalan di **PHP 7.4 sampai 8.x**, MySQL atau MariaDB. Kode ini belum pernah dijalankan oleh pembuatnya, jadi uji di lingkungan uji dulu.

## Pasang di XAMPP

1. Salin folder `register-po-php` ke `C:\xampp\htdocs\register-po`.
2. Buka phpMyAdmin, buat database kosong `register_po` (collation utf8mb4_unicode_ci).
3. Di tab SQL database itu, jalankan isi `database/schema.sql`, lalu `database/seed.sql`.
4. Salin `app/config.sample.php` menjadi `app/config.php`, lalu isi user dan password database. Port MySQL XAMPP biasanya 3306.
5. (Untuk PDF sungguhan) di folder proyek jalankan `composer install`. Kalau Composer tidak ada, aplikasi tetap jalan; tombol Download akan membuka halaman siap cetak (pilih "Simpan sebagai PDF").
6. Buka http://localhost/register-po/public/ lalu masuk. Akun contoh (password: `password`):
   - dev@example.test (Developer)
   - admin.hru@example.test (Admin HRU)
   - manager.hru@example.test (Manager lokal HRU)
   GANTI password lewat menu Pengguna atau hapus akun contoh sebelum dipakai sungguhan.

Idealnya arahkan Apache ke folder `public` saja. Kalau tidak bisa, folder `app`, `views`, dan `database` sudah dilindungi file `.htaccess` (butuh `AllowOverride All` di Apache).

## Keamanan yang sudah ada
Query pakai prepared statement, password di-hash (bcrypt), token CSRF di semua form POST, output di-escape, session ID diganti saat login, cookie session HttpOnly, semua izin dicek di server (bukan cuma tombol disembunyikan).

## Yang belum ada
Pembatasan percobaan login, reset password, impor PO lama, notifikasi, tes otomatis. Untuk dipakai sungguhan: pakai HTTPS, set `debug` ke false di config, dan backup database berkala.

## Melanjutkan nomor dari data lama
Jalankan di phpMyAdmin (ganti angka):
    INSERT INTO number_counters (department_id, type, year, last_seq)
    VALUES ((SELECT id FROM departments WHERE code='HRU'), 'PO', 2026, 37)
    ON DUPLICATE KEY UPDATE last_seq = 37;
Nomor berikutnya jadi 038.

## Alur
Draft -> Ajukan (nomor terbit) -> Menunggu Manager lokal -> Setuju (PDF bisa diunduh) -> Admin catat hasil Korea -> Disetujui.
Ditolak atau minta revisi -> Revisi (Rev. +1) -> ajukan ulang. Nomor tetap, approval diulang, PDF mati lagi sampai disetujui ulang.

## Pembaruan v2 (PWA, kop surat, WYSIWYG, alamat vendor)

**Wajib dijalankan sekali** di phpMyAdmin pada database yang sudah ada: isi `database/migration_v2.sql`
(menambah kolom `vendor_address` dan `body`). Instalasi baru cukup `schema.sql` karena sudah memuatnya.

1. **PWA**: `public/manifest.webmanifest`, `public/sw.js`, `public/offline.html`. Ikon dari `public/img/starion.png`
   (turunannya: `icon-192.png`, `icon-512.png`, `icon-maskable-512.png`, `apple-touch-icon.png`). Kalau mau ganti logo,
   timpa `starion.png` lalu buat ulang ukuran ikon di atas. Install ke layar utama hanya muncul lewat **HTTPS** (atau `localhost`).
   Service worker hanya menyimpan file statis; halaman dan data tidak di-cache.
2. **PDF PO dan Proposal**: pakai kop surat `public/img/kop.png` (diambil dari `Kop_Surat_Wooin.docx`), selalu A4 potrait.
   Dengan Dompdf, kop muncul di setiap halaman. Dengan cadangan cetak browser, kop hanya di halaman pertama.
3. **Proposal**: bagian barang sekarang editor WYSIWYG (TinyMCE lokal di `public/vendor/tinymce`, tanpa internet).
   Bisa teks, tabel, daftar, dan gambar (tempel, seret, atau tombol gambar). Gambar besar otomatis dikecilkan.
   HTML dibersihkan di server (`app/Sanitizer.php`). Proposal lama tetap tampil dari tabel barang lamanya dan
   otomatis diisikan ke editor saat diubah.
4. **Tanda tangan**: semua kolom nama dikosongkan, tulisan "Disetujui digital" dihapus.
5. **Alamat vendor**: kolom baru di form PO, tampil di bawah nama vendor pada PO dan halaman detail.

Pengaturan server kalau proposal memuat banyak gambar:
- `php.ini`: `post_max_size` dan `upload_max_filesize` naikkan ke 32M.
- `my.ini` MariaDB (XAMPP): `max_allowed_packet=64M`, lalu restart MySQL. Bawaan XAMPP kecil dan bisa menolak simpan.
- Batas isi proposal di aplikasi: 8 MB.
