<?php
require_once __DIR__ . '/../../config/init.php';
requireLogin();
requireRole(['Admin']);

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: ' . BASE_URL . '/pages/departments/index.php');
    exit;
}

$error = '';
$success = '';

// Fetch department
$stmt = $pdo->prepare("SELECT * FROM departments WHERE id = ?");
$stmt->execute([$id]);
$department = $stmt->fetch();

if (!$department) {
    header('Location: ' . BASE_URL . '/pages/departments/index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');

    if (empty($name)) {
        $error = "Department name is required.";
    } else {
        try {
            $updateStmt = $pdo->prepare("UPDATE departments SET name = ? WHERE id = ?");
            $updateStmt->execute([$name, $id]);
            
            logActivity($pdo, $_SESSION['user_id'], "Updated department: " . $name);
            $_SESSION["success_msg"] = "Updated successfully."; header("Location: " . BASE_URL . "/pages/departments/index.php"); exit;
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $error = "Department name already exists.";
            } else {
                $error = "An error occurred: " . $e->getMessage();
            }
        }
    }
}

$pageTitle = 'Edit Department';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>

<div class="flex-1 flex flex-col min-w-0 overflow-hidden">
    <?php require_once __DIR__ . '/../../includes/navbar.php'; ?>

    <main class="flex-1 overflow-y-auto p-6">
        <div class="max-w-3xl mx-auto space-y-6">
            
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-bold text-brand-dark">Edit Department: <?= htmlspecialchars($department['name']) ?></h2>
                </div>
                <a href="<?= BASE_URL ?>/pages/departments/index.php" class="text-brand-dark/70 hover:text-brand-dark transition-colors flex items-center">
                    <i data-lucide="arrow-left" mr-2"></i> Back to Departments
                </a>
            </div>

            <?php if ($error): ?>
                <div class="bg-black/5 border border-brand-dark/10 text-brand-dark/70 px-4 py-3 rounded text-sm">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="bg-black/5 border border-brand-dark/10 text-brand-dark/70 px-4 py-3 rounded text-sm">
                    <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>

            <div class="bg-white rounded-xl border border-brand-dark/10 shadow-sm overflow-hidden">
                <form method="POST" action="" class="p-6 space-y-6">
                    <div class="grid grid-cols-1 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-brand-dark mb-2">Department Name</label>
                            <input type="text" name="name" required value="<?= htmlspecialchars($department['name']) ?>"
                                   class="bg-slate-50 border border-brand-dark/10 text-brand-dark rounded-lg focus:ring-grc-primary focus:border-grc-primary block w-full p-2.5">
                        </div>
                    </div>
                    
                    <div class="flex justify-end pt-4 border-t border-brand-dark/10">
                        <button type="submit" class="bg-brand-primary hover:opacity-90 text-white px-5 py-2.5 rounded-lg text-sm font-medium transition-colors">
                            Update Department
                        </button>
                    </div>
                </form>
            </div>
            
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
