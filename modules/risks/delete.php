<?php
require_once __DIR__ . '/../../config/init.php';
requireLogin();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "Invalid risk ID.";
    header("Location: " . BASE_URL . "/modules/risks/index.php");
    exit;
}

$id = (int)$_GET['id'];

try {
    $stmt_r = $pdo->prepare("SELECT risk_id FROM risks WHERE id = ?");
    $stmt_r->execute([$id]);
    $risk = $stmt_r->fetch();
    $r_id = $risk ? $risk['risk_id'] : $id;

    $stmt = $pdo->prepare("DELETE FROM risks WHERE id = ?");
    $stmt->execute([$id]);
    $_SESSION['success'] = "Risk deleted successfully.";
    logActivity($pdo, $_SESSION['user_id'], "[Risk Register] Deleted Risk: " . $r_id);
} catch (PDOException $e) {
    $_SESSION['error'] = "Cannot delete risk. It may be referenced elsewhere.";
}

header("Location: " . BASE_URL . "/modules/risks/index.php");
exit;
