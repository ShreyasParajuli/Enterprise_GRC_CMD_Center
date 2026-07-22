<?php
require_once __DIR__ . '/../../config/init.php';
requireLogin();
requireRole(['Admin']);

$id = $_GET['id'] ?? null;
if ($id && $id != $_SESSION['user_id']) {
    try {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
        logActivity($pdo, $_SESSION['user_id'], "Deleted user ID: $id");
    } catch (PDOException $e) {
        // Handle error (could be foreign key constraint, though we use SET NULL)
    }
}

header('Location: ' . BASE_URL . '/pages/users/index.php');
exit;
