<?php
require_once __DIR__ . '/../../config/init.php';
requireLogin();

$pageTitle = 'Role Management';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';

// Fetch roles
$stmt = $pdo->query("SELECT * FROM roles ORDER BY id ASC");
$roles = $stmt->fetchAll();
?>

<div class="flex-1 flex flex-col min-w-0 overflow-hidden">
    <?php require_once __DIR__ . '/../../includes/navbar.php'; ?>

    <main class="flex-1 overflow-y-auto p-6">
        <div class="max-w-7xl mx-auto space-y-6">
            
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-bold text-brand-dark">Roles</h2>
                    <p class="text-brand-dark/70 mt-1">Manage RBAC roles and permissions.</p>
                </div>
                <a href="<?= BASE_URL ?>/pages/roles/create.php" class="bg-brand-primary hover:opacity-90 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-lg flex items-center inline-flex">
                    <i data-lucide="plus" class="w-4 h-4 mr-2"></i> Add Role
                </a>
            </div>

            <div class="bg-white rounded-xl border border-brand-dark/10 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-brand-dark">
                        <thead class="text-xs text-brand-dark/70 uppercase bg-slate-50/50 border-b border-brand-dark/10">
                            <tr>
                                <th scope="col" class="px-6 py-4 font-semibold tracking-wider">ID</th>
                                <th scope="col" class="px-6 py-4 font-semibold tracking-wider">Role Name</th>
                                <th scope="col" class="px-6 py-4 font-semibold tracking-wider">Created At</th>
                                <th scope="col" class="px-6 py-4 font-semibold tracking-wider text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700/50">
                            <?php foreach ($roles as $index => $role): ?>
                                <tr class="hover:bg-brand-dark/5/30 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">#<?= $index + 1 ?></td>
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-brand-dark"><?= htmlspecialchars($role['name']) ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-brand-dark/70">
                                        <?= htmlspecialchars($role['created_at']) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="<?= BASE_URL ?>/pages/roles/edit.php?id=<?= $role['id'] ?>" class="text-brand-primary hover:opacity-80 mr-3">Edit</a>
                                        <a href="<?= BASE_URL ?>/pages/roles/delete.php?id=<?= $role['id'] ?>" class="text-brand-dark/70 hover:text-brand-dark/70" onclick="return confirm('Are you sure you want to delete this role?')">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
