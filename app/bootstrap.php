<?php
define('ROOT', dirname(__DIR__));
define('VIEWS', ROOT . '/views');

if (!is_file(ROOT . '/app/config.php')) {
    http_response_code(500);
    exit('File app/config.php belum ada. Salin app/config.sample.php menjadi app/config.php lalu isi.');
}
$config = require ROOT . '/app/config.php';
date_default_timezone_set($config['timezone']);
error_reporting(E_ALL);
ini_set('display_errors', $config['debug'] ? '1' : '0');

if (is_file(ROOT . '/vendor/autoload.php')) {
    require ROOT . '/vendor/autoload.php';
}

require ROOT . '/app/helpers.php';

session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
session_start();
$GLOBALS['__old'] = isset($_SESSION['old']) ? $_SESSION['old'] : [];
unset($_SESSION['old']);

require ROOT . '/app/DB.php';
require ROOT . '/app/Auth.php';
require ROOT . '/app/Policy.php';
require ROOT . '/app/Sanitizer.php';
require ROOT . '/app/Document.php';
require ROOT . '/app/NumberGenerator.php';
require ROOT . '/app/Workflow.php';
require ROOT . '/app/controllers.php';

set_exception_handler(function ($e) {
    http_response_code(500);
    if (!empty($GLOBALS['config']['debug'])) {
        echo '<pre style="white-space:pre-wrap">' . e((string) $e) . '</pre>';
    } else {
        error_log((string) $e);
        echo 'Terjadi kesalahan pada server.';
    }
});
