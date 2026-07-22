<?php
require_once __DIR__ . '/../config/init.php';

// Log out and destroy session
logoutUser($pdo);

// Redirect to login page
header('Location: ' . BASE_URL . '/pages/login.php');
exit;
