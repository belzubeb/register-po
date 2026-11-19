# PANDUAN PENGGUNAAN APLIKASI REGISTER PROPOSAL & PO (PER LEVEL USER)

## 1. Pendahuluan
Aplikasi **Register Proposal & PO** (PHP Native - XAMPP PHP 7.4) memiliki hak akses (role) yang dibagi menjadi beberapa level untuk menjaga keamanan dan kerapian data.

---

## 2. Hak Akses & Level User

### A. Level: Administrator (Admin)
Admin memiliki akses penuh terhadap seluruh fitur aplikasi.
* **Manajemen User:** Menambah, mengedit, atau menghapus akun pengguna beserta hak aksesnya.
* **Kelola Data Master & Proposal/PO:** Menginput, mengedit, dan menghapus data proposal serta PO dari seluruh divisi.
* **Laporan & Rekapitulasi:** Melihat ringkasan data keseluruhan dan mengekspor dokumen ke format PDF.
* **Pengaturan Sistem:** Mengatur konfigurasi dasar aplikasi.

### B. Level: Staff / User Biasa
Staff memiliki akses terbatas yang berfokus pada operasional harian.
* **Input Proposal/PO Baru:** Membuat draf atau entri proposal/PO baru milik sendiri/divisi terkait.
* **Edit Data Sendiri:** Mengubah data proposal/PO yang belum divalidasi penuh (sesuai aturan sistem).
* **Cetak PDF:** Mencetak dokumen proposal atau PO yang sudah dibuat ke bentuk PDF.
* **Pencarian & Monitoring:** Melihat status persetujuan atau progres proposal/PO.

---

## 3. Alur Kerja (Workflow) Penggunaan

### Langkah 1: Login ke Sistem
1. Buka browser, akses: `http://localhost/register-po`
2. Masukkan **Username** dan **Password** yang diberikan admin.
3. Sistem akan mengarahkan user ke dashboard sesuai rolenya masing-masing.

### Langkah 2: Proses untuk Staff (Input & Cetak)
1. Pilih menu **Proposal** atau **PO**.
2. Klik **Tambah Data**, lengkapi form:
   - Nomor Dokumen
   - Tanggal Pengajuan
   - Perihal / Keterangan
   - Nominal Anggaran
3. Klik **Simpan**.
4. Untuk mencetak, klik tombol **Cetak PDF** pada baris data terkait.

### Langkah 3: Proses untuk Admin (Validasi & Manajemen)
1. Periksa data masuk dari daftar proposal/PO.
2. Lakukan verifikasi nominal dan nomor surat.
3. Setujui atau edit jika ada kesalahan penulisan.
4. Pantau rekapitulasi data secara berkala.

---

## 4. Informasi Teknis
- **Path Project:** `D:\xampp74\htdocs\register-po`
- **Server:** XAMPP (Apache / PHP 7.4 / MySQL 3306)
- **File Terkait:** `views/login.php`, `views/layout.php`, `views/pdf.php`
