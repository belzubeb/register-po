<div class="tw"><table>
    <thead><tr><th>Waktu</th><th>Siapa</th><th>Dokumen</th><th>Aktivitas</th></tr></thead>
    <tbody>
    <?php foreach ($logs as $l): ?>
        <tr>
            <td class="num"><?= e(fmt_dt($l['created_at'])) ?></td>
            <td><?= e($l['user_name'] ?: '-') ?></td>
            <td class="num"><?php if (!empty($l['doc_id'])): ?><a href="<?= e(url('documents/show', ['id' => $l['doc_id']])) ?>"><?= e($l['doc_number'] ?: 'Draft #' . $l['doc_id']) ?></a><?php else: ?>-<?php endif; ?></td>
            <td><?= e($l['action']) ?><?= $l['note'] ? ' (' . e($l['note']) . ')' : '' ?></td>
        </tr>
    <?php endforeach; ?>
    <?php if (!$logs): ?><tr><td colspan="4" class="empty"><?= icon('activity') ?>Belum ada aktivitas.</td></tr><?php endif; ?>
    </tbody>
</table></div>
