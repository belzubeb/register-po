<?php
/* ---------- util ---------- */
function authorize($ability, $d = null)
{
    $u = Auth::user();
    $args = $d === null ? [$u] : [$u, $d];
    if (!call_user_func_array(['Policy', $ability], $args)) {
        abort(403, 'Lu tidak punya izin untuk tindakan ini.');
    }
}
function require_dev() { if (!Policy::isDev(Auth::user())) { abort(403, 'Hanya Developer yang boleh membuka halaman ini.'); } }
function load_doc($id)
{
    $d = Document::find((int) $id);
    if (!$d) { abort(404, 'Dokumen tidak ditemukan.'); }
    authorize('view', $d);
    return $d;
}
function post($k, $default = '') { return isset($_POST[$k]) ? $_POST[$k] : $default; }
function fail_back($route, $params, $errors)
{
    foreach ((array) $errors as $m) { flash_set('errors', $m); }
    old_keep($_POST);
    redirect($route, $params);
}
function pager_html($total, $page, $per, $route, $params)
{
    $pages = (int) ceil($total / $per);
    if ($pages <= 1) { return ''; }
    $h = '<div class="acts" style="justify-content:center;margin:1rem 0">';
    if ($page > 1) { $h .= '<a class="btn sm" href="' . e(url($route, $params + ['page' => $page - 1])) . '">&larr; Sebelumnya</a>'; }
    $h .= '<span class="mut small">Halaman ' . $page . ' dari ' . $pages . '</span>';
    if ($page < $pages) { $h .= '<a class="btn sm" href="' . e(url($route, $params + ['page' => $page + 1])) . '">Berikutnya &rarr;</a>'; }
    return $h . '</div>';
}

/* ---------- auth ---------- */
function auth_form()
{
    if (Auth::check()) { redirect('dashboard'); }
    render('login', ['title' => 'Masuk'], null);
}
function auth_login()
{
    $email = trim((string) post('email'));
    $pass = (string) post('password');
    if ($email === '' || $pass === '' || !Auth::attempt($email, $pass)) {
        flash_set('errors', 'Email atau password salah.');
        old_keep(['email' => $email]);
        redirect('login');
    }
    redirect('dashboard');
}
function auth_logout() { Auth::logout(); redirect('login'); }

/* ---------- dashboard ---------- */
function dashboard_index()
{
    $u = Auth::user();
    if ($u['role'] === 'dev') {
        $recent = DB::all('SELECT l.*, u.name AS user_name, d.number AS doc_number, d.id AS doc_id
                           FROM activity_logs l LEFT JOIN users u ON u.id = l.user_id LEFT JOIN documents d ON d.id = l.document_id
                           ORDER BY l.id DESC LIMIT 10');
        render('dashboard', ['title' => 'Dashboard', 'mode' => 'dev', 'counts' => Document::countsByStatus('1=1', []), 'recent' => $recent]);
        return;
    }
    $dept = (int) $u['department_id'];
    $counts = Document::countsByStatus('d.department_id = ?', [$dept]);
    if ($u['role'] === 'manager') {
        render('dashboard', [
            'title' => 'Dashboard', 'mode' => 'manager', 'counts' => $counts,
            'pending' => Document::listRows('d.department_id = ? AND d.status = ?', [$dept, 'pending_local'], 50),
            'recent' => Document::listRows('d.department_id = ?', [$dept], 8),
        ]);
        return;
    }
    render('dashboard', [
        'title' => 'Dashboard', 'mode' => 'admin', 'counts' => $counts,
        'todo' => Document::listRows("d.department_id = ? AND d.status IN ('draft','revision','rejected_local','rejected_korea','approved_local')", [$dept], 50),
        'recent' => Document::listRows('d.department_id = ?', [$dept], 8),
    ]);
}

/* ---------- dokumen ---------- */
function documents_index()
{
    $u = Auth::user();
    list($vis, $params) = Document::visibleSql($u, 'd');
    $where = [$vis];
    $filters = [];
    if (!empty($_GET['type']) && in_array($_GET['type'], ['PRP', 'PO'], true)) { $where[] = 'd.type = ?'; $params[] = $_GET['type']; $filters['type'] = $_GET['type']; }
    if (!empty($_GET['status']) && isset(Document::STATUSES[$_GET['status']])) { $where[] = 'd.status = ?'; $params[] = $_GET['status']; $filters['status'] = $_GET['status']; }
    if (!empty($_GET['q'])) {
        $q = '%' . trim((string) $_GET['q']) . '%';
        $where[] = '(d.number LIKE ? OR d.vendor LIKE ? OR d.subject LIKE ?)';
        array_push($params, $q, $q, $q);
        $filters['q'] = trim((string) $_GET['q']);
    }
    $w = implode(' AND ', $where);
    $page = max(1, (int) (isset($_GET['page']) ? $_GET['page'] : 1));
    $per = 20;
    $c = DB::one('SELECT COUNT(*) AS c FROM documents d WHERE ' . $w, $params);
    $docs = Document::listRows($w, $params, $per, ($page - 1) * $per);
    render('documents/list', [
        'title' => 'Dokumen', 'docs' => $docs, 'filters' => $filters,
        'pager' => pager_html((int) $c['c'], $page, $per, 'documents', $filters),
        'canCreate' => Policy::create($u),
    ]);
}

function form_data($d)
{
    $u = Auth::user();
    return [
        'd' => $d,
        'departments' => DB::all('SELECT * FROM departments WHERE id <> ? ORDER BY code', [(int) $u['department_id']]),
        'proposals' => DB::all("SELECT id, number, subject FROM documents WHERE type = 'PRP' AND department_id = ? AND number IS NOT NULL ORDER BY id DESC", [(int) $u['department_id']]),
        'items' => $d ? Document::items($d['id']) : [],
        'bodyHtml' => $d ? Document::bodyHtml($d, Document::items($d['id'])) : '',
    ];
}
function documents_create()
{
    authorize('create');
    render('documents/form', ['title' => 'Dokumen baru'] + form_data(null));
}
function documents_edit()
{
    $d = load_doc(isset($_GET['id']) ? $_GET['id'] : 0);
    authorize('update', $d);
    render('documents/form', ['title' => 'Ubah dokumen'] + form_data($d));
}

/** Validasi input form. Kembalikan [data, daftar_error, items]. */
function parse_doc_input($type, $deptId)
{
    $errors = [];
    $in = [];
    $rel = post('related_department_id', '');
    $in['related_department_id'] = null;
    if ($rel !== '') {
        if (DB::one('SELECT id FROM departments WHERE id = ?', [(int) $rel])) { $in['related_department_id'] = (int) $rel; }
        else { $errors[] = 'Dept terkait tidak valid.'; }
    }
    $prio = post('priority', 'sedang');
    if (!in_array($prio, ['tinggi', 'sedang', 'rendah'], true)) { $errors[] = 'Prioritas tidak valid.'; $prio = 'sedang'; }
    $in['priority'] = $prio;

    $subject = trim((string) post('subject'));
    $vendor = trim((string) post('vendor'));
    if ($type === 'PRP' && $subject === '') { $errors[] = 'Subjek wajib diisi untuk proposal.'; }
    if ($type === 'PO' && $vendor === '') { $errors[] = 'Vendor wajib diisi untuk PO.'; }
    if (strlen($subject) > 255 || strlen($vendor) > 255) { $errors[] = 'Subjek atau vendor terlalu panjang.'; }
    $in['subject'] = $type === 'PRP' ? $subject : null;
    $in['vendor'] = $type === 'PO' ? $vendor : null;

    $addr = trim(str_replace("\r\n", "\n", (string) post('vendor_address')));
    if (mb_strlen($addr) > 500) { $errors[] = 'Alamat vendor maksimal 500 karakter.'; }
    $in['vendor_address'] = ($type === 'PO' && $addr !== '') ? $addr : null;

    $pay = post('pay_type', '');
    $in['pay_type'] = null;
    if ($type === 'PO' && $pay !== '') {
        if (in_array($pay, ['cash', 'tempo'], true)) { $in['pay_type'] = $pay; } else { $errors[] = 'Tipe bayar tidak valid.'; }
    }

    $in['discount'] = 0.0;
    $disc = trim((string) post('discount', ''));
    if ($type === 'PO' && $disc !== '') {
        if (is_numeric($disc) && (float) $disc >= 0) { $in['discount'] = round((float) $disc, 2); }
        else { $errors[] = 'Diskon harus angka dan tidak boleh negatif.'; }
    }

    $in['ref_document_id'] = null;
    $ref = post('ref_document_id', '');
    if ($type === 'PO' && $ref !== '') {
        $ok = DB::one("SELECT id FROM documents WHERE id = ? AND type = 'PRP' AND department_id = ?", [(int) $ref, (int) $deptId]);
        if ($ok) { $in['ref_document_id'] = (int) $ref; } else { $errors[] = 'Proposal rujukan tidak valid.'; }
    }

    $in['w5'] = null;
    if ($type === 'PRP') {
        $w5 = [];
        foreach (['what', 'why', 'who', 'when', 'where', 'how'] as $k) {
            $v = isset($_POST['w5'][$k]) ? trim((string) $_POST['w5'][$k]) : '';
            if (strlen($v) > 2000) { $errors[] = 'Isian 5W1H terlalu panjang.'; $v = substr($v, 0, 2000); }
            $w5[$k] = $v;
        }
        $in['w5'] = json_encode($w5, JSON_UNESCAPED_UNICODE);
    }

    // Proposal: barang ditulis bebas di editor WYSIWYG (disanitasi di server). PO: tabel barang biasa.
    $in['body'] = null;
    if ($type === 'PRP') {
        $rawBody = (string) post('body');
        if (strlen($rawBody) > Sanitizer::MAX_BYTES) {
            $errors[] = 'Isi proposal terlalu besar (gambar terlalu banyak atau terlalu berat). Kecilkan atau kurangi gambar.';
        } else {
            $clean = Sanitizer::clean($rawBody);
            if (!Sanitizer::hasContent($clean)) { $errors[] = 'Isi barang proposal wajib diisi.'; }
            $in['body'] = $clean;
        }
    }

    $items = [];
    $raw = ($type === 'PO' && isset($_POST['items']) && is_array($_POST['items'])) ? $_POST['items'] : [];
    foreach ($raw as $it) {
        $name = isset($it['name']) ? trim((string) $it['name']) : '';
        if ($name === '') { continue; }
        $qty = isset($it['qty']) ? $it['qty'] : '';
        $price = isset($it['price']) ? $it['price'] : '';
        $unit = isset($it['unit']) ? trim((string) $it['unit']) : '';
        if (!is_numeric($qty) || $qty < 0 || !is_numeric($price) || $price < 0) { $errors[] = 'Qty dan harga harus angka dan tidak boleh negatif.'; continue; }
        if ($unit === '' || strlen($unit) > 20 || strlen($name) > 255) { $errors[] = 'Satuan wajib diisi (maks. 20 karakter), nama barang maks. 255.'; continue; }
        $items[] = ['name' => $name, 'qty' => (float) $qty, 'unit' => $unit, 'price' => (float) $price];
    }
    if ($type === 'PO' && !$items) { $errors[] = 'Isi minimal satu barang.'; }
    if ($type === 'PO' && $items && $in['discount'] > Document::total($items)) { $errors[] = 'Diskon tidak boleh lebih besar dari subtotal barang.'; }
    return [$in, array_values(array_unique($errors)), $items];
}
function insert_items($docId, $items)
{
    foreach ($items as $i) {
        DB::run('INSERT INTO document_items (document_id, name, qty, unit, price) VALUES (?,?,?,?,?)', [$docId, $i['name'], $i['qty'], $i['unit'], $i['price']]);
    }
}

function documents_store()
{
    authorize('create');
    $u = Auth::user();
    $type = (string) post('type');
    if (!in_array($type, ['PRP', 'PO'], true)) { abort(422, 'Jenis dokumen tidak valid.'); }
    list($in, $errors, $items) = parse_doc_input($type, $u['department_id']);
    if ($errors) { fail_back('documents/create', [], $errors); }

    $id = DB::tx(function () use ($type, $u, $in, $items) {
        $now = now_sql();
        $id = DB::insert(
            'INSERT INTO documents (type, department_id, related_department_id, priority, subject, vendor, vendor_address, pay_type, discount, ref_document_id, w5, body, created_by, created_at, updated_at)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
            [$type, $u['department_id'], $in['related_department_id'], $in['priority'], $in['subject'], $in['vendor'], $in['vendor_address'],
             $in['pay_type'], $in['discount'], $in['ref_document_id'], $in['w5'], $in['body'], $u['id'], $now, $now]
        );
        insert_items($id, $items);
        Document::log($id, $u['id'], 'Dibuat (draft)');
        return $id;
    });
    flash_set('ok', 'Draft tersimpan. Klik Ajukan supaya nomor terbit.');
    redirect('documents/show', ['id' => $id]);
}

function documents_update()
{
    $d = load_doc(post('id', 0));
    authorize('update', $d);
    $u = Auth::user();
    list($in, $errors, $items) = parse_doc_input($d['type'], $d['department_id']);
    if ($errors) { fail_back('documents/edit', ['id' => $d['id']], $errors); }

    DB::tx(function () use ($d, $u, $in, $items) {
        DB::run('UPDATE documents SET related_department_id = ?, priority = ?, subject = ?, vendor = ?, vendor_address = ?, pay_type = ?, discount = ?, ref_document_id = ?, w5 = ?, body = ?, updated_at = ? WHERE id = ?',
            [$in['related_department_id'], $in['priority'], $in['subject'], $in['vendor'], $in['vendor_address'], $in['pay_type'], $in['discount'], $in['ref_document_id'], $in['w5'], $in['body'], now_sql(), $d['id']]);
        DB::run('DELETE FROM document_items WHERE document_id = ?', [$d['id']]);
        insert_items($d['id'], $items);
        Document::log($d['id'], $u['id'], 'Diubah');
    });
    flash_set('ok', 'Perubahan tersimpan.');
    redirect('documents/show', ['id' => $d['id']]);
}

function documents_delete()
{
    $d = load_doc(post('id', 0));
    authorize('delete', $d);
    DB::run('DELETE FROM documents WHERE id = ?', [$d['id']]);
    flash_set('ok', 'Draft dihapus.');
    redirect('documents');
}

function documents_show()
{
    $u = Auth::user();
    $d = load_doc(isset($_GET['id']) ? $_GET['id'] : 0);
    $items = Document::items($d['id']);
    render('documents/show', [
        'title' => Document::displayNumber($d),
        'd' => $d, 'items' => $items, 'w5' => Document::w5($d),
        'bodyHtml' => Policy::seePrices($u, $d) ? Document::bodyHtml($d, $items) : '',
        'derived' => DB::all('SELECT id, number FROM documents WHERE ref_document_id = ?', [$d['id']]),
        'revisions' => DB::all('SELECT r.*, u.name AS user_name FROM document_revisions r LEFT JOIN users u ON u.id = r.created_by WHERE r.document_id = ? ORDER BY r.revision DESC', [$d['id']]),
        'logs' => DB::all('SELECT l.*, u.name AS user_name FROM activity_logs l LEFT JOIN users u ON u.id = l.user_id WHERE l.document_id = ? ORDER BY l.id DESC', [$d['id']]),
        'can' => [
            'update' => Policy::update($u, $d), 'delete' => Policy::delete($u, $d),
            'approve' => Policy::approveLocal($u, $d), 'korea' => Policy::recordKorea($u, $d),
            'revise' => Policy::revise($u, $d), 'download' => Policy::download($u, $d),
            'prices' => Policy::seePrices($u, $d),
        ],
    ]);
}

function documents_submit()
{
    $d = load_doc(post('id', 0));
    authorize('submit', $d);
    $no = Workflow::submit($d, Auth::user());
    flash_set('ok', 'Dokumen diajukan. Nomor: ' . $no);
    redirect('documents/show', ['id' => $d['id']]);
}
function documents_approve()
{
    $d = load_doc(post('id', 0));
    authorize('approveLocal', $d);
    Workflow::approveLocal($d, Auth::user());
    flash_set('ok', 'Disetujui. PDF sekarang bisa diunduh.');
    redirect('documents/show', ['id' => $d['id']]);
}
function documents_reject()
{
    $d = load_doc(post('id', 0));
    authorize('approveLocal', $d);
    $reason = trim((string) post('reason'));
    if ($reason === '') { flash_set('errors', 'Alasan penolakan wajib diisi.'); redirect('documents/show', ['id' => $d['id']]); }
    Workflow::rejectLocal($d, Auth::user(), $reason);
    flash_set('ok', 'Dokumen ditolak.');
    redirect('documents/show', ['id' => $d['id']]);
}
function documents_korea()
{
    $d = load_doc(post('id', 0));
    authorize('recordKorea', $d);
    Workflow::recordKorea($d, Auth::user(), (string) post('result'), trim((string) post('note')));
    flash_set('ok', 'Hasil Korea dicatat.');
    redirect('documents/show', ['id' => $d['id']]);
}
function documents_revise()
{
    $d = load_doc(post('id', 0));
    authorize('revise', $d);
    $reason = trim((string) post('reason'));
    if ($reason === '') { flash_set('errors', 'Alasan revisi wajib diisi.'); redirect('documents/show', ['id' => $d['id']]); }
    Workflow::revise($d, Auth::user(), $reason);
    flash_set('ok', 'Revisi dimulai. Ubah dokumen lalu ajukan ulang.');
    redirect('documents/edit', ['id' => $d['id']]);
}

function documents_download()
{
    $d = load_doc(isset($_GET['id']) ? $_GET['id'] : 0);
    authorize('download', $d);
    $u = Auth::user();
    Document::log($d['id'], $u['id'], 'PDF diunduh', 'Rev. ' . $d['revision']);
    $items = Document::items($d['id']);
    $data = ['d' => $d, 'items' => $items, 'w5' => Document::w5($d), 'bodyHtml' => Document::bodyHtml($d, $items)];
    $name = str_replace('/', '-', $d['number']) . '-rev' . $d['revision'] . '.pdf';

    if (class_exists('Dompdf\Dompdf')) {
        $html = view_string('pdf', $data + ['print' => false]);
        $pdf = new Dompdf\Dompdf(['defaultFont' => 'DejaVu Sans', 'isHtml5ParserEnabled' => true]);
        $pdf->loadHtml($html);
        $pdf->setPaper('A4', 'portrait');
        $pdf->render();
        $pdf->stream($name, ['Attachment' => true]);
        exit;
    }
    // Cadangan kalau Dompdf belum terpasang: halaman siap cetak (pilih "Simpan sebagai PDF").
    render('pdf', $data + ['print' => true], null);
}

/* ---------- panduan ---------- */
function panduan_index()
{
    $u = Auth::user();
    $role = isset($_GET['role']) ? (string) $_GET['role'] : $u['role'];
    if (!in_array($role, ['admin', 'manager', 'dev'], true)) { $role = $u['role']; }
    render('panduan', ['title' => 'Panduan', 'role' => $role, 'mine' => $u['role']]);
}

/* ---------- developer ---------- */
function activity_index()
{
    require_dev();
    $page = max(1, (int) (isset($_GET['page']) ? $_GET['page'] : 1));
    $per = 50;
    $c = DB::one('SELECT COUNT(*) AS c FROM activity_logs');
    $logs = DB::all('SELECT l.*, u.name AS user_name, d.number AS doc_number, d.id AS doc_id
                     FROM activity_logs l LEFT JOIN users u ON u.id = l.user_id LEFT JOIN documents d ON d.id = l.document_id
                     ORDER BY l.id DESC LIMIT ' . $per . ' OFFSET ' . (($page - 1) * $per));
    render('activity', ['title' => 'Aktivitas', 'logs' => $logs, 'pager' => pager_html((int) $c['c'], $page, $per, 'activity', [])]);
}
function users_index()
{
    require_dev();
    render('users', [
        'title' => 'Pengguna',
        'users' => DB::all('SELECT u.*, d.code AS dept_code FROM users u LEFT JOIN departments d ON d.id = u.department_id ORDER BY u.name'),
        'departments' => DB::all('SELECT * FROM departments ORDER BY code'),
    ]);
}
function users_store()
{
    require_dev();
    $name = trim((string) post('name'));
    $email = trim((string) post('email'));
    $pass = (string) post('password');
    $role = (string) post('role');
    $dept = post('department_id', '');
    $errors = [];
    if ($name === '' || strlen($name) > 100) { $errors[] = 'Nama wajib diisi (maks. 100 karakter).'; }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors[] = 'Email tidak valid.'; }
    elseif (DB::one('SELECT id FROM users WHERE email = ?', [$email])) { $errors[] = 'Email sudah dipakai.'; }
    if (strlen($pass) < 8) { $errors[] = 'Password minimal 8 karakter.'; }
    if (!in_array($role, ['admin', 'manager', 'dev'], true)) { $errors[] = 'Peran tidak valid.'; }
    if ($role !== 'dev' && $dept === '') { $errors[] = 'Dept wajib untuk Admin dan Manager.'; }
    if ($dept !== '' && !DB::one('SELECT id FROM departments WHERE id = ?', [(int) $dept])) { $errors[] = 'Dept tidak valid.'; }
    if ($errors) { fail_back('users', [], $errors); }
    DB::run('INSERT INTO users (name, email, password, role, department_id) VALUES (?,?,?,?,?)',
        [$name, $email, password_hash($pass, PASSWORD_DEFAULT), $role, $role === 'dev' ? null : (int) $dept]);
    flash_set('ok', 'Pengguna dibuat.');
    redirect('users');
}