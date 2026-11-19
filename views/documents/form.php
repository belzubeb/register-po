<?php
$o = $GLOBALS['__old'];
$type = isset($o['type']) ? $o['type'] : ($d ? $d['type'] : 'PO');
if (isset($o['items']) && is_array($o['items'])) {
    $rows = array_values($o['items']);
} elseif ($d) {
    $rows = array_map(function ($i) { return ['name' => $i['name'], 'qty' => num($i['qty']), 'unit' => $i['unit'], 'price' => num($i['price'])]; }, $items);
} else {
    $rows = [['name' => '', 'qty' => 1, 'unit' => 'pcs', 'price' => 0]];
}
$val = function ($k, $default = '') use ($o, $d) {
    if (isset($o[$k])) { return $o[$k]; }
    return ($d && isset($d[$k]) && $d[$k] !== null) ? $d[$k] : $default;
};
$w5d = $d ? Document::w5($d) : [];
$action = $d ? url('documents/update') : url('documents/store');
$backUrl = $d ? url('documents/show', ['id' => $d['id']]) : url('documents');
?>
<a class="back" href="<?= e($backUrl) ?>">&larr; Kembali</a>
<div class="page-h">
    <div>
        <h1><?= $d ? 'Ubah ' . e(Document::displayNumber($d)) : 'Dokumen baru' ?></h1>
        <p class="sub">Nomor terbit saat dokumen diajukan, bukan saat draft.</p>
    </div>
</div>

<form method="POST" action="<?= e($action) ?>">
    <?= csrf_field() ?>
    <?php if ($d): ?><input type="hidden" name="id" value="<?= (int) $d['id'] ?>"><?php endif; ?>

    <div class="cols wide">
        <div>
            <section class="card">
                <div class="card-h"><h2>Informasi dokumen</h2></div>
                <div class="card-b">
                    <div class="g">
                        <div>
                            <label for="type">Jenis</label>
                            <?php if ($d): ?>
                                <input value="<?= e(Document::typeLabel($d)) ?>" disabled>
                            <?php else: ?>
                                <select id="type" name="type" onchange="toggleType()">
                                    <option value="PO"<?= sel($type === 'PO') ?>>PO</option>
                                    <option value="PRP"<?= sel($type === 'PRP') ?>>Proposal</option>
                                </select>
                            <?php endif; ?>
                        </div>
                        <div>
                            <label for="related_department_id">Dept terkait</label>
                            <select id="related_department_id" name="related_department_id">
                                <option value="">- (dept sendiri)</option>
                                <?php foreach ($departments as $dp): ?>
                                    <option value="<?= (int) $dp['id'] ?>"<?= sel((string) $val('related_department_id') === (string) $dp['id']) ?>><?= e($dp['code']) ?> - <?= e($dp['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label for="priority">Prioritas</label>
                            <select id="priority" name="priority">
                                <?php foreach (['tinggi' => 'Tinggi', 'sedang' => 'Sedang', 'rendah' => 'Rendah'] as $k => $lab): ?>
                                    <option value="<?= $k ?>"<?= sel($val('priority', 'sedang') === $k) ?>><?= $lab ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="g po-only">
                        <div>
                            <label for="vendor">Vendor</label>
                            <input id="vendor" name="vendor" value="<?= e($val('vendor')) ?>">
                            <label for="vendor_address" style="margin-top:.6rem">Alamat vendor</label>
                            <textarea id="vendor_address" name="vendor_address" rows="3" maxlength="500" placeholder="Alamat lengkap vendor (tampil di bawah nama vendor pada PO)"><?= e($val('vendor_address')) ?></textarea>
                        </div>
                        <div>
                            <label for="pay_type">Tipe bayar (keterangan)</label>
                            <select id="pay_type" name="pay_type">
                                <option value="">-</option>
                                <option value="cash"<?= sel($val('pay_type') === 'cash') ?>>Cash</option>
                                <option value="tempo"<?= sel($val('pay_type') === 'tempo') ?>>Tempo</option>
                            </select>
                        </div>
                        <div>
                            <label for="ref_document_id">Rujukan proposal (opsional)</label>
                            <select id="ref_document_id" name="ref_document_id">
                                <option value="">Tanpa proposal (pembelian langsung)</option>
                                <?php foreach ($proposals as $p): ?>
                                    <option value="<?= (int) $p['id'] ?>"<?= sel((string) $val('ref_document_id') === (string) $p['id']) ?>><?= e($p['number']) ?> - <?= e($p['subject']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="g prp-only">
                        <div><label for="subject">Subjek</label><input id="subject" name="subject" value="<?= e($val('subject')) ?>"></div>
                    </div>
                </div>
            </section>

            <section class="card po-only">
                <div class="card-h"><h2>Barang</h2></div>
                <div class="card-b">
                    <div class="it head" aria-hidden="true"><span>Nama barang</span><span>Qty</span><span>Satuan</span><span>Harga satuan</span><span></span></div>
                    <div id="items">
                        <?php foreach ($rows as $i => $it): ?>
                            <div class="it">
                                <input name="items[<?= $i ?>][name]" value="<?= e(isset($it['name']) ? $it['name'] : '') ?>" placeholder="Nama barang" aria-label="Nama barang">
                                <input name="items[<?= $i ?>][qty]" type="number" step="any" min="0" value="<?= e(isset($it['qty']) ? $it['qty'] : 1) ?>" aria-label="Qty">
                                <input name="items[<?= $i ?>][unit]" value="<?= e(isset($it['unit']) ? $it['unit'] : 'pcs') ?>" aria-label="Satuan">
                                <input name="items[<?= $i ?>][price]" type="number" step="any" min="0" value="<?= e(isset($it['price']) ? $it['price'] : 0) ?>" aria-label="Harga satuan">
                                <button type="button" class="btn" onclick="this.parentNode.remove();recalc()" aria-label="Hapus baris">&times;</button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" class="btn sm" onclick="addItem()"><?= icon('plus') ?> Tambah barang</button>
                </div>
            </section>

            <section class="card prp-only">
                <div class="card-h"><h2>Barang proposal</h2></div>
                <div class="card-b">
                    <p class="hint" style="margin:0 0 .6rem">Tulis bebas sepanjang yang dibutuhkan: teks, tabel, daftar, dan gambar (tempel, seret, atau tombol gambar). Gambar otomatis dikecilkan.</p>
                    <textarea id="body" name="body" rows="14"><?= e($val('body', $bodyHtml)) ?></textarea>
                </div>
            </section>

            <section class="card prp-only">
                <div class="card-h"><h2>5W1H</h2></div>
                <div class="card-b">
                    <div class="g">
                        <?php foreach (['what' => 'What: apa yang diajukan', 'why' => 'Why: alasan atau kebutuhan', 'who' => 'Who: peminta dan pemakai', 'when' => 'When: kapan dibutuhkan', 'where' => 'Where: dipakai atau dikirim ke mana', 'how' => 'How: cara pengadaan'] as $k => $lab):
                            $v = isset($o['w5'][$k]) ? $o['w5'][$k] : (isset($w5d[$k]) ? $w5d[$k] : ''); ?>
                            <div><label for="w5_<?= $k ?>"><?= e($lab) ?></label><textarea id="w5_<?= $k ?>" name="w5[<?= $k ?>]"><?= e($v) ?></textarea></div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
        </div>

        <aside>
            <section class="card sticky">
                <div class="card-h"><h2>Ringkasan</h2></div>
                <div class="card-b stack">
                    <div class="po-only"><span class="mut small">Total perkiraan</span><div class="total" id="total">Rp 0</div></div>
                    <p class="hint" style="margin:0">Simpan sebagai draft dulu. Setelah itu klik Ajukan di halaman detail supaya nomor terbit.</p>
                    <div class="form-actions">
                        <button type="submit" class="btn p">Simpan draft</button>
                        <a class="btn" href="<?= e($backUrl) ?>">Batal</a>
                    </div>
                </div>
            </section>
        </aside>
    </div>
</form>

<script>
    var n = <?= count($rows) ?>;
    function idr(v) { return 'Rp ' + Math.round(v).toLocaleString('id-ID'); }
    function recalc() {
        var s = 0;
        document.querySelectorAll('#items .it').forEach(function (r) {
            var q = parseFloat(r.querySelector('[name$="[qty]"]').value) || 0;
            var p = parseFloat(r.querySelector('[name$="[price]"]').value) || 0;
            s += q * p;
        });
        var t = document.getElementById('total');
        if (t) { t.textContent = idr(s); }
    }
    function addItem() {
        var row = document.createElement('div');
        row.className = 'it';
        row.innerHTML = '<input name="items[' + n + '][name]" placeholder="Nama barang" aria-label="Nama barang">' +
            '<input name="items[' + n + '][qty]" type="number" step="any" min="0" value="1" aria-label="Qty">' +
            '<input name="items[' + n + '][unit]" value="pcs" aria-label="Satuan">' +
            '<input name="items[' + n + '][price]" type="number" step="any" min="0" value="0" aria-label="Harga satuan">' +
            '<button type="button" class="btn" onclick="this.parentNode.remove();recalc()" aria-label="Hapus baris">&times;</button>';
        document.getElementById('items').appendChild(row);
        n++;
        recalc();
    }
    function toggleType() {
        var el = document.getElementById('type');
        var t = el ? el.value : <?= json_encode($type) ?>;
        document.querySelectorAll('.po-only').forEach(function (e) { e.style.display = t === 'PO' ? '' : 'none'; });
        document.querySelectorAll('.prp-only').forEach(function (e) { e.style.display = t === 'PRP' ? '' : 'none'; });
        if (t === 'PRP') { ensureEditor(); }
    }

    /* ---- Editor WYSIWYG (TinyMCE lokal, tanpa internet) ---- */
    var editorStarted = false;
    // Kecilkan gambar besar supaya dokumen tidak berat; hasilnya data URI (ikut tersimpan di dokumen dan PDF).
    function imageToDataUrl(blob) {
        return new Promise(function (resolve, reject) {
            var fr = new FileReader();
            fr.onerror = function () { reject('Gambar tidak bisa dibaca.'); };
            fr.onload = function () {
                if (blob.size < 300 * 1024 || blob.type === 'image/gif') { resolve(fr.result); return; }
                var img = new Image();
                img.onerror = function () { resolve(fr.result); };
                img.onload = function () {
                    var max = 1400, w = img.width, h = img.height;
                    if (w > max) { h = Math.round(h * max / w); w = max; }
                    var c = document.createElement('canvas');
                    c.width = w; c.height = h;
                    var ctx = c.getContext('2d');
                    ctx.fillStyle = '#fff'; ctx.fillRect(0, 0, w, h);
                    ctx.drawImage(img, 0, 0, w, h);
                    resolve(c.toDataURL('image/jpeg', 0.85));
                };
                img.src = fr.result;
            };
            fr.readAsDataURL(blob);
        });
    }
    function ensureEditor() {
        if (editorStarted || typeof tinymce === 'undefined') { return; }
        editorStarted = true;
        tinymce.init({
            selector: '#body',
            base_url: <?= json_encode(asset('vendor/tinymce')) ?>,
            suffix: '.min',
            language: 'id',
            license_key: 'gpl',
            height: 520,
            menubar: 'edit insert format table',
            plugins: 'advlist lists table image link code fullscreen',
            toolbar: 'undo redo | blocks | bold italic underline strikethrough | forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | table | image link | removeformat | fullscreen code',
            branding: false,
            promotion: false,
            statusbar: true,
            resize: true,
            browser_spellcheck: true,
            convert_urls: false,
            relative_urls: false,
            paste_data_images: true,
            automatic_uploads: true,
            images_file_types: 'jpg,jpeg,png,gif,webp',
            images_upload_handler: function (blobInfo) { return imageToDataUrl(blobInfo.blob()); },
            file_picker_types: 'image',
            file_picker_callback: function (cb) {
                var inp = document.createElement('input');
                inp.type = 'file'; inp.accept = 'image/png,image/jpeg,image/gif,image/webp';
                inp.onchange = function () {
                    if (!inp.files[0]) { return; }
                    imageToDataUrl(inp.files[0]).then(function (u) { cb(u, { alt: inp.files[0].name }); });
                };
                inp.click();
            },
            table_default_attributes: { border: '1' },
            table_default_styles: { 'border-collapse': 'collapse', width: '100%' },
            content_style: 'body{font-family:Arial,sans-serif;font-size:14px} img{max-width:100%;height:auto} td,th{padding:4px 6px}'
        });
    }
    document.getElementById('items').addEventListener('input', recalc);
    toggleType();
    recalc();
</script>
<script src="<?= e(asset('vendor/tinymce/tinymce.min.js')) ?>"></script>
<script>toggleType();</script>
