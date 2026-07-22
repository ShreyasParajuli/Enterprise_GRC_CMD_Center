<?php
require_once __DIR__ . '/../../config/init.php';
requireLogin();
requireRole(['Admin']); // Only Admin can create users

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $role_id = $_POST['role_id'] ?? null;
    $department_id = $_POST['department_id'] ?? null;
    $status = $_POST['status'] ?? 'active';

    if (empty($username) || empty($email) || empty($password)) {
        $error = "Username, email, and password are required.";
    } else {
        try {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, role_id, department_id, status) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$username, $email, $hash, $role_id ?: null, $department_id ?: null, $status]);
            
            logActivity($pdo, $_SESSION['user_id'], "Created user: $username");
            
            $success = "User created successfully.";
            // Reset fields
            $_POST = [];
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $error = "Username or email already exists.";
            } else {
                $error = "An error occurred: " . $e->getMessage();
            }
        }
    }
}

// Fetch roles and departments for dropdowns
$roles = $pdo->query("SELECT id, name FROM roles")->fetchAll();
$departments = $pdo->query("SELECT id, name FROM departments")->fetchAll();

$pageTitle = 'Create User';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>

<div class="flex-1 flex flex-col min-w-0 overflow-hidden">
    <?php require_once __DIR__ . '/../../includes/navbar.php'; ?>

    <main class="flex-1 overflow-y-auto p-6">
        <div class="max-w-3xl mx-auto space-y-6">
            
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-bold text-brand-dark">Create New User</h2>
                    <p class="text-brand-dark/70 mt-1">Add a new user to the command center.</p>
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
                            <label class="block text-sm font-medium text-brand-dark mb-2">Username</label>
                            <input type="text" name="username" required value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                                   class="bg-slate-50 border border-brand-dark/10 text-brand-dark rounded-lg focus:ring-grc-primary focus:border-grc-primary block w-full p-2.5">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-brand-dark mb-2">Email Address</label>
                            <input type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                                   class="bg-slate-50 border border-brand-dark/10 text-brand-dark rounded-lg focus:ring-grc-primary focus:border-grc-primary block w-full p-2.5">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-brand-dark mb-2">Password</label>
                            <input type="password" name="password" required
                                   class="bg-slate-50 border border-brand-dark/10 text-brand-dark rounded-lg focus:ring-grc-primary focus:border-grc-primary block w-full p-2.5">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-brand-dark mb-2">Status</label>
                            <select name="status" class="bg-slate-50 border border-brand-dark/10 text-brand-dark rounded-lg focus:ring-grc-primary focus:border-grc-primary block w-full p-2.5">
                                <option value="active" <?= ($_POST['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                                <option value="inactive" <?= ($_POST['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                <option value="locked" <?= ($_POST['status'] ?? '') === 'locked' ? 'selected' : '' ?>>Locked</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-brand-dark mb-2">Role</label>
                            <select name="role_id" class="bg-slate-50 border border-brand-dark/10 text-brand-dark rounded-lg focus:ring-grc-primary focus:border-grc-primary block w-full p-2.5">
                                <option value="">None</option>
                                <?php foreach ($roles as $role): ?>
                                    <option value="<?= $role['id'] ?>" <?= ($_POST['role_id'] ?? '') == $role['id'] ? 'selected' : '' ?>><?= htmlspecialchars($role['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-brand-dark mb-2">Department</label>
                            <select name="department_id" class="bg-slate-50 border border-brand-dark/10 text-brand-dark rounded-lg focus:ring-grc-primary focus:border-grc-primary block w-full p-2.5">
                                <option value="">None</option>
                                <?php foreach ($departments as $dept): ?>
                                    <option value="<?= $dept['id'] ?>" <?= ($_POST['department_id'] ?? '') == $dept['id'] ? 'selected' : '' ?>><?= htmlspecialchars($dept['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="flex justify-end pt-4 border-t border-brand-dark/10">
                        <button type="submit" class="bg-brand-primary hover:opacity-90 text-white px-5 py-2.5 rounded-lg text-sm font-medium transition-colors">
                            Create User
                        </button>
                    </div>
                </form>
            </div>
            
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
