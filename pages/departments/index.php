<?php
require_once __DIR__ . '/../../config/init.php';
requireLogin();

$pageTitle = 'Department Management';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';

// Fetch departments
$stmt = $pdo->query("SELECT * FROM departments ORDER BY name ASC");
$departments = $stmt->fetchAll();
?>

<div class="flex-1 flex flex-col min-w-0 overflow-hidden">
    <?php require_once __DIR__ . '/../../includes/navbar.php'; ?>

    <main class="flex-1 overflow-y-auto p-6 bg-[#FFFAF3]">
        <div class="max-w-7xl mx-auto space-y-6">
            
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-bold text-brand-dark">Departments</h2>
                    <p class="text-brand-dark/70 mt-1">Manage organizational units.</p>
                </div>
                <a href="<?= BASE_URL ?>/pages/departments/create.php" class="bg-brand-primary hover:opacity-90 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-lg flex items-center inline-flex">
                    <i data-lucide="plus" class="w-4 h-4 mr-2"></i> Add Department
                </a>
            </div>

            <div class="bg-white rounded-xl border border-brand-dark/10 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-brand-dark">
                        <thead class="text-xs text-brand-dark/70 uppercase bg-slate-50/50 border-b border-brand-dark/10">
                            <tr>
                                <th scope="col" class="px-6 py-4 font-semibold tracking-wider">ID</th>
                                <th scope="col" class="px-6 py-4 font-semibold tracking-wider">Department Name</th>
                                <th scope="col" class="px-6 py-4 font-semibold tracking-wider">Created At</th>
                                <th scope="col" class="px-6 py-4 font-semibold tracking-wider text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700/50">
                            <?php foreach ($departments as $index => $dept): ?>
                                <tr class="hover:bg-brand-dark/5/30 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">#<?= $index + 1 ?></td>
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-brand-dark"><?= htmlspecialchars($dept['name']) ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-brand-dark/70">
                                        <?= htmlspecialchars($dept['created_at']) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="<?= BASE_URL ?>/pages/departments/edit.php?id=<?= $dept['id'] ?>" class="text-brand-primary hover:opacity-80 mr-3">Edit</a>
                                        <a href="<?= BASE_URL ?>/pages/departments/delete.php?id=<?= $dept['id'] ?>" class="text-brand-dark/70 hover:text-brand-dark/70" onclick="event.preventDefault(); let url=this.href; confirmAction('Are you sure you want to delete this department?', () => window.location.href=url)">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($departments)): ?>
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center text-brand-dark/50 bg-white">
                                        <i data-lucide="building-2" class="w-12 h-12 mx-auto mb-3 text-brand-dark/20"></i>
                                        <p class="text-base font-semibold">No departments found.</p>
                                        <p class="text-sm mt-1">Get started by creating a new department.</p>
                                        <a href="<?= BASE_URL ?>/pages/departments/create.php" class="inline-flex items-center mt-4 bg-brand-primary text-white px-4 py-2 rounded-lg text-sm font-medium hover:opacity-90 transition-opacity">
                                            <i data-lucide="plus" class="w-4 h-4 mr-2"></i> Add First Department
                                        </a>
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
