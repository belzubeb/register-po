<?php
// Teks panduan per role. **tebal** diubah jadi huruf tebal; sisanya di-escape.
$g = function ($s) { return preg_replace(['/\*\*(.+?)\*\*/', '/`(.+?)`/'], ['<b>$1</b>', '<code>$1</code>'], e($s)); };
$chip = function ($s) { return '<span class="t t-' . e($s) . '">' . e(Document::statusLabel($s)) . '</span>'; };

$guide = [
    'admin' => [
        'label' => 'Admin dept',
        'title' => 'Panduan Admin Departemen',
        'summary' => 'Lu yang bikin dokumen, mengajukan ke Manager, mencatat hasil Korea, dan mengunduh PDF untuk ditandatangani.',
        'can' => [
            'Membuat PO dan Proposal untuk departemen lu',
            'Mengubah dokumen selama berstatus Draft atau Revisi',
            'Mengajukan dokumen (nomor terbit saat diajukan)',
            'Menghapus draft yang belum bernomor',
            'Mencatat hasil approval Korea',
            'Memulai revisi dokumen (Rev. naik, nomor tetap)',
            'Mengunduh PDF setelah disetujui Manager lokal',
        ],
        'cannot' => [
            'Menyetujui atau menolak sebagai Manager',
            'Melihat atau mengubah dokumen departemen lain',
            'Mengunduh PDF sebelum Manager menyetujui',
            'Mengelola pengguna dan melihat log aktivitas',
        ],
        'steps' => [
            ['Buat dokumen baru', 'Klik **Dokumen baru** di menu kiri, pilih jenis **PO** atau **Proposal**, lalu isi prioritas dan dept terkait (kalau ada).', ''],
            ['Isi PO', 'Isi **Vendor** dan **Alamat vendor** (alamat tampil di bawah nama vendor pada PO), tipe bayar, lalu tabel barang: nama, qty, satuan, harga. Total terhitung otomatis. Rujukan proposal boleh dikosongkan untuk pembelian langsung.', 'Alamat vendor maksimal 500 karakter dan boleh dikosongkan.'],
            ['Isi Proposal', 'Isi **Subjek**, lalu bagian **Barang proposal** di editor WYSIWYG: ketik bebas, buat tabel, daftar, dan tambah gambar (tempel, seret, atau tombol gambar). Lengkapi juga isian 5W1H.', 'Total biaya nggak dihitung otomatis di proposal. Tulis sendiri di tabel editor.'],
            ['Simpan draft', 'Klik **Simpan draft**. Dokumen masih bebas diubah dan belum punya nomor.', ''],
            ['Ajukan', 'Buka detail dokumen, klik **Ajukan**. Nomor terbit dan status jadi **Menunggu Manager lokal**. Setelah ini dokumen terkunci dari pengeditan.', 'Salah isi setelah diajukan? Pakai **Mulai revisi**, jangan bikin dokumen baru.'],
            ['Download PDF dan tanda tangan', 'Setelah Manager setuju, tombol **Download PDF** aktif. PDF berkop surat, A4 potrait. Kolom nama dan tanda tangan sengaja kosong: cetak lalu isi tangan.', ''],
            ['Catat hasil Korea', 'Saat statusnya **Disetujui Lokal**, tunggu keputusan Korea (di luar sistem), lalu catat di panel **Catat hasil approval Korea**: Disetujui, atau Ditolak dengan alasan wajib.', ''],
            ['Revisi kalau perlu', 'Dokumen ditolak atau ada yang salah? Isi alasan di **Mulai revisi**. Nomor tetap, Rev. naik, ubah dokumen, lalu **Ajukan ulang**. Approval diulang dari awal dan PDF mati sampai disetujui lagi.', ''],
        ],
        'faq' => [
            ['Kenapa tombol Download PDF nggak aktif?', 'PDF hanya aktif setelah Manager lokal menyetujui. Status harus Disetujui Lokal atau Disetujui.'],
            ['Nomor dokumen kok belum ada?', 'Nomor terbit saat dokumen diajukan, bukan saat draft.'],
            ['Gambar di proposal nggak muncul di PDF?', 'Gambar harus ditempel, diseret, atau diupload lewat editor. Gambar yang hanya ditaut lewat URL internet dibuang.'],
            ['Simpan proposal gagal setelah banyak gambar?', 'Isi proposal maksimal 8 MB. Kecilkan atau kurangi gambar. Kalau tetap gagal, minta Developer cek `post_max_size` dan `max_allowed_packet`.'],
        ],
    ],
    'manager' => [
        'label' => 'Manager lokal',
        'title' => 'Panduan Manager Lokal',
        'summary' => 'Lu memeriksa dokumen yang diajukan Admin departemen lu, lalu menyetujui atau menolaknya.',
        'can' => [
            'Melihat dokumen departemen lu dan yang terkait ke dept lu',
            'Menyetujui dokumen berstatus Menunggu Manager lokal',
            'Menolak dokumen dengan alasan yang jelas',
            'Mengunduh PDF setelah dokumen disetujui',
        ],
        'cannot' => [
            'Membuat atau mengubah dokumen',
            'Mencatat hasil Korea atau memulai revisi (tugas Admin)',
            'Melihat dokumen departemen lain yang nggak terkait',
        ],
        'steps' => [
            ['Buka Dashboard', 'Daftar **Menunggu persetujuan** tampil di dashboard. Klik nomor dokumen untuk membukanya.', ''],
            ['Periksa isi dokumen', 'Cek vendor dan alamat (PO), barang, harga, total, prioritas. Untuk proposal, baca subjek, 5W1H, dan isi barang proposal.', ''],
            ['Setujui', 'Klik **Setuju (Manager lokal)**. Status jadi **Disetujui Lokal** dan Admin bisa mengunduh PDF. Persetujuan ini tercatat di log aktivitas, bukan dicetak di PDF.', 'PDF nggak memuat tulisan "disetujui digital". Tanda tangan Manager dilakukan manual di kertas.'],
            ['Atau tolak', 'Isi **alasan penolakan** (wajib) lalu klik **Tolak dokumen**. Admin akan melihat alasannya dan memulai revisi.', ''],
        ],
        'faq' => [
            ['Dokumen yang gue setujui bisa dibatalkan?', 'Nggak langsung. Admin bisa memulai revisi, dan persetujuan Manager akan diulang dari awal.'],
            ['Kenapa isi barang proposal nggak tampil?', 'Isi proposal hanya terlihat oleh departemen pemilik dokumen karena bisa memuat harga.'],
        ],
    ],
    'dev' => [
        'label' => 'Developer',
        'title' => 'Panduan Developer',
        'summary' => 'Lu mengawasi seluruh sistem: semua departemen, log aktivitas, dan akun pengguna. Lu nggak ikut alur persetujuan.',
        'can' => [
            'Melihat semua dokumen lintas departemen, lengkap dengan harga',
            'Membuka halaman Aktivitas (log seluruh tindakan)',
            'Membuat akun pengguna (Admin, Manager, Developer)',
            'Mengunduh PDF dokumen yang sudah disetujui',
        ],
        'cannot' => [
            'Membuat, mengajukan, menyetujui, atau merevisi dokumen',
            'Mengubah password pengguna lewat menu (belum tersedia)',
        ],
        'steps' => [
            ['Pantau Dashboard', 'Dashboard developer menampilkan jumlah dokumen per status dan aktivitas terbaru dari semua departemen.', ''],
            ['Audit lewat Aktivitas', 'Menu **Aktivitas** mencatat siapa melakukan apa dan kapan: diajukan, disetujui, ditolak, PDF diunduh, revisi.', ''],
            ['Tambah pengguna', 'Menu **Pengguna**: isi nama, email, password (minimal 8 karakter), peran, dan departemen. Admin dan Manager wajib punya departemen.', 'Hapus atau ganti password akun contoh (`password`) sebelum dipakai sungguhan.'],
            ['Pemeliharaan', 'Matikan `debug` di `app/config.php` saat produksi, pakai HTTPS (wajib untuk install PWA), dan backup database berkala.', 'Setelah update versi, jalankan file migrasi SQL yang disertakan sekali saja.'],
        ],
        'faq' => [
            ['Install aplikasi ke HP (PWA) nggak muncul?', 'Butuh HTTPS atau localhost, manifest dan service worker harus terbaca. Buka lewat Chrome lalu pilih Install atau Tambahkan ke layar utama.'],
            ['PDF jadi halaman cetak, bukan file?', 'Dompdf belum terpasang. Jalankan `composer install` di folder proyek. Tanpa itu, aplikasi memakai cadangan cetak browser.'],
        ],
    ],
];

if (!isset($guide[$role])) { $role = 'admin'; }
$G = $guide[$role];
?>
<div class="page-h">
    <div>
        <h1>Panduan</h1>
        <p class="sub">Pilih peran untuk melihat langkah kerjanya.</p>
    </div>
    <div class="acts noprint"><button type="button" class="btn sm" onclick="window.print()"><?= icon('down') ?> Cetak / simpan PDF</button></div>
</div>

<nav class="tabs" aria-label="Pilih peran">
    <?php foreach ($guide as $k => $x): ?>
        <a href="<?= e(url('panduan', ['role' => $k])) ?>"<?= $k === $role ? ' class="on" aria-current="page"' : '' ?>><?= e($x['label']) ?><?php if ($k === $mine): ?><span class="me">(peran lu)</span><?php endif; ?></a>
    <?php endforeach; ?>
</nav>

<div class="hero">
    <h2><?= e($G['title']) ?></h2>
    <p><?= e($G['summary']) ?></p>
</div>

<div class="two">
    <section class="card yes">
        <div class="card-h"><h2>Yang bisa lu lakukan</h2></div>
        <div class="card-b"><ul><?php foreach ($G['can'] as $t): ?><li><?= $g($t) ?></li><?php endforeach; ?></ul></div>
    </section>
    <section class="card no">
        <div class="card-h"><h2>Yang nggak bisa</h2></div>
        <div class="card-b"><ul><?php foreach ($G['cannot'] as $t): ?><li><?= $g($t) ?></li><?php endforeach; ?></ul></div>
    </section>
</div>

<section class="card">
    <div class="card-h"><h2>Langkah kerja</h2></div>
    <div class="card-b">
        <ol class="gsteps">
            <?php foreach ($G['steps'] as $s): ?>
                <li>
                    <div>
                        <b class="t1"><?= e($s[0]) ?></b>
                        <div><?= $g($s[1]) ?></div>
                        <?php if (!empty($s[2])): ?><div class="tip"><?= $g($s[2]) ?></div><?php endif; ?>
                    </div>
                </li>
            <?php endforeach; ?>
        </ol>
    </div>
</section>

<section class="card">
    <div class="card-h"><h2>Alur dokumen</h2></div>
    <div class="card-b stack">
        <div class="flow">
            <?= $chip('draft') ?><span class="arr">&rarr; Ajukan &rarr;</span>
            <?= $chip('pending_local') ?><span class="arr">&rarr; Setuju &rarr;</span>
            <?= $chip('approved_local') ?><span class="arr">&rarr; Catat Korea &rarr;</span>
            <?= $chip('approved') ?>
        </div>
        <div class="flow">
            <?= $chip('rejected_local') ?><span class="arr">atau</span><?= $chip('rejected_korea') ?>
            <span class="arr">&rarr; Mulai revisi &rarr;</span><?= $chip('revision') ?>
            <span class="arr">&rarr; Ajukan ulang &rarr;</span><?= $chip('pending_local') ?>
        </div>
        <div class="callout">Revisi: <b>nomor tetap</b>, Rev. naik satu, approval diulang dari awal, dan PDF nonaktif sampai disetujui lagi.</div>
        <div class="tw"><table>
            <thead><tr><th>Status</th><th>Artinya</th><th>PDF</th></tr></thead>
            <tbody>
                <tr><td><?= $chip('draft') ?></td><td>Masih disusun, belum bernomor</td><td>Belum</td></tr>
                <tr><td><?= $chip('pending_local') ?></td><td>Menunggu keputusan Manager</td><td>Belum</td></tr>
                <tr><td><?= $chip('rejected_local') ?></td><td>Ditolak Manager, perlu revisi</td><td>Belum</td></tr>
                <tr><td><?= $chip('approved_local') ?></td><td>Manager setuju, menunggu hasil Korea</td><td>Bisa</td></tr>
                <tr><td><?= $chip('rejected_korea') ?></td><td>Korea menolak, perlu revisi</td><td>Belum</td></tr>
                <tr><td><?= $chip('approved') ?></td><td>Final, disetujui Korea</td><td>Bisa</td></tr>
                <tr><td><?= $chip('revision') ?></td><td>Sedang direvisi oleh Admin</td><td>Belum</td></tr>
            </tbody>
        </table></div>
    </div>
</section>

<section class="card">
    <div class="card-h"><h2>Tanya jawab</h2></div>
    <div class="card-b faq">
        <?php foreach ($G['faq'] as $f): ?>
            <details><summary><?= e($f[0]) ?></summary><p><?= $g($f[1]) ?></p></details>
        <?php endforeach; ?>
    </div>
</section>
