<?php
// Initialize session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Define base URL for absolute linking
// Change this depending on deployment environment
define('BASE_URL', '/Enterprise_GRC_CMD_Center');
define('APP_NAME', 'Enterprise GRC Command Center');

// Set default timezone
date_default_timezone_set('UTC');

// Error reporting (disable in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Require database connection
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/../includes/auth.php';
?>
