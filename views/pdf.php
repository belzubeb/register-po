<?php
// Template PDF untuk PO dan Proposal. Selalu A4 potrait, memakai kop surat (public/img/kop.png).
// Kolom nama di tanda tangan sengaja dikosongkan: nama dan tanda tangan diisi manual di kertas.

// ---- Turunan ----
$kopFile = ROOT . '/public/img/kop.png';
$kop = is_file($kopFile) ? 'data:image/png;base64,' . base64_encode(file_get_contents($kopFile)) : '';
$isPrint = !empty($print);   // true = cadangan cetak lewat browser (tanpa Dompdf)
$isPO = $d['type'] === 'PO';
$total = Document::total($items);
$numberText = $d['number'] . ((int) $d['revision'] > 0 ? ' (Rev. ' . (int) $d['revision'] . ')' : '');
$docDate = $d['local_at'] ? $d['local_at'] : $d['created_at'];
$blank = max(0, 8 - count($items));
$term = $d['pay_type'] === 'tempo' ? 'Term Payment : 45 Days' : ($d['pay_type'] === 'cash' ? 'Term Payment : Cash' : '');
$priorityLabel = ['tinggi' => 'Tinggi', 'sedang' => 'Sedang', 'rendah' => 'Rendah'];
$prio = isset($priorityLabel[$d['priority']]) ? $priorityLabel[$d['priority']] : $d['priority'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title><?= e($d['number']) ?></title>
<style>
    /* Dompdf: kop surat berulang di setiap halaman lewat elemen fixed yang ditaruh di margin atas. */
    @page { size: A4 portrait; margin: <?= $isPrint ? '26px 34px' : '155px 34px 46px 34px' ?>; }
    body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 10.5px; color: #000; }
    #kop { <?= $isPrint ? 'margin: 0 0 8px;' : 'position: fixed; top: -135px; left: 0; right: 0;' ?> }
    #kop img { <?= $isPrint ? 'width: 100%;' : 'width: 725px;' ?> }
    table { border-collapse: collapse; width: 100%; }
    .nb td { border: 0; padding: 1px 0; vertical-align: top; }
    .grid th, .grid td { border: 1px solid #000; padding: 5px 6px; vertical-align: top; }
    .grid th { background: #f0f0f0; text-align: center; }
    .r { text-align: right; }
    .c { text-align: center; }
    .title { font-size: 20px; font-weight: bold; margin: 0 0 2px; text-align: center; letter-spacing: 1px; }
    .sig th, .sig td { border: 1px solid #000; text-align: center; padding: 4px 3px; font-size: 9.5px; }
    .sig th { background: #f0f0f0; }
    .sig td.sp { height: 58px; }
    .sig td.nm { height: 20px; }
    .small { font-size: 8.5px; color: #333; }
    .gap { height: 10px; }
    .sect { font-weight: bold; background: #f0f0f0; border: 1px solid #000; padding: 5px 6px; margin-bottom: 6px; }
    /* Isi WYSIWYG */
    .wys { font-size: 10.5px; line-height: 1.45; word-wrap: break-word; }
    .wys p { margin: 0 0 6px; }
    .wys img { max-width: 100%; height: auto; }
    .wys table { margin: 4px 0 8px; }
    .wys table[border] td, .wys table[border] th { border: 1px solid #000; }
    .wys table[border="0"] td, .wys table[border="0"] th { border: 0; }
    .wys td, .wys th { padding: 4px 6px; vertical-align: top; }
    .wys th { background: #f0f0f0; }
    .wys ul, .wys ol { margin: 0 0 6px; padding-left: 20px; }
    .wys blockquote { margin: 0 0 6px 12px; padding-left: 8px; border-left: 2px solid #999; }
    .bar { background: #eef; padding: 8px 12px; margin-bottom: 12px; font-family: Arial, sans-serif; }
    @media print { .bar { display: none; } }
</style>
</head>
<body>
<?php if ($isPrint): ?>
    <div class="bar">Halaman siap cetak. Pilih <b>Simpan sebagai PDF</b> di dialog cetak (ukuran A4, orientasi Portrait). <button onclick="window.print()">Cetak</button></div>
    <script>window.onload = function () { window.print(); };</script>
<?php endif; ?>

<?php if ($kop): ?><div id="kop"><img src="<?= $kop ?>" alt="PT WOOIN"></div><?php endif; ?>

<?php if ($isPO): ?>
<!-- ======================= PURCHASE ORDER ======================= -->
<p class="title">PURCHASE ORDER</p>
<div class="gap"></div>

<table class="nb">
    <tr>
        <td width="56%">
            <b>To:</b> <b><?= e($d['vendor']) ?></b>
            <?php if (!empty($d['vendor_address'])): ?><br><?= nl2br(e($d['vendor_address'])) ?><?php endif; ?>
            <?php if (!empty($d['vendor_phone'])): ?><br>Telp. <?= e($d['vendor_phone']) ?><?php endif; ?>
        </td>
        <td width="44%" class="r">
            <b>PO NO. : <?= e($numberText) ?></b><br>
            <?= e(fmt_date($docDate)) ?>
            <?php if (!empty($d['ref_number'])): ?><br><span class="small">Ref. proposal: <?= e($d['ref_number']) ?></span><?php endif; ?>
        </td>
    </tr>
</table>
<div class="gap"></div>

<table class="grid">
    <tr>
        <th width="6%">No</th><th>Uraian</th><th width="11%">Qty</th><th width="15%">Part Number</th>
        <th width="16%">Unit Price</th><th width="16%">Total</th>
    </tr>
    <?php foreach ($items as $k => $i): ?>
        <tr>
            <td class="c"><?= $k + 1 ?></td>
            <td><?= e($i['name']) ?></td>
            <td class="c"><?= e(num($i['qty'])) ?> <?= e($i['unit']) ?></td>
            <td class="c"><?= e(!empty($i['part_number']) ? $i['part_number'] : '-') ?></td>
            <td class="r"><?= e(rp($i['price'])) ?></td>
            <td class="r"><?= e(rp($i['qty'] * $i['price'])) ?></td>
        </tr>
    <?php endforeach; ?>
    <?php for ($b = 0; $b < $blank; $b++): ?>
        <tr><td>&nbsp;</td><td></td><td></td><td></td><td></td><td></td></tr>
    <?php endfor; ?>
</table>

<table class="nb" style="margin-top:8px">
    <tr>
        <td width="58%">
            <b>Note :</b><br>
            <?php if ($term): ?><?= e($term) ?><?php endif; ?>
        </td>
        <td width="42%">
            <table class="grid">
                <tr><th style="text-align:left">TOTAL</th><th class="r"><?= e(rp($total)) ?></th></tr>
            </table>
        </td>
    </tr>
</table>

<div class="gap"></div><div class="gap"></div>
<table class="sig">
    <tr>
        <th width="20%">Charged</th><th width="20%">Checked</th><th width="20%">Approved<br><span class="small">Manager lokal</span></th>
        <th width="20%">Approved<br><span class="small">Korea</span></th><th width="20%">Pres. Dir</th>
    </tr>
    <tr><td class="sp"></td><td class="sp"></td><td class="sp"></td><td class="sp"></td><td class="sp"></td></tr>
    <tr><td class="nm"></td><td class="nm"></td><td class="nm"></td><td class="nm"></td><td class="nm"></td></tr>
</table>

<?php else: ?>
<!-- ======================= PROPOSAL ======================= -->
<p class="title">PROPOSAL</p>
<div class="gap"></div>

<table class="nb">
    <tr>
        <td width="48%">
            <table class="nb">
                <tr><td width="34%">Dokumen No.</td><td>: <b><?= e($numberText) ?></b></td></tr>
                <tr><td>Writing Date</td><td>: <?= e(fmt_date($d['created_at'])) ?></td></tr>
                <tr><td>To.</td><td>: Management</td></tr>
                <tr><td>Cc.</td><td>: -</td></tr>
                <tr><td>From</td><td>: <?= e($d['dept_name']) ?></td></tr>
                <tr><td>Planning Date</td><td>: <?= e(!empty($w5['when']) ? $w5['when'] : '-') ?></td></tr>
                <tr><td>Related With</td><td>: <?= e($d['rel_name'] ? $d['rel_name'] : '-') ?></td></tr>
            </table>
        </td>
        <td width="4%"></td>
        <td width="48%">
            <table class="sig">
                <tr><th>Prepared</th><th>Checked</th><th>Manager</th><th>G.Manager</th><th>P. Direktur</th></tr>
                <tr><td class="sp"></td><td class="sp"></td><td class="sp"></td><td class="sp"></td><td class="sp"></td></tr>
                <tr><td class="nm"></td><td class="nm"></td><td class="nm"></td><td class="nm"></td><td class="nm"></td></tr>
            </table>
            <div class="gap"></div>
            <table class="grid">
                <tr><th>PRIORITY SCALE</th><th>DECISION TERM</th></tr>
                <tr>
                    <td class="c"><b><?= e($prio) ?></b></td>
                    <td>
                        [<?= $d['korea_result'] === 'approved' ? 'X' : '&nbsp;&nbsp;' ?>] OK &nbsp;
                        [<?= $d['korea_result'] === 'rejected' ? 'X' : '&nbsp;&nbsp;' ?>] NO &nbsp;
                        [&nbsp;&nbsp;] POSTPONE
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
<div class="gap"></div>

<table class="grid">
    <tr><th width="16%" style="text-align:left">SUBJECT</th><td><b><?= e($d['subject']) ?></b></td></tr>
    <?php foreach (['what' => 'What', 'why' => 'Why', 'who' => 'Who', 'when' => 'When', 'where' => 'Where', 'how' => 'How'] as $k => $lab):
        if (!empty($w5[$k])): ?>
        <tr><th style="text-align:left"><?= $lab ?> :</th><td><?= nl2br(e($w5[$k])) ?></td></tr>
    <?php endif; endforeach; ?>
</table>
<div class="gap"></div>

<!-- Barang proposal: isi WYSIWYG (sudah disanitasi). Bukan dibungkus tabel supaya bisa menyambung ke halaman berikutnya. -->
<div class="sect">BARANG</div>
<div class="wys"><?= $bodyHtml ?></div>

<p style="margin-top:14px">Demikian Proposal ini kami sampaikan, atas perhatian dan kerjasamanya kami ucapkan terima kasih.</p>
<?php endif; ?>

<p class="small" style="margin-top:12px">Dokumen ini dicetak dari sistem setelah disetujui Manager lokal. Tanda tangan dan nama diisi langsung pada lembar cetak.</p>
</body>
</html>
