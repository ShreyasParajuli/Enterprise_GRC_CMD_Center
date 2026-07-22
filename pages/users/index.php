<?php
require_once __DIR__ . '/../../config/init.php';
requireLogin();

$pageTitle = 'User Management';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';

// Fetch users
$stmt = $pdo->query("
    SELECT u.id, u.username, u.email, u.status, u.created_at, r.name as role_name, d.name as dept_name 
    FROM users u 
    LEFT JOIN roles r ON u.role_id = r.id 
    LEFT JOIN departments d ON u.department_id = d.id 
    ORDER BY u.created_at DESC
");
$users = $stmt->fetchAll();
?>

<div class="flex-1 flex flex-col min-w-0 overflow-hidden">
    <?php require_once __DIR__ . '/../../includes/navbar.php'; ?>

    <main class="flex-1 overflow-y-auto p-6">
        <div class="max-w-7xl mx-auto space-y-6">
            
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-bold text-brand-dark">Users</h2>
                    <p class="text-brand-dark/70 mt-1">Manage system users, roles, and access.</p>
                </div>
                <a href="<?= BASE_URL ?>/pages/users/create.php" class="bg-brand-primary hover:opacity-90 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-lg flex items-center inline-flex">
                    <i data-lucide="plus" class="w-4 h-4 mr-2"></i> Add User
                </a>
            </div>

            <div class="bg-white rounded-xl border border-brand-dark/10 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-brand-dark">
                        <thead class="text-xs text-brand-dark/70 uppercase bg-slate-50/50 border-b border-brand-dark/10">
                            <tr>
                                <th scope="col" class="px-6 py-4 font-semibold tracking-wider">User</th>
                                <th scope="col" class="px-6 py-4 font-semibold tracking-wider">Role</th>
                                <th scope="col" class="px-6 py-4 font-semibold tracking-wider">Department</th>
                                <th scope="col" class="px-6 py-4 font-semibold tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-4 font-semibold tracking-wider text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700/50">
                            <?php foreach ($users as $index => $user): ?>
                                <tr class="hover:bg-brand-dark/5/30 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <div class="h-10 w-10 rounded-full bg-slate-100 flex items-center justify-center text-white font-bold border border-brand-dark/10">
                                                    <?= strtoupper(substr($user['username'], 0, 1)) ?>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="font-medium text-brand-dark"><?= htmlspecialchars($user['username']) ?></div>
                                                <div class="text-xs text-brand-dark/70"><?= htmlspecialchars($user['email']) ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-black/5 text-brand-dark/70 border border-brand-dark/10">
                                            <?= htmlspecialchars($user['role_name'] ?? 'None') ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-brand-dark/70">
                                        <?= htmlspecialchars($user['dept_name'] ?? 'None') ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php 
                                        $statusClass = 'bg-slate-900 text-brand-dark border-brand-dark/10';
                                        if ($user['status'] === 'active') $statusClass = 'bg-black/5 text-brand-dark/70 border-brand-dark/10';
                                        if ($user['status'] === 'inactive') $statusClass = 'bg-black/5 text-brand-dark/70 border-brand-dark/10';
                                        if ($user['status'] === 'locked') $statusClass = 'bg-black/5 text-brand-dark/70 border-brand-dark/10';
                                        ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border <?= $statusClass ?>">
                                            <?= ucfirst(htmlspecialchars($user['status'])) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="<?= BASE_URL ?>/pages/users/edit.php?id=<?= $user['id'] ?>" class="text-brand-primary hover:opacity-80 mr-3">Edit</a>
                                        <a href="<?= BASE_URL ?>/pages/users/delete.php?id=<?= $user['id'] ?>" class="text-brand-dark/70 hover:text-brand-dark/70" onclick="return confirm('Are you sure?')">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (count($users) === 0): ?>
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center text-brand-dark/70">
                                        No users found.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
