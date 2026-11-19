# BUKU PANDUAN APLIKASI REGISTER PROPOSAL & PO (BERDASARKAN ROLE)

Buku panduan ini merinci hak akses, tanggung jawab, dan langkah operasional (workflow) untuk masing-masing role di dalam aplikasi.

---

## 1. ROLE: DEVELOPER / SUPER ADMIN (`dev`)
Developer memiliki akses penuh tanpa batasan departemen untuk keperluan pemeliharaan sistem, audit, dan pengawasan global.

### A. Tanggung Jawab Utama
* Mengawasi seluruh aktivitas dan log sistem lintas departemen.
* Membantu pemecahan masalah (troubleshooting) error atau kendala teknis.
* Mengelola akses level tinggi jika diperlukan.

### B. Langkah Operasional (Workflow)
1. **Login:** Masuk menggunakan akun ber-role `dev`.
2. **Dashboard Global:** Melihat rekapitulasi data dari seluruh departemen secara bersamaan.
3. **Audit / Monitoring:** Memeriksa log dokumen yang masuk, status persetujuan, atau kendala teknis pada sistem database.

---

## 2. ROLE: ADMIN DEPARTEMEN (`admin`)
Admin departemen adalah motor penggerak utama dalam pencatatan administrasi harian per departemen.

### A. Tanggung Jawab Utama
* Membuat dan mencatat (Create) draf Proposal atau PO baru untuk departemennya.
* Mengisi kelengkapan data (Nomor, Tanggal, Perihal, Nominal, dan Keterangan).
* Mengajukan dokumen ke Manager.
* Mencatat hasil approval lanjutan (seperti approval Korea).
* Mencetak dokumen resmi ke format PDF.

### B. Langkah Operasional (Workflow)
1. **Login:** Masuk dengan akun `admin` sesuai departemen masing-masing (misal: HRU).
2. **Membuat Dokumen (Draft):**
   - Masuk ke menu **Proposal/PO**.
   - Klik **Tambah Data / Buat Draf**.
   - Isi form yang tersedia, lalu simpan. Status awal dokumen adalah **Draft**.
3. **Mengajukan ke Manager:**
   - Setelah data lengkap, ajukan dokumen agar berstatus **Pending Local** untuk diperiksa Manager.
4. **Pencetakan:**
   - Klik tombol **Cetak PDF** untuk menghasilkan dokumen cetak resmi.
5. **Pencatatan Hasil Lanjutan:**
   - Melakukan update status jika ada hasil keputusan lanjutan (misal: *Approved Korea* / *Rejected Korea*).

---

## 3. ROLE: MANAGER LOKAL / DEPARTEMEN (`manager`)
Manager bertanggung jawab memberikan validasi dan persetujuan awal (approval lokal) terhadap proposal atau PO yang diajukan oleh admin departemennya.

### A. Tanggung Jawab Utama
* Memeriksa proposal atau PO yang masuk dalam status *Pending Local*.
* Memberikan keputusan **Setuju (Approved Local)** atau **Tolak (Rejected Local)**.

### B. Langkah Operasional (Workflow)
1. **Login:** Masuk menggunakan akun ber-role `manager` di departemen terkait.
2. **Review Dokumen:**
   - Buka halaman Dashboard atau daftar dokumen yang membutuhkan tindakan (*To-Do List*).
   - Periksa rincian proposal/PO (nominal, perihal, dan kelengkapan).
3. **Eksekusi Keputusan:**
   - Klik **Setujui** jika dokumen valid (status berubah menjadi *Approved Local*).
   - Klik **Tolak/Revisi** jika ada catatan atau perbaikan yang diperlukan oleh admin.
