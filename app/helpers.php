<?php
function e($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); }

function base_path() {
    $d = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    return $d === '/' ? '' : rtrim($d, '/');
}
function url($r, $params = []) { return base_path() . '/index.php?' . http_build_query(array_merge(['r' => $r], $params)); }
function asset($p) { return base_path() . '/' . ltrim($p, '/'); }
function redirect($r, $params = []) { header('Location: ' . url($r, $params)); exit; }
function back($fallback = 'dashboard') {
    $ref = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
    $host = parse_url($ref, PHP_URL_HOST);
    if ($ref && $host && $host === parse_url('http://' . $_SERVER['HTTP_HOST'], PHP_URL_HOST)) {
        header('Location: ' . $ref);
        exit;
    }
    redirect($fallback);
}

function csrf_token() {
    if (empty($_SESSION['csrf'])) { $_SESSION['csrf'] = bin2hex(random_bytes(32)); }
    return $_SESSION['csrf'];
}
function csrf_field() { return '<input type="hidden" name="_token" value="' . e(csrf_token()) . '">'; }
function verify_csrf() {
    $t = isset($_POST['_token']) ? $_POST['_token'] : '';
    if (!hash_equals(isset($_SESSION['csrf']) ? $_SESSION['csrf'] : '', (string) $t)) {
        abort(419, 'Sesi kedaluwarsa. Muat ulang halaman lalu coba lagi.');
    }
}

function flash_set($type, $msg) { $_SESSION['flash'][$type][] = $msg; }
function flash_get() { $f = isset($_SESSION['flash']) ? $_SESSION['flash'] : []; unset($_SESSION['flash']); return $f; }
function old_keep($arr) { $_SESSION['old'] = $arr; }
function old($k, $default = '') { return isset($GLOBALS['__old'][$k]) ? $GLOBALS['__old'][$k] : $default; }

function rp($v) { return 'Rp ' . number_format((float) $v, 0, ',', '.'); }
function num($v) { return rtrim(rtrim(number_format((float) $v, 2, '.', ''), '0'), '.'); }
function fmt_date($ts) { return $ts ? date('d M Y', strtotime($ts)) : '-'; }
function fmt_dt($ts) { return $ts ? date('d M Y H:i', strtotime($ts)) : '-'; }
function now_sql() { return date('Y-m-d H:i:s'); }

function view_string($view, $data = []) {
    extract($data);
    ob_start();
    include VIEWS . '/' . $view . '.php';
    return ob_get_clean();
}
function render($view, $data = [], $layout = 'layout') {
    $content = view_string($view, $data);
    if ($layout) {
        $title = isset($data['title']) ? $data['title'] : 'Register Proposal dan PO';
        include VIEWS . '/' . $layout . '.php';
    } else {
        echo $content;
    }
}
function partial($view, $data = []) { extract($data); include VIEWS . '/' . $view . '.php'; }

function abort($code, $msg = '') {
    http_response_code($code);
    render('error', ['code' => $code, 'msg' => $msg], null);
    exit;
}
function icon($name) { return '<svg class="ic"><use href="#i-' . e($name) . '"/></svg>'; }

function sel($cond) { return $cond ? ' selected' : ''; }
function chk($cond) { return $cond ? ' checked' : ''; }
