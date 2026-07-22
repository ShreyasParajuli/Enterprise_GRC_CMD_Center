<?php
require_once __DIR__ . '/config/init.php';

if (isLoggedIn()) {
    header('Location: ' . BASE_URL . '/pages/dashboard.php');
} else {
    header('Location: ' . BASE_URL . '/pages/login.php');
}
exit;
