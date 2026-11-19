<ul class="list">
    <?php foreach ($docs as $d): ?>
        <li>
            <div class="grow">
                <a href="<?= e(url('documents/show', ['id' => $d['id']])) ?>" class="num"><?= e($d['number'] ?: 'Draft #' . $d['id']) ?></a>
                <span><?= e(Document::typeLabel($d)) ?> &middot; <?= e($d['vendor'] ?: $d['subject']) ?></span>
            </div>
            <span class="t t-<?= e($d['status']) ?>"><?= e(Document::statusLabel($d['status'])) ?></span>
        </li>
    <?php endforeach; ?>
    <?php if (!$docs): ?><li class="mut" style="justify-content:center;padding:2rem">Belum ada dokumen.</li><?php endif; ?>
</ul>
