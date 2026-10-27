<?php
require_once __DIR__ . '/../../config/init.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: " . BASE_URL . "/modules/assets/index.php");
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
if ($id) {
    try {
        // Fetch asset for logging
        $stmtCheck = $pdo->prepare("SELECT asset_id FROM assets WHERE id = ?");
        $stmtCheck->execute([$id]);
        $asset = $stmtCheck->fetch();

        if ($asset) {
            $stmt = $pdo->prepare("DELETE FROM assets WHERE id = ?");
            $stmt->execute([$id]);

            $_SESSION['success'] = "Asset deleted successfully.";
            logActivity($pdo, $_SESSION['user_id'], "User deleted asset " . $asset['asset_id']);
        } else {
            $_SESSION['error'] = "Asset not found.";
        }
    } catch (PDOException $e) {
        // If there's a foreign key constraint failure (e.g. 23000)
        if ($e->getCode() == 23000) {
            $_SESSION['error'] = "Cannot delete this asset because it is referenced by other records.";
        } else {
            $_SESSION['error'] = "Database error: " . $e->getMessage();
        }
    }
} else {
    $_SESSION['error'] = "Invalid request.";
}

header("Location: " . BASE_URL . "/modules/assets/index.php");
exit;
