<?php
require_once __DIR__ . '/../../config/init.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['risk_ids'])) {
    $_SESSION['error'] = "No risks selected for bulk action.";
    header('Location: ' . BASE_URL . '/modules/risks/index.php');
    exit;
}

$riskIds = $_POST['risk_ids'];
$bulkAction = $_POST['bulk_action'] ?? '';

if (empty($bulkAction)) {
    $_SESSION['error'] = "No bulk action specified.";
    header('Location: ' . BASE_URL . '/modules/risks/index.php');
    exit;
}

// Security: Make sure riskIds are integers
$safeIds = array_map('intval', $riskIds);
$placeholders = implode(',', array_fill(0, count($safeIds), '?'));

try {
    $pdo->beginTransaction();

    if ($bulkAction === 'delete') {
        $stmt = $pdo->prepare("DELETE FROM risks WHERE id IN ($placeholders)");
        $stmt->execute($safeIds);
        $count = count($safeIds);
        $_SESSION['success'] = "$count risk(s) successfully deleted.";
        logActivity($pdo, $_SESSION['user_id'], "[Risk Register] Bulk Deleted $count Risks");
    } 
    elseif ($bulkAction === 'close') {
        $stmt = $pdo->prepare("UPDATE risks SET status = 'Closed' WHERE id IN ($placeholders)");
        $stmt->execute($safeIds);
        $count = count($safeIds);
        $_SESSION['success'] = "$count risk(s) successfully closed.";
        logActivity($pdo, $_SESSION['user_id'], "[Risk Register] Bulk Closed $count Risks");
    } 
    elseif (strpos($bulkAction, 'set_status:') === 0) {
        $status = substr($bulkAction, 11); // Length of "set_status:"
        if (in_array($status, ['Open', 'In Progress', 'Closed'])) {
            $stmt = $pdo->prepare("UPDATE risks SET status = ? WHERE id IN ($placeholders)");
            $params = array_merge([$status], $safeIds);
            $stmt->execute($params);
            $count = count($safeIds);
            $_SESSION['success'] = "$count risk(s) status updated to " . htmlspecialchars($status) . ".";
            logActivity($pdo, $_SESSION['user_id'], "[Risk Register] Bulk Updated Status to $status for $count Risks");
        }
    }
    elseif (strpos($bulkAction, 'set_treatment:') === 0) {
        $treatment = substr($bulkAction, 14); // Length of "set_treatment:"
        if (in_array($treatment, ['Mitigate', 'Transfer', 'Avoid', 'Accept'])) {
            $stmt = $pdo->prepare("UPDATE risks SET treatment_strategy = ? WHERE id IN ($placeholders)");
            $params = array_merge([$treatment], $safeIds);
            $stmt->execute($params);
            $count = count($safeIds);
            $_SESSION['success'] = "$count risk(s) treatment updated to " . htmlspecialchars($treatment) . ".";
            logActivity($pdo, $_SESSION['user_id'], "[Risk Register] Bulk Updated Treatment to $treatment for $count Risks");
        }
    }
    else {
        $_SESSION['error'] = "Unknown bulk action.";
    }

    $pdo->commit();
} catch (PDOException $e) {
    $pdo->rollBack();
    $_SESSION['error'] = "Error applying bulk action: " . $e->getMessage();
}

header('Location: ' . BASE_URL . '/modules/risks/index.php');
exit;
