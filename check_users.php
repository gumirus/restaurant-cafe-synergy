<?php
header('Content-Type: text/plain; charset=utf-8');
require_once __DIR__ . '/backend/config/db.php';
$users = $pdo->query("SELECT id, phone, email, name, access_rights_id FROM users ORDER BY id")->fetchAll();
echo "Users:\n";
foreach ($users as $u) {
    echo "  {$u['id']}: phone={$u['phone']} email={$u['email']} name={$u['name']} rights={$u['access_rights_id']}\n";
}
