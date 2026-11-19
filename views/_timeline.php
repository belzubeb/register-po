<ol class="tl">
    <?php foreach ($logs as $l): ?>
        <li>
            <b><?= e($l['action']) ?></b>
            <?php if ($l['note']): ?><div class="mut"><?= e($l['note']) ?></div><?php endif; ?>
            <div class="mut small"><?= e($l['user_name'] ?: '-') ?> &middot; <?= e(fmt_dt($l['created_at'])) ?></div>
        </li>
    <?php endforeach; ?>
    <?php if (!$logs): ?><li class="mut">Belum ada aktivitas.</li><?php endif; ?>
</ol>
