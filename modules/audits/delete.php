<?php
require_once __DIR__ . '/../../config/init.php';
requireLogin();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "Invalid audit engagement ID.";
    header("Location: " . BASE_URL . "/modules/audits/index.php");
    exit;
}

$id = (int)$_GET['id'];

try {
    // Fetch audit_id for activity log
    $stmt = $pdo->prepare("SELECT audit_id, title FROM audits WHERE id = ?");
    $stmt->execute([$id]);
    $audit = $stmt->fetch();

    if (!$audit) {
        $_SESSION['error'] = "Audit engagement not found.";
        header("Location: " . BASE_URL . "/modules/audits/index.php");
        exit;
    }

    // Delete audit (Cascades to audit_findings and audit_checklists via ON DELETE CASCADE)
    $del = $pdo->prepare("DELETE FROM audits WHERE id = ?");
    $del->execute([$id]);

    logActivity($pdo, $_SESSION['user_id'], "Deleted audit engagement {$audit['audit_id']} ({$audit['title']})");
    $_SESSION['success'] = "Audit engagement {$audit['audit_id']} deleted successfully.";
    header("Location: " . BASE_URL . "/modules/audits/index.php");
    exit;
} catch (PDOException $e) {
    $_SESSION['error'] = "Failed to delete audit: " . $e->getMessage();
    header("Location: " . BASE_URL . "/modules/audits/index.php");
    exit;
}
