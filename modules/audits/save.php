<?php
require_once __DIR__ . '/../../config/init.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: " . BASE_URL . "/modules/audits/index.php");
    exit;
}

$action = $_POST['action'] ?? 'create';
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$isEdit = ($action === 'update' && $id > 0);

$audit_id = trim($_POST['audit_id'] ?? '');
$title = trim($_POST['title'] ?? '');
$audit_type = trim($_POST['audit_type'] ?? 'Internal');
$framework = trim($_POST['framework'] ?? '');
$department_id = (int)($_POST['department_id'] ?? 0);
$lead_auditor_id = (int)($_POST['lead_auditor_id'] ?? 0);
$start_date = empty($_POST['start_date']) ? null : $_POST['start_date'];
$end_date = empty($_POST['end_date']) ? null : $_POST['end_date'];
$status = trim($_POST['status'] ?? 'Planned');
$report_rating = trim($_POST['report_rating'] ?? 'Pending Rating');
$scope = trim($_POST['scope'] ?? '');
$description = trim($_POST['description'] ?? '');

// Validation
if (empty($audit_id) || empty($title) || empty($framework) || !$department_id || !$lead_auditor_id) {
    $_SESSION['error'] = "Please fill in all required fields (Audit ID, Title, Framework, Department, Lead Auditor).";
    if ($isEdit) {
        header("Location: " . BASE_URL . "/modules/audits/edit.php?id=" . $id);
    } else {
        header("Location: " . BASE_URL . "/modules/audits/create.php");
    }
    exit;
}

try {
    if ($isEdit) {
        // Check duplicate audit_id excluding current
        $stmtCheck = $pdo->prepare("SELECT id FROM audits WHERE audit_id = ? AND id != ?");
        $stmtCheck->execute([$audit_id, $id]);
        if ($stmtCheck->fetch()) {
            $_SESSION['error'] = "Audit identifier {$audit_id} is already in use by another engagement.";
            header("Location: " . BASE_URL . "/modules/audits/edit.php?id=" . $id);
            exit;
        }

        $stmt = $pdo->prepare("
            UPDATE audits 
            SET audit_id = ?, title = ?, audit_type = ?, framework = ?, department_id = ?, 
                lead_auditor_id = ?, start_date = ?, end_date = ?, status = ?, report_rating = ?, 
                scope = ?, description = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $audit_id, $title, $audit_type, $framework, $department_id,
            $lead_auditor_id, $start_date, $end_date, $status, $report_rating,
            $scope, $description, $id
        ]);

        logActivity($pdo, $_SESSION['user_id'], "Updated audit engagement {$audit_id} ({$title})");
        $_SESSION['success'] = "Audit engagement {$audit_id} updated successfully.";
        header("Location: " . BASE_URL . "/modules/audits/view.php?id=" . $id);
        exit;
    } else {
        // Create new audit
        $stmtCheck = $pdo->prepare("SELECT id FROM audits WHERE audit_id = ?");
        $stmtCheck->execute([$audit_id]);
        if ($stmtCheck->fetch()) {
            $_SESSION['error'] = "Audit identifier {$audit_id} already exists.";
            header("Location: " . BASE_URL . "/modules/audits/create.php");
            exit;
        }

        $stmt = $pdo->prepare("
            INSERT INTO audits (audit_id, title, audit_type, framework, department_id, lead_auditor_id, start_date, end_date, status, report_rating, scope, description)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $audit_id, $title, $audit_type, $framework, $department_id,
            $lead_auditor_id, $start_date, $end_date, $status, $report_rating,
            $scope, $description
        ]);

        $newId = $pdo->lastInsertId();
        logActivity($pdo, $_SESSION['user_id'], "Created new audit engagement {$audit_id} ({$title})");
        $_SESSION['success'] = "Audit engagement {$audit_id} scheduled successfully.";
        header("Location: " . BASE_URL . "/modules/audits/view.php?id=" . $newId);
        exit;
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Database error: " . $e->getMessage();
    header("Location: " . BASE_URL . "/modules/audits/index.php");
    exit;
}
