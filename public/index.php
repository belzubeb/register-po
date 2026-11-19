<?php
require __DIR__ . '/../app/bootstrap.php';

// route:METODE => [fungsi, publik?]
$routes = [
    'login:GET'            => ['auth_form', true],
    'login:POST'           => ['auth_login', true],
    'logout:POST'          => ['auth_logout', false],
    'dashboard:GET'        => ['dashboard_index', false],
    'documents:GET'        => ['documents_index', false],
    'documents/create:GET' => ['documents_create', false],
    'documents/store:POST' => ['documents_store', false],
    'documents/show:GET'   => ['documents_show', false],
    'documents/edit:GET'   => ['documents_edit', false],
    'documents/update:POST'  => ['documents_update', false],
    'documents/delete:POST'  => ['documents_delete', false],
    'documents/submit:POST'  => ['documents_submit', false],
    'documents/approve:POST' => ['documents_approve', false],
    'documents/reject:POST'  => ['documents_reject', false],
    'documents/korea:POST'   => ['documents_korea', false],
    'documents/revise:POST'  => ['documents_revise', false],
    'documents/download:GET' => ['documents_download', false],
    'panduan:GET'          => ['panduan_index', false],
    'activity:GET'         => ['activity_index', false],
    'users:GET'            => ['users_index', false],
    'users/store:POST'     => ['users_store', false],
];

$route = isset($_GET['r']) ? (string) $_GET['r'] : 'dashboard';
$method = $_SERVER['REQUEST_METHOD'];
$key = $route . ':' . $method;

if (!isset($routes[$key])) { abort(404, 'Halaman tidak ditemukan.'); }
list($handler, $public) = $routes[$key];

if (!$public && !Auth::check()) { redirect('login'); }
if ($method === 'POST') { verify_csrf(); }

$handler();