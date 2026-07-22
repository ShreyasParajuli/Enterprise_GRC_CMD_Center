<?php
require_once __DIR__ . '/../../config/init.php';
requireLogin();
requireRole(['Admin']);

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: ' . BASE_URL . '/pages/users/index.php');
    exit;
}

$error = '';
$success = '';

// Fetch user
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    header('Location: ' . BASE_URL . '/pages/users/index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $role_id = $_POST['role_id'] ?? null;
    $department_id = $_POST['department_id'] ?? null;
    $status = $_POST['status'] ?? 'active';

    if (empty($email)) {
        $error = "Email is required.";
    } else {
        try {
            $updateStmt = $pdo->prepare("UPDATE users SET email = ?, role_id = ?, department_id = ?, status = ? WHERE id = ?");
            $updateStmt->execute([$email, $role_id ?: null, $department_id ?: null, $status, $id]);
            
            logActivity($pdo, $_SESSION['user_id'], "Updated user: " . $user['username']);
            $success = "User updated successfully.";
            
            // Refresh user data
            $stmt->execute([$id]);
            $user = $stmt->fetch();
        } catch (PDOException $e) {
            $error = "An error occurred: " . $e->getMessage();
        }
    }
}

$roles = $pdo->query("SELECT id, name FROM roles")->fetchAll();
$departments = $pdo->query("SELECT id, name FROM departments")->fetchAll();

$pageTitle = 'Edit User';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>

<div class="flex-1 flex flex-col min-w-0 overflow-hidden">
    <?php require_once __DIR__ . '/../../includes/navbar.php'; ?>

    <main class="flex-1 overflow-y-auto p-6">
        <div class="max-w-3xl mx-auto space-y-6">
            
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-bold text-brand-dark">Edit User: <?= htmlspecialchars($user['username']) ?></h2>
                </div>
                <a href="<?= BASE_URL ?>/pages/users/index.php" class="text-brand-dark/70 hover:text-brand-dark transition-colors flex items-center">
                    <i data-lucide="arrow-left" mr-2"></i> Back to Users
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
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-brand-dark mb-2">Username (Cannot change)</label>
                            <input type="text" disabled value="<?= htmlspecialchars($user['username']) ?>"
                                   class="bg-slate-100 border border-brand-dark/10 text-brand-dark/70 rounded-lg block w-full p-2.5 cursor-not-allowed">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-brand-dark mb-2">Email Address</label>
                            <input type="email" name="email" required value="<?= htmlspecialchars($user['email']) ?>"
                                   class="bg-slate-50 border border-brand-dark/10 text-brand-dark rounded-lg focus:ring-grc-primary focus:border-grc-primary block w-full p-2.5">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-brand-dark mb-2">Status</label>
                            <select name="status" class="bg-slate-50 border border-brand-dark/10 text-brand-dark rounded-lg focus:ring-grc-primary focus:border-grc-primary block w-full p-2.5">
                                <option value="active" <?= $user['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                                <option value="inactive" <?= $user['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                <option value="locked" <?= $user['status'] === 'locked' ? 'selected' : '' ?>>Locked</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-brand-dark mb-2">Role</label>
                            <select name="role_id" class="bg-slate-50 border border-brand-dark/10 text-brand-dark rounded-lg focus:ring-grc-primary focus:border-grc-primary block w-full p-2.5">
                                <option value="">None</option>
                                <?php foreach ($roles as $role): ?>
                                    <option value="<?= $role['id'] ?>" <?= $user['role_id'] == $role['id'] ? 'selected' : '' ?>><?= htmlspecialchars($role['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-brand-dark mb-2">Department</label>
                            <select name="department_id" class="bg-slate-50 border border-brand-dark/10 text-brand-dark rounded-lg focus:ring-grc-primary focus:border-grc-primary block w-full p-2.5">
                                <option value="">None</option>
                                <?php foreach ($departments as $dept): ?>
                                    <option value="<?= $dept['id'] ?>" <?= $user['department_id'] == $dept['id'] ? 'selected' : '' ?>><?= htmlspecialchars($dept['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="flex justify-end pt-4 border-t border-brand-dark/10">
                        <button type="submit" class="bg-brand-primary hover:opacity-90 text-white px-5 py-2.5 rounded-lg text-sm font-medium transition-colors">
                            Update User
                        </button>
                    </div>
                </form>
            </div>
            
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
