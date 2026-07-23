<?php
require_once __DIR__ . '/config/init.php';

$pdo->exec("UPDATE users SET role_id = (SELECT id FROM roles WHERE name = 'Admin' LIMIT 1) WHERE username = 'admin'");
echo "Done.";
