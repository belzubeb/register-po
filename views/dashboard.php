<?php
$u = Auth::user();
$c = function ($k) use ($counts) { return isset($counts[$k]) ? $counts[$k] : 0; };
$names = ['admin' => 'Dashboard Admin', 'manager' => 'Dashboard Manager lokal', 'dev' => 'Dashboard Developer'];
$rejected = $c('rejected_local') + $c('rejected_korea');
?>
<div class="page-h">
    <div>
        <h1><?= e($names[$mode]) ?></h1>
        <p class="sub"><?= $mode === 'dev' ? 'Semua departemen' : e($u['dept_name']) ?></p>
    </div>
    <div class="acts">
        <?php if (Policy::create($u)): ?><a class="btn p" href="<?= e(url('documents/create')) ?>"><?= icon('plus') ?> Dokumen baru</a><?php endif; ?>
    </div>
</div>

<?php if ($mode === 'admin'): ?>
    <div class="kpis">
        <div class="kpi"><span class="ico"><?= icon('edit') ?></span><div><b><?= $c('draft') + $c('revision') ?></b><span>Draft dan revisi</span></div></div>
        <div class="kpi info"><span class="ico"><?= icon('clock') ?></span><div><b><?= $c('pending_local') ?></b><span>Menunggu Manager lokal</span></div></div>
        <div class="kpi ok"><span class="ico"><?= icon('down') ?></span><div><b><?= $c('approved_local') ?></b><span>Siap cetak, menunggu Korea</span></div></div>
        <div class="kpi <?= $rejected ? 'bad' : '' ?>"><span class="ico"><?= icon('alert') ?></span><div><b><?= $rejected ?></b><span>Ditolak, perlu revisi</span></div></div>
    </div>
    <div class="cols">
        <section class="card flush">
            <div class="card-h"><h2>Perlu ditindak</h2><span class="mut small"><?= count($todo) ?> dokumen</span></div>
            <div class="card-b"><?php partial('documents/_table', ['docs' => $todo]); ?></div>
        </section>
        <section class="card flush">
            <div class="card-h"><h2>Dokumen terbaru</h2><a class="small" href="<?= e(url('documents')) ?>">Lihat semua</a></div>
            <div class="card-b"><?php partial('documents/_compact', ['docs' => $recent]); ?></div>
        </section>
    </div>

<?php elseif ($mode === 'manager'): ?>
    <div class="kpis">
        <div class="kpi <?= $c('pending_local') ? 'warn' : '' ?>"><span class="ico"><?= icon('clock') ?></span><div><b><?= $c('pending_local') ?></b><span>Menunggu persetujuan lu</span></div></div>
        <div class="kpi info"><span class="ico"><?= icon('down') ?></span><div><b><?= $c('approved_local') ?></b><span>Disetujui lokal, menunggu Korea</span></div></div>
        <div class="kpi ok"><span class="ico"><?= icon('check') ?></span><div><b><?= $c('approved') ?></b><span>Disetujui final</span></div></div>
        <div class="kpi"><span class="ico"><?= icon('alert') ?></span><div><b><?= $rejected ?></b><span>Ditolak</span></div></div>
    </div>
    <div class="cols">
        <section class="card flush">
            <div class="card-h"><h2>Menunggu persetujuan</h2><span class="mut small"><?= count($pending) ?> dokumen</span></div>
            <div class="card-b"><?php partial('documents/_table', ['docs' => $pending]); ?></div>
        </section>
        <section class="card flush">
            <div class="card-h"><h2>Dokumen dept terbaru</h2><a class="small" href="<?= e(url('documents')) ?>">Lihat semua</a></div>
            <div class="card-b"><?php partial('documents/_compact', ['docs' => $recent]); ?></div>
        </section>
    </div>

<?php else: ?>
    <div class="kpis">
        <?php foreach (Document::STATUSES as $k => $label): ?>
            <div class="kpi"><span class="ico"><?= icon('file') ?></span><div><b><?= $c($k) ?></b><span><?= e($label) ?></span></div></div>
        <?php endforeach; ?>
    </div>
    <section class="card flush">
        <div class="card-h"><h2>Aktivitas terbaru</h2><a class="small" href="<?= e(url('activity')) ?>">Lihat semua</a></div>
        <div class="card-b"><?php partial('_logs', ['logs' => $recent]); ?></div>
    </section>
<?php endif; ?>
