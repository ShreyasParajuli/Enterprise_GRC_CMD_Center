<?php
require_once __DIR__ . '/../../config/init.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: " . BASE_URL . "/modules/risks/index.php");
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$isEdit = $id > 0;

$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$category_id = (int)($_POST['category_id'] ?? 0);
$department_id = (int)($_POST['department_id'] ?? 0);
$owner_id = (int)($_POST['owner_id'] ?? 0);
$treatment_strategy = $_POST['treatment_strategy'] ?? '';
$likelihood = (int)($_POST['likelihood'] ?? 0);
$impact = (int)($_POST['impact'] ?? 0);
$status = $_POST['status'] ?? '';
$review_date = empty($_POST['review_date']) ? null : $_POST['review_date'];

// Basic validation
if (empty($title) || !$category_id || !$department_id || !$owner_id || !$treatment_strategy || !$likelihood || !$impact || !$status) {
    $_SESSION['error'] = "Please fill in all required fields.";
    if ($isEdit) {
        header("Location: " . BASE_URL . "/modules/risks/edit.php?id=" . $id);
    } else {
        header("Location: " . BASE_URL . "/modules/risks/create.php");
    }
    exit;
}

try {
    if ($isEdit) {
        // Update existing
        $stmt = $pdo->prepare("
            UPDATE risks 
            SET title = ?, description = ?, category_id = ?, department_id = ?, owner_id = ?, 
                treatment_strategy = ?, likelihood = ?, impact = ?, status = ?, review_date = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $title, $description, $category_id, $department_id, $owner_id, 
            $treatment_strategy, $likelihood, $impact, $status, $review_date, $id
        ]);
        $_SESSION['success'] = "Risk updated successfully.";
        
        // Fetch risk_id for logging
        $stmt2 = $pdo->prepare("SELECT risk_id FROM risks WHERE id = ?");
        $stmt2->execute([$id]);
        $r = $stmt2->fetch();
        if ($r) logActivity($pdo, $_SESSION['user_id'], "[Risk Register] Updated Risk: " . $r['risk_id']);
    } else {
        // Generate Risk ID (e.g. RSK-0001)
        $stmt = $pdo->query("SELECT MAX(id) as max_id FROM risks");
        $row = $stmt->fetch();
        $nextId = ($row['max_id'] ?? 0) + 1;
        $risk_id = 'RSK-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

        // Insert new
        $stmt = $pdo->prepare("
            INSERT INTO risks (risk_id, title, description, category_id, department_id, owner_id, treatment_strategy, likelihood, impact, status, review_date)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $risk_id, $title, $description, $category_id, $department_id, $owner_id, 
            $treatment_strategy, $likelihood, $impact, $status, $review_date
        ]);
        $_SESSION['success'] = "Risk created successfully.";
        logActivity($pdo, $_SESSION['user_id'], "[Risk Register] Created Risk: " . $risk_id);
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Database error: " . $e->getMessage();
    if ($isEdit) {
        header("Location: " . BASE_URL . "/modules/risks/edit.php?id=" . $id);
    } else {
        header("Location: " . BASE_URL . "/modules/risks/create.php");
    }
    exit;
}

header("Location: " . BASE_URL . "/modules/risks/index.php");
exit;
