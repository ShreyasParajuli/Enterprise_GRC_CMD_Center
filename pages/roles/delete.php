<?php
require_once __DIR__ . '/../../config/init.php';
requireLogin();
requireRole(['Admin']);

$id = $_GET['id'] ?? null;
if ($id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM roles WHERE id = ?");
        $stmt->execute([$id]);
        logActivity($pdo, $_SESSION['user_id'], "Deleted role ID: $id");
    } catch (PDOException $e) {
        // Log or handle foreign key constraint errors silently to user for now
    }
}

header('Location: ' . BASE_URL . '/pages/roles/index.php');
exit;
