<?php
/** Aturan siapa boleh apa. Semua pengecekan dilakukan di server. */
class Policy
{
    private static function sameDept($u, $d)
    {
        return $u['department_id'] !== null && (int) $u['department_id'] === (int) $d['department_id'];
    }
    private static function isRole($u, $r) { return $u['role'] === $r; }

    public static function view($u, $d)
    {
        return self::isRole($u, 'dev') || self::sameDept($u, $d)
            || ($u['department_id'] !== null && $d['related_department_id'] !== null
                && (int) $u['department_id'] === (int) $d['related_department_id']);
    }
    public static function create($u) { return self::isRole($u, 'admin') && $u['department_id'] !== null; }
    public static function seePrices($u, $d) { return self::isRole($u, 'dev') || self::sameDept($u, $d); }
    public static function update($u, $d)
    {
        return self::isRole($u, 'admin') && self::sameDept($u, $d) && in_array($d['status'], Document::EDITABLE, true);
    }
    public static function submit($u, $d) { return self::update($u, $d); }
    public static function delete($u, $d)
    {
        return self::isRole($u, 'admin') && self::sameDept($u, $d) && $d['status'] === 'draft' && $d['number'] === null;
    }
    public static function approveLocal($u, $d)
    {
        return self::isRole($u, 'manager') && self::sameDept($u, $d) && $d['status'] === 'pending_local';
    }
    public static function recordKorea($u, $d)
    {
        return self::isRole($u, 'admin') && self::sameDept($u, $d) && $d['status'] === 'approved_local';
    }
    public static function revise($u, $d)
    {
        return self::isRole($u, 'admin') && self::sameDept($u, $d) && in_array($d['status'], Document::REVISABLE, true);
    }
    /** PDF hanya boleh diunduh setelah disetujui Manager lokal. */
    public static function download($u, $d)
    {
        return self::view($u, $d) && self::seePrices($u, $d) && in_array($d['status'], Document::DOWNLOADABLE, true);
    }
    public static function isDev($u) { return self::isRole($u, 'dev'); }
}
