<div class="tw"><table>
    <thead><tr><th>Nomor</th><th>Jenis</th><th>Subjek / vendor</th><th>Dept</th><th>Status</th></tr></thead>
    <tbody>
    <?php foreach ($docs as $d): ?>
        <tr>
            <td class="num"><a href="<?= e(url('documents/show', ['id' => $d['id']])) ?>"><?= e(Document::displayNumber($d)) ?></a></td>
            <td><?= e(Document::typeLabel($d)) ?></td>
            <td><?= e($d['vendor'] ?: $d['subject']) ?></td>
            <td><?= e($d['dept_code']) ?><?= $d['rel_code'] ? ' - ' . e($d['rel_code']) : '' ?></td>
            <td><span class="t t-<?= e($d['status']) ?>"><?= e(Document::statusLabel($d['status'])) ?></span></td>
        </tr>
    <?php endforeach; ?>
    <?php if (!$docs): ?><tr><td colspan="5" class="empty"><?= icon('file') ?>Belum ada dokumen di sini.</td></tr><?php endif; ?>
    </tbody>
</table></div>
