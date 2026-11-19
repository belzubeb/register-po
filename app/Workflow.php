<?php
class Workflow
{
    private static function need($d, $allowed)
    {
        if (!in_array($d['status'], $allowed, true)) {
            abort(422, 'Status dokumen tidak mengizinkan tindakan ini.');
        }
    }

    /** Draft atau Revisi -> Menunggu Manager lokal. Nomor terbit saat pertama kali diajukan. */
    public static function submit($d, $u)
    {
        self::need($d, Document::EDITABLE);
        $c = DB::one('SELECT COUNT(*) AS c FROM document_items WHERE document_id = ?', [$d['id']]);
        if ($d['type'] === 'PO') {
            if ((int) $c['c'] === 0) { abort(422, 'Isi minimal satu barang.'); }
        } elseif ((int) $c['c'] === 0 && !Sanitizer::hasContent(isset($d['body']) ? $d['body'] : '')) {
            abort(422, 'Isi barang proposal belum diisi.');
        }

        DB::tx(function () use ($d, $u) {
            $no = $d['number'];
            $seq = $d['seq'];
            $year = $d['year'];
            if (!$no) { list($seq, $year, $no) = NumberGenerator::next($d); }
            DB::run('UPDATE documents SET status = ?, number = ?, seq = ?, year = ?, reject_reason = NULL, updated_at = ? WHERE id = ?',
                ['pending_local', $no, $seq, $year, now_sql(), $d['id']]);
            Document::log($d['id'], $u['id'], 'Diajukan', 'Rev. ' . $d['revision']);
        });
        $r = DB::one('SELECT number FROM documents WHERE id = ?', [$d['id']]);
        return $r['number'];
    }

    /** Satu-satunya approval digital. Setelah ini PDF boleh diunduh. */
    public static function approveLocal($d, $u)
    {
        self::need($d, ['pending_local']);
        DB::run('UPDATE documents SET status = ?, local_by = ?, local_at = ?, updated_at = ? WHERE id = ?',
            ['approved_local', $u['id'], now_sql(), now_sql(), $d['id']]);
        Document::log($d['id'], $u['id'], 'Disetujui Manager lokal');
    }

    public static function rejectLocal($d, $u, $reason)
    {
        self::need($d, ['pending_local']);
        DB::run('UPDATE documents SET status = ?, reject_reason = ?, updated_at = ? WHERE id = ?',
            ['rejected_local', $reason, now_sql(), $d['id']]);
        Document::log($d['id'], $u['id'], 'Ditolak Manager lokal', $reason);
    }

    /** Admin mencatat hasil approval Korea (dilakukan di luar sistem). */
    public static function recordKorea($d, $u, $result, $note)
    {
        self::need($d, ['approved_local']);
        if (!in_array($result, ['approved', 'rejected'], true)) { abort(422, 'Hasil Korea tidak valid.'); }
        if ($result === 'rejected' && trim((string) $note) === '') { abort(422, 'Alasan penolakan Korea wajib diisi.'); }

        DB::run('UPDATE documents SET status = ?, korea_result = ?, korea_by = ?, korea_at = ?, korea_note = ?, reject_reason = ?, updated_at = ? WHERE id = ?',
            [$result === 'approved' ? 'approved' : 'rejected_korea', $result, $u['id'], now_sql(), $note ?: null,
             $result === 'rejected' ? $note : null, now_sql(), $d['id']]);
        Document::log($d['id'], $u['id'], $result === 'approved' ? 'Korea menyetujui (dicatat)' : 'Korea menolak (dicatat)', $note ?: null);
    }

    /** Revisi sebelum maupun sesudah disetujui. Nomor tetap, revisi naik, approval diulang. */
    public static function revise($d, $u, $reason)
    {
        self::need($d, Document::REVISABLE);
        DB::tx(function () use ($d, $u, $reason) {
            $snapshot = [
                'status' => $d['status'], 'subject' => $d['subject'], 'vendor' => $d['vendor'],
                'vendor_address' => isset($d['vendor_address']) ? $d['vendor_address'] : null, 'body' => isset($d['body']) ? $d['body'] : null,
                'pay_type' => $d['pay_type'], 'w5' => Document::w5($d), 'items' => Document::items($d['id']),
                'local_at' => $d['local_at'], 'korea_result' => $d['korea_result'],
            ];
            DB::run('INSERT INTO document_revisions (document_id, revision, snapshot, reason, created_by, created_at) VALUES (?,?,?,?,?,?)',
                [$d['id'], $d['revision'], json_encode($snapshot, JSON_UNESCAPED_UNICODE), $reason, $u['id'], now_sql()]);
            DB::run('UPDATE documents SET revision = revision + 1, status = ?, local_by = NULL, local_at = NULL,
                     korea_result = NULL, korea_by = NULL, korea_at = NULL, korea_note = NULL, reject_reason = NULL, updated_at = ? WHERE id = ?',
                ['revision', now_sql(), $d['id']]);
            Document::log($d['id'], $u['id'], 'Revisi dimulai (Rev. ' . ($d['revision'] + 1) . ')', $reason);
        });
    }
}
