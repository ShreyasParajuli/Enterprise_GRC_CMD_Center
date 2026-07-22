<?php
require_once __DIR__ . '/../../config/init.php';
requireLogin();
requireRole(['Admin']);

$id = $_GET['id'] ?? null;
if ($id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM departments WHERE id = ?");
        $stmt->execute([$id]);
        logActivity($pdo, $_SESSION['user_id'], "Deleted department ID: $id");
    } catch (PDOException $e) {
        // Log or handle foreign key constraint errors silently to user for now
    }
}

header('Location: ' . BASE_URL . '/pages/departments/index.php');
exit;
