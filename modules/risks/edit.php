<?php
require_once __DIR__ . '/../../config/init.php';
requireLogin();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "Invalid risk ID.";
    header("Location: " . BASE_URL . "/modules/risks/index.php");
    exit;
}

$id = (int)$_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM risks WHERE id = ?");
$stmt->execute([$id]);
$riskData = $stmt->fetch();

if (!$riskData) {
    $_SESSION['error'] = "Risk not found.";
    header("Location: " . BASE_URL . "/modules/risks/index.php");
    exit;
}

$pageTitle = 'Edit Risk';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';

// Fetch lookup data
$categories = $pdo->query("SELECT id, category_name FROM risk_categories ORDER BY category_name")->fetchAll();
$departments = $pdo->query("SELECT id, name FROM departments ORDER BY name")->fetchAll();
$users = $pdo->query("SELECT id, username FROM users WHERE status = 'active' ORDER BY username")->fetchAll();
?>

<div class="flex-1 flex flex-col min-w-0 overflow-hidden">
    <?php require_once __DIR__ . '/../../includes/navbar.php'; ?>

    <main class="flex-1 overflow-y-auto p-6 bg-[#FFFAF3]">
        <div class="max-w-4xl mx-auto space-y-6">
            <!-- Breadcrumb -->
            <nav class="text-sm font-medium text-brand-dark/50" aria-label="Breadcrumb">
                <ol class="list-none p-0 inline-flex">
                    <li class="flex items-center">
                        <a href="<?= BASE_URL ?>/pages/dashboard.php" class="hover:text-brand-primary">Dashboard</a>
                        <i data-lucide="chevron-right" class="w-4 h-4 mx-2"></i>
                    </li>
                    <li class="flex items-center">
                        <a href="<?= BASE_URL ?>/modules/risks/index.php" class="hover:text-brand-primary">Risk Register</a>
                        <i data-lucide="chevron-right" class="w-4 h-4 mx-2"></i>
                    </li>
                    <li class="flex items-center text-[#232426]">
                        Edit Risk
                    </li>
                </ol>
            </nav>

            <div>
                <h2 class="text-2xl font-bold text-[#232426]">Edit Risk: <?= htmlspecialchars($riskData['risk_id']) ?></h2>
                <p class="text-[#232426]/70 mt-1">Update risk assessment and details.</p>
            </div>



            <!-- Risk Form Component -->
            <?php 
                $isEdit = true;
                require_once __DIR__ . '/components/risk_form.php'; 
            ?>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
