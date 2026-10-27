<?php
require_once __DIR__ . '/../../config/init.php';
require_once __DIR__ . '/helpers/asset_helper.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: " . BASE_URL . "/modules/assets/index.php");
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$isEdit = $id > 0;

$asset_name = trim($_POST['asset_name'] ?? '');
$description = trim($_POST['description'] ?? '');
$category_id = (int)($_POST['category_id'] ?? 0);
$asset_type = trim($_POST['asset_type'] ?? '');
$owner_id = (int)($_POST['owner_id'] ?? 0);
$department_id = (int)($_POST['department_id'] ?? 0);
$status = $_POST['status'] ?? '';
$confidentiality = (int)($_POST['confidentiality'] ?? 0);
$integrity = (int)($_POST['integrity'] ?? 0);
$availability = (int)($_POST['availability'] ?? 0);
$location = trim($_POST['location'] ?? '');
$purchase_date = empty($_POST['purchase_date']) ? null : $_POST['purchase_date'];
$warranty_expiry = empty($_POST['warranty_expiry']) ? null : $_POST['warranty_expiry'];

// Validate required fields (criticality is no longer manually validated as it's calculated)
if (empty($asset_name) || !$category_id || empty($asset_type) || !$owner_id || !$department_id || empty($status)) {
    $_SESSION['error'] = "Please complete all required fields.";
    if ($isEdit) {
        header("Location: " . BASE_URL . "/modules/assets/edit.php?id=" . $id);
    } else {
        header("Location: " . BASE_URL . "/modules/assets/create.php");
    }
    exit;
}

// Validate CIA limits (1 to 5)
if ($confidentiality < 1 || $confidentiality > 5 || $integrity < 1 || $integrity > 5 || $availability < 1 || $availability > 5) {
    $_SESSION['error'] = "CIA values must be between 1 and 5.";
    if ($isEdit) {
        header("Location: " . BASE_URL . "/modules/assets/edit.php?id=" . $id);
    } else {
        header("Location: " . BASE_URL . "/modules/assets/create.php");
    }
    exit;
}

// Phase 4: Server-side calculation is authoritative
$ciaScore = calculateCiaScore($confidentiality, $integrity, $availability);
$criticality = calculateAssetCriticality($ciaScore);

try {
    if ($isEdit) {
        // Fetch asset_id for logging
        $stmtCheck = $pdo->prepare("SELECT asset_id, criticality FROM assets WHERE id = ?");
        $stmtCheck->execute([$id]);
        $asset = $stmtCheck->fetch();
        
        if (!$asset) {
            $_SESSION['error'] = "Asset not found.";
            header("Location: " . BASE_URL . "/modules/assets/index.php");
            exit;
        }

        // Update existing
        $stmt = $pdo->prepare("
            UPDATE assets 
            SET asset_name = ?, description = ?, asset_type = ?, category_id = ?, owner_id = ?, department_id = ?, 
                criticality = ?, status = ?, confidentiality = ?, integrity = ?, availability = ?, 
                location = ?, purchase_date = ?, warranty_expiry = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $asset_name, $description, $asset_type, $category_id, $owner_id, $department_id,
            $criticality, $status, $confidentiality, $integrity, $availability,
            $location, $purchase_date, $warranty_expiry, $id
        ]);
        
        $_SESSION['success'] = "Asset updated successfully.";
        
        if ($asset['criticality'] !== $criticality) {
            logActivity($pdo, $_SESSION['user_id'], "Asset " . $asset['asset_id'] . " updated — Criticality changed from " . $asset['criticality'] . " to " . $criticality . ".");
        } else {
            logActivity($pdo, $_SESSION['user_id'], "User updated asset " . $asset['asset_id']);
        }
        
        header("Location: " . BASE_URL . "/modules/assets/view.php?id=" . $id);
        exit;
    } else {
        // Generate Asset ID (AST-0001)
        $stmt = $pdo->query("SELECT MAX(id) as max_id FROM assets");
        $row = $stmt->fetch();
        $nextId = ($row['max_id'] ?? 0) + 1;
        $asset_id = 'AST-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

        // Insert new
        $stmt = $pdo->prepare("
            INSERT INTO assets (asset_id, asset_name, description, asset_type, category_id, owner_id, department_id, 
                                criticality, status, confidentiality, integrity, availability, location, purchase_date, warranty_expiry)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $asset_id, $asset_name, $description, $asset_type, $category_id, $owner_id, $department_id,
            $criticality, $status, $confidentiality, $integrity, $availability, $location, $purchase_date, $warranty_expiry
        ]);
        
        $_SESSION['success'] = "Asset created successfully.";
        logActivity($pdo, $_SESSION['user_id'], "User created asset " . $asset_id . " — " . $asset_name . " (Calculated: $criticality)");
        
        header("Location: " . BASE_URL . "/modules/assets/index.php");
        exit;
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Database error: " . $e->getMessage();
    if ($isEdit) {
        header("Location: " . BASE_URL . "/modules/assets/edit.php?id=" . $id);
    } else {
        header("Location: " . BASE_URL . "/modules/assets/create.php");
    }
    exit;
}
