<?php
class Document
{
    const STATUSES = [
        'draft'          => 'Draft',
        'pending_local'  => 'Menunggu Manager lokal',
        'rejected_local' => 'Ditolak Lokal',
        'approved_local' => 'Disetujui Lokal (menunggu Korea)',
        'rejected_korea' => 'Ditolak Korea',
        'approved'       => 'Disetujui',
        'revision'       => 'Revisi',
    ];
    const DOWNLOADABLE = ['approved_local', 'approved'];
    const EDITABLE = ['draft', 'revision'];
    const REVISABLE = ['pending_local', 'approved_local', 'approved', 'rejected_local', 'rejected_korea'];

    public static function statusLabel($s) { return isset(self::STATUSES[$s]) ? self::STATUSES[$s] : $s; }
    public static function typeLabel($d) { return $d['type'] === 'PO' ? 'PO' : 'Proposal'; }

    public static function displayNumber($d)
    {
        return $d['number'] ? $d['number'] . ' (Rev. ' . $d['revision'] . ')' : 'Belum bernomor';
    }

    public static function find($id)
    {
        return DB::one(
            'SELECT d.*, dp.code AS dept_code, dp.name AS dept_name, rp.code AS rel_code, rp.name AS rel_name,
                    cu.name AS creator_name, lu.name AS local_name, ku.name AS korea_name, rd.number AS ref_number
             FROM documents d
             JOIN departments dp ON dp.id = d.department_id
             LEFT JOIN departments rp ON rp.id = d.related_department_id
             LEFT JOIN users cu ON cu.id = d.created_by
             LEFT JOIN users lu ON lu.id = d.local_by
             LEFT JOIN users ku ON ku.id = d.korea_by
             LEFT JOIN documents rd ON rd.id = d.ref_document_id
             WHERE d.id = ?',
            [$id]
        );
    }

    public static function items($id)
    {
        return DB::all('SELECT * FROM document_items WHERE document_id = ? ORDER BY id', [$id]);
    }

    public static function total($items)
    {
        $t = 0.0;
        foreach ($items as $i) { $t += (float) $i['qty'] * (float) $i['price']; }
        return $t;
    }

    /** Tabel HTML dari barang lama (proposal yang dibuat sebelum ada WYSIWYG). */
    public static function legacyItemsHtml($items)
    {
        if (!$items) { return ''; }
        $h = '<table border="1" style="border-collapse: collapse; width: 100%;"><thead><tr>'
           . '<th>No.</th><th>Nama Part</th><th>Qty</th><th>Stn</th><th>Harga</th><th>Total</th></tr></thead><tbody>';
        foreach ($items as $k => $i) {
            $h .= '<tr><td style="text-align: center;">' . ($k + 1) . '</td><td>' . e($i['name']) . '</td>'
                . '<td style="text-align: center;">' . e(num($i['qty'])) . '</td><td style="text-align: center;">' . e($i['unit']) . '</td>'
                . '<td style="text-align: right;">' . e(rp($i['price'])) . '</td><td style="text-align: right;">' . e(rp($i['qty'] * $i['price'])) . '</td></tr>';
        }
        return $h . '<tr><th colspan="5" style="text-align: right;">Total Biaya</th><th style="text-align: right;">' . e(rp(self::total($items))) . '</th></tr></tbody></table>';
    }

    /** HTML isi proposal yang aman ditampilkan. Kalau belum ada isi WYSIWYG, pakai barang lama. */
    public static function bodyHtml($d, $items)
    {
        $b = isset($d['body']) ? (string) $d['body'] : '';
        if (Sanitizer::hasContent($b)) { return Sanitizer::clean($b); }
        return self::legacyItemsHtml($items);
    }

    public static function w5($d) { return $d['w5'] ? (json_decode($d['w5'], true) ?: []) : []; }

    /** Dokumen yang boleh dilihat user: dept sendiri atau dept terkait. Dev melihat semua. */
    public static function visibleSql($u, $alias = 'd')
    {
        if ($u['role'] === 'dev') { return ['1=1', []]; }
        return ["($alias.department_id = ? OR $alias.related_department_id = ?)", [$u['department_id'], $u['department_id']]];
    }

    public static function log($docId, $userId, $action, $note = null)
    {
        DB::run('INSERT INTO activity_logs (document_id, user_id, action, note, created_at) VALUES (?,?,?,?,?)',
            [$docId, $userId, $action, $note, now_sql()]);
    }

    public static function listRows($where, $params, $limit = 50, $offset = 0)
    {
        return DB::all(
            'SELECT d.*, dp.code AS dept_code, rp.code AS rel_code
             FROM documents d
             JOIN departments dp ON dp.id = d.department_id
             LEFT JOIN departments rp ON rp.id = d.related_department_id
             WHERE ' . $where . ' ORDER BY d.id DESC LIMIT ' . (int) $limit . ' OFFSET ' . (int) $offset,
            $params
        );
    }

    public static function countsByStatus($where, $params)
    {
        $out = [];
        foreach (DB::all('SELECT status, COUNT(*) AS c FROM documents d WHERE ' . $where . ' GROUP BY status', $params) as $r) {
            $out[$r['status']] = (int) $r['c'];
        }
        return $out;
    }
}
