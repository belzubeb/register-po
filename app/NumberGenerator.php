<?php
/**
 * Nomor: urut/tgl/bln/DEPT[-DEPT_TERKAIT]/tahun
 * Urutan per departemen, per jenis (PRP/PO), reset tiap tahun.
 * HARUS dipanggil di dalam DB::tx(); baris counter dikunci (FOR UPDATE)
 * supaya dua orang yang mengajukan bersamaan tidak dapat nomor kembar.
 */
class NumberGenerator
{
    public static function next($d)
    {
        $year = (int) date('Y');
        DB::run('INSERT IGNORE INTO number_counters (department_id, type, year, last_seq) VALUES (?,?,?,0)',
            [$d['department_id'], $d['type'], $year]);
        $row = DB::one('SELECT id, last_seq FROM number_counters WHERE department_id = ? AND type = ? AND year = ? FOR UPDATE',
            [$d['department_id'], $d['type'], $year]);
        $seq = (int) $row['last_seq'] + 1;
        DB::run('UPDATE number_counters SET last_seq = ? WHERE id = ?', [$seq, $row['id']]);

        $rel = !empty($d['rel_code']) ? '-' . $d['rel_code'] : '';
        $number = sprintf('%03d/%s/%s/%s%s/%d', $seq, date('d'), date('m'), $d['dept_code'], $rel, $year);
        return [$seq, $year, $number];
    }
}
