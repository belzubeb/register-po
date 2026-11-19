<?php
// Pengarah: buka /register-po/ langsung masuk ke aplikasi (folder public).
$base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
header('Location: ' . $base . '/public/index.php');
exit;
