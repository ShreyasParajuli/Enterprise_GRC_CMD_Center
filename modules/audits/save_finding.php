<?php
require_once __DIR__ . '/../../config/init.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: " . BASE_URL . "/modules/audits/index.php");
    exit;
}

$audit_id = (int)($_POST['audit_id'] ?? 0);
$title = trim($_POST['title'] ?? '');
$severity = trim($_POST['severity'] ?? 'Medium');
$status = trim($_POST['status'] ?? 'Open');
$description = trim($_POST['description'] ?? '');
$remediation_plan = trim($_POST['remediation_plan'] ?? '');
$assigned_to = !empty($_POST['assigned_to']) ? (int)$_POST['assigned_to'] : null;
$due_date = !empty($_POST['due_date']) ? $_POST['due_date'] : null;
$risk_id = !empty($_POST['risk_id']) ? (int)$_POST['risk_id'] : null;
$asset_id = !empty($_POST['asset_id']) ? (int)$_POST['asset_id'] : null;

if (!$audit_id || empty($title)) {
    $_SESSION['error'] = "Finding title and valid audit reference are required.";
    header("Location: " . BASE_URL . "/modules/audits/view.php?id=" . $audit_id);
    exit;
}

try {
    // Auto-generate Finding ID
    $lastFindingId = $pdo->query("SELECT id FROM audit_findings ORDER BY id DESC LIMIT 1")->fetchColumn();
    $nextNum = str_pad(($lastFindingId ?: 0) + 1, 3, '0', STR_PAD_LEFT);
    $finding_id = "FND-" . date('Y') . "-" . $nextNum;

    $stmt = $pdo->prepare("
        INSERT INTO audit_findings (finding_id, audit_id, title, description, severity, risk_id, asset_id, status, remediation_plan, due_date, assigned_to)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $finding_id, $audit_id, $title, $description, $severity,
        $risk_id, $asset_id, $status, $remediation_plan, $due_date, $assigned_to
    ]);

    logActivity($pdo, $_SESSION['user_id'], "Logged audit finding {$finding_id} ({$title}) with {$severity} severity");
    $_SESSION['success'] = "Audit finding {$finding_id} logged successfully.";
    header("Location: " . BASE_URL . "/modules/audits/view.php?id=" . $audit_id);
    exit;
} catch (PDOException $e) {
    $_SESSION['error'] = "Database error: " . $e->getMessage();
    header("Location: " . BASE_URL . "/modules/audits/view.php?id=" . $audit_id);
    exit;
}
