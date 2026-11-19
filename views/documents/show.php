<?php
$submitted = !in_array($d['status'], ['draft', 'revision'], true);
$localState = $d['local_at'] ? 'done' : ($d['status'] === 'rejected_local' ? 'bad' : '');
$koreaState = $d['korea_result'] === 'approved' ? 'done' : ($d['korea_result'] === 'rejected' ? 'bad' : '');
$id = (int) $d['id'];
?>
<a class="back" href="<?= e(url('documents')) ?>">&larr; Kembali ke daftar</a>

<div class="page-h">
    <div>
        <h1><?= e(Document::displayNumber($d)) ?></h1>
        <p class="sub"><?= e(Document::typeLabel($d)) ?> &middot; <?= e($d['dept_name']) ?><?= $d['rel_code'] ? ' (terkait ' . e($d['rel_code']) . ')' : '' ?> &middot; prioritas <?= e($d['priority']) ?></p>
    </div>
    <div class="acts"><span class="t t-<?= e($d['status']) ?>"><?= e(Document::statusLabel($d['status'])) ?></span></div>
</div>

<?php if ($d['reject_reason'] && in_array($d['status'], ['rejected_local', 'rejected_korea'], true)): ?>
    <div class="flash err" role="alert"><?= icon('alert') ?><div><b>Alasan penolakan:</b> <?= e($d['reject_reason']) ?></div></div>
<?php endif; ?>

<div class="cols wide">
    <div>
        <section class="card">
            <div class="card-h"><h2>Ringkasan</h2></div>
            <div class="card-b">
                <dl class="meta">
                    <?php if ($d['type'] === 'PO'): ?>
                        <div><dt>Vendor</dt><dd><?= e($d['vendor']) ?></dd></div>
                        <div><dt>Alamat vendor</dt><dd><?= !empty($d['vendor_address']) ? nl2br(e($d['vendor_address'])) : '-' ?></dd></div>
                        <div><dt>Tipe bayar</dt><dd><?= e($d['pay_type'] ?: '-') ?></dd></div>
                        <div><dt>Rujukan proposal</dt><dd><?php if ($d['ref_document_id']): ?><a href="<?= e(url('documents/show', ['id' => $d['ref_document_id']])) ?>"><?= e($d['ref_number']) ?></a><?php else: ?>Tanpa proposal<?php endif; ?></dd></div>
                    <?php else: ?>
                        <div><dt>Subjek</dt><dd><?= e($d['subject']) ?></dd></div>
                        <div><dt>PO terkait</dt><dd><?php
                            $links = [];
                            foreach ($derived as $x) { $links[] = '<a href="' . e(url('documents/show', ['id' => $x['id']])) . '">' . e($x['number'] ?: 'Draft') . '</a>'; }
                            echo $links ? implode(', ', $links) : 'belum ada';
                        ?></dd></div>
                    <?php endif; ?>
                    <div><dt>Dibuat oleh</dt><dd><?= e($d['creator_name']) ?></dd></div>
                </dl>
            </div>
        </section>

        <?php if ($d['type'] === 'PRP'): ?>
        <section class="card">
            <div class="card-h"><h2>Barang proposal</h2></div>
            <div class="card-b">
                <?php if ($can['prices']): ?>
                    <div class="wys"><?= $bodyHtml ?></div>
                <?php else: ?>
                    <p class="mut small" style="margin:0">Isi barang proposal hanya terlihat oleh departemen pemilik dokumen.</p>
                <?php endif; ?>
            </div>
        </section>
        <?php else: ?>
        <section class="card flush">
            <div class="card-h"><h2>Barang</h2></div>
            <div class="card-b"><div class="tw"><table>
                <thead><tr><th>Barang</th><th class="r">Qty</th><?php if ($can['prices']): ?><th class="r">Harga</th><th class="r">Jumlah</th><?php endif; ?></tr></thead>
                <tbody>
                <?php foreach ($items as $i): ?>
                    <tr><td><?= e($i['name']) ?></td><td class="r num"><?= e(num($i['qty'])) ?> <?= e($i['unit']) ?></td>
                        <?php if ($can['prices']): ?><td class="r num"><?= e(rp($i['price'])) ?></td><td class="r num"><?= e(rp($i['qty'] * $i['price'])) ?></td><?php endif; ?></tr>
                <?php endforeach; ?>
                <?php if ($can['prices']): ?><tr><th colspan="3" class="r">Total</th><th class="r num"><?= e(rp(Document::total($items))) ?></th></tr><?php endif; ?>
                </tbody>
            </table></div></div>
        </section>
        <?php endif; ?>

        <?php if ($d['type'] === 'PRP' && array_filter($w5)): ?>
            <section class="card">
                <div class="card-h"><h2>5W1H</h2></div>
                <div class="card-b"><dl class="meta">
                    <?php foreach ($w5 as $k => $v): if ($v !== ''): ?>
                        <div><dt><?= e(strtoupper($k)) ?></dt><dd><?= e($v) ?></dd></div>
                    <?php endif; endforeach; ?>
                </dl></div>
            </section>
        <?php endif; ?>

        <?php if ($revisions): ?>
            <section class="card flush">
                <div class="card-h"><h2>Riwayat revisi</h2></div>
                <div class="card-b"><div class="tw"><table>
                    <thead><tr><th>Rev.</th><th>Alasan</th><th>Oleh</th><th>Waktu</th></tr></thead>
                    <tbody><?php foreach ($revisions as $r): ?>
                        <tr><td class="num"><?= (int) $r['revision'] ?></td><td><?= e($r['reason']) ?></td><td><?= e($r['user_name'] ?: '-') ?></td><td class="num"><?= e(fmt_dt($r['created_at'])) ?></td></tr>
                    <?php endforeach; ?></tbody>
                </table></div></div>
            </section>
        <?php endif; ?>
    </div>

    <aside>
        <section class="card">
            <div class="card-h"><h2>Persetujuan</h2></div>
            <div class="card-b">
                <ol class="steps">
                    <li class="<?= $submitted ? 'done' : '' ?>"><span class="dot">1</span><div><b>Diajukan</b><div class="mut small"><?= $submitted ? 'Rev. ' . (int) $d['revision'] : 'Belum diajukan' ?></div></div></li>
                    <li class="<?= $localState ?>"><span class="dot">2</span><div><b>Manager lokal</b>
                        <div class="mut small"><?php
                            if ($d['local_at']) { echo e(($d['local_name'] ?: '') . ', ' . fmt_date($d['local_at'])); }
                            elseif ($d['status'] === 'rejected_local') { echo 'Ditolak'; }
                            elseif ($d['status'] === 'pending_local') { echo 'Menunggu'; }
                            else { echo '-'; }
                        ?></div></div></li>
                    <li class="<?= $koreaState ?>"><span class="dot">3</span><div><b>Korea (dicatat admin)</b>
                        <div class="mut small"><?php
                            if ($d['korea_result']) { echo e(($d['korea_result'] === 'approved' ? 'Disetujui' : 'Ditolak') . ', ' . fmt_date($d['korea_at']) . ' oleh ' . ($d['korea_name'] ?: '-')); }
                            elseif ($d['status'] === 'approved_local') { echo 'Menunggu hasil Korea'; }
                            else { echo '-'; }
                        ?></div></div></li>
                </ol>
            </div>
        </section>

        <section class="card">
            <div class="card-h"><h2>Tindakan</h2></div>
            <div class="card-b stack">
                <?php if ($can['download']): ?>
                    <a class="btn p block" href="<?= e(url('documents/download', ['id' => $id])) ?>"><?= icon('down') ?> Download PDF</a>
                <?php else: ?>
                    <p class="mut small" style="margin:0">PDF aktif setelah disetujui Manager lokal.</p>
                <?php endif; ?>

                <?php if ($can['update']): ?>
                    <form method="POST" action="<?= e(url('documents/submit')) ?>"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $id ?>"><button class="btn p block"><?= $d['status'] === 'revision' ? 'Ajukan ulang' : 'Ajukan' ?></button></form>
                    <a class="btn block" href="<?= e(url('documents/edit', ['id' => $id])) ?>"><?= icon('edit') ?> Ubah</a>
                <?php endif; ?>

                <?php if ($can['approve']): ?>
                    <form method="POST" action="<?= e(url('documents/approve')) ?>"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $id ?>"><button class="btn p block"><?= icon('check') ?> Setuju (Manager lokal)</button></form>
                <?php endif; ?>

                <?php if ($can['delete']): ?>
                    <form method="POST" action="<?= e(url('documents/delete')) ?>" onsubmit="return confirm('Hapus draft ini?')"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $id ?>"><button class="btn d block">Hapus draft</button></form>
                <?php endif; ?>

                <?php if ($can['approve']): ?>
                    <form method="POST" action="<?= e(url('documents/reject')) ?>" class="sub-form">
                        <?= csrf_field() ?><input type="hidden" name="id" value="<?= $id ?>">
                        <label for="rj">Tolak (alasan wajib)</label>
                        <textarea id="rj" name="reason" required></textarea>
                        <button class="btn d block">Tolak dokumen</button>
                    </form>
                <?php endif; ?>

                <?php if ($can['korea']): ?>
                    <form method="POST" action="<?= e(url('documents/korea')) ?>" class="sub-form">
                        <?= csrf_field() ?><input type="hidden" name="id" value="<?= $id ?>">
                        <label>Catat hasil approval Korea</label>
                        <label class="chk"><input type="radio" name="result" value="approved" required> Disetujui</label>
                        <label class="chk"><input type="radio" name="result" value="rejected"> Ditolak (alasan wajib)</label>
                        <textarea name="note" placeholder="Catatan atau alasan" aria-label="Catatan"></textarea>
                        <button class="btn p block">Simpan hasil Korea</button>
                    </form>
                <?php endif; ?>

                <?php if ($can['revise']): ?>
                    <form method="POST" action="<?= e(url('documents/revise')) ?>" class="sub-form">
                        <?= csrf_field() ?><input type="hidden" name="id" value="<?= $id ?>">
                        <label for="rv">Mulai revisi</label>
                        <div class="hint" style="margin:-.2rem 0 .3rem">Nomor tetap, revisi naik, approval diulang.</div>
                        <textarea id="rv" name="reason" placeholder="Alasan revisi (wajib)" required></textarea>
                        <button class="btn block">Mulai revisi</button>
                    </form>
                <?php endif; ?>
            </div>
        </section>

        <section class="card">
            <div class="card-h"><h2>Aktivitas</h2></div>
            <div class="card-b"><?php partial('_timeline', ['logs' => $logs]); ?></div>
        </section>
    </aside>
</div>
