<?php
require_once 'app/bootstrap.php';

$users = DB::all('SELECT id, name, email, role FROM users');
echo "=== DAFTAR USER ===\n";
foreach ($users as $u) {
    echo "ID: {$u['id']} | Nama: {$u['name']} | Email: {$u['email']} | Role: {$u['role']}\n";
}
