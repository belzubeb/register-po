<div class="page-h">
    <div><h1>Dokumen</h1><p class="sub">Proposal dan PO yang bisa lu lihat.</p></div>
    <div class="acts">
        <?php if ($canCreate): ?><a class="btn p" href="<?= e(url('documents/create')) ?>"><?= icon('plus') ?> Dokumen baru</a><?php endif; ?>
    </div>
</div>

<section class="card flush">
    <form method="GET" action="<?= e(base_path() . '/index.php') ?>" class="filters" role="search">
        <input type="hidden" name="r" value="documents">
        <input name="q" value="<?= e(isset($filters['q']) ? $filters['q'] : '') ?>" placeholder="Cari nomor, vendor, subjek" aria-label="Cari">
        <select name="type" aria-label="Jenis">
            <option value="">Semua jenis</option>
            <option value="PRP"<?= sel(isset($filters['type']) && $filters['type'] === 'PRP') ?>>Proposal</option>
            <option value="PO"<?= sel(isset($filters['type']) && $filters['type'] === 'PO') ?>>PO</option>
        </select>
        <select name="status" aria-label="Status">
            <option value="">Semua status</option>
            <?php foreach (Document::STATUSES as $k => $label): ?>
                <option value="<?= e($k) ?>"<?= sel(isset($filters['status']) && $filters['status'] === $k) ?>><?= e($label) ?></option>
            <?php endforeach; ?>
        </select>
        <button class="btn" type="submit">Terapkan</button>
        <?php if ($filters): ?><a class="btn" href="<?= e(url('documents')) ?>">Reset</a><?php endif; ?>
    </form>
    <div class="card-b"><?php partial('documents/_table', ['docs' => $docs]); ?></div>
</section>
<?= $pager ?>
