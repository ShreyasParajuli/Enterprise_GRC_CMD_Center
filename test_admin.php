<?php
require_once __DIR__ . '/config/init.php';

$stmt = $pdo->query("SELECT u.id, u.username, u.role_id, r.name as role_name FROM users u LEFT JOIN roles r ON u.role_id = r.id WHERE u.username = 'admin'");
$user = $stmt->fetch();
print_r($user);

echo "\nSession info:\n";
session_start();
print_r($_SESSION);
