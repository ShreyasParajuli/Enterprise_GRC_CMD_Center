<?php
require_once __DIR__ . '/../../config/init.php';
requireLogin();

$pageTitle = 'Enterprise Asset Inventory';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
require_once __DIR__ . '/helpers/asset_helper.php';

// Fetch assets
$stmt = $pdo->query("
    SELECT a.*, c.category_name, u.username as owner_name, d.name as department_name 
    FROM assets a
    LEFT JOIN asset_categories c ON a.category_id = c.id
    LEFT JOIN users u ON a.owner_id = u.id
    LEFT JOIN departments d ON a.department_id = d.id
    ORDER BY a.id DESC
");
$assets = $stmt->fetchAll();

// Breadcrumbs and Header Actions
$breadcrumbs = [
    'Asset Register' => ''
];
$primaryAction = ['label' => 'Add Asset', 'url' => BASE_URL . '/modules/assets/create.php', 'icon' => 'plus'];
$secondaryAction = ['label' => 'Manage Categories', 'url' => BASE_URL . '/modules/assets/categories.php', 'icon' => 'tags'];
?>

<div class="flex-1 flex flex-col min-w-0 overflow-hidden">
    <?php require_once __DIR__ . '/../../includes/navbar.php'; ?>

    <main class="flex-1 overflow-y-auto p-6 bg-[#FFFAF3]">
        <div class="max-w-7xl mx-auto space-y-6">
            
            <?php 
            $pageSubtitle = "Centralized inventory of organizational technology and business assets.";
            require_once __DIR__ . '/components/asset_header.php'; 
            ?>

            <!-- Summary Cards Placeholder -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <?php
                $cardTitle = "Total Assets"; $cardValue = "0"; $cardColor = "brand"; require __DIR__ . '/components/asset_card.php';
                $cardTitle = "Critical Assets"; $cardValue = "0"; $cardColor = "red"; require __DIR__ . '/components/asset_card.php';
                $cardTitle = "Active Assets"; $cardValue = "0"; $cardColor = "green"; require __DIR__ . '/components/asset_card.php';
                $cardTitle = "Under Maintenance"; $cardValue = "0"; $cardColor = "yellow"; require __DIR__ . '/components/asset_card.php';
                ?>
            </div>

            <!-- Asset Table Shell -->
            <div class="bg-white rounded-xl border border-[#232426]/10 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-[#232426]/10">
                        <thead class="bg-slate-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-[#232426]/50 uppercase tracking-wider">Asset ID</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-[#232426]/50 uppercase tracking-wider">Asset Name</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-[#232426]/50 uppercase tracking-wider">Category</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-[#232426]/50 uppercase tracking-wider">Owner</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-[#232426]/50 uppercase tracking-wider">Department</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-[#232426]/50 uppercase tracking-wider">Criticality</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-[#232426]/50 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-[#232426]/50 uppercase tracking-wider">Last Updated</th>
                                <th scope="col" class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-[#232426]/10">
                            <?php if (empty($assets)): ?>
                                <tr>
                                    <td colspan="9" class="p-0">
                                        <?php 
                                        $emptyMessage = "No assets to display yet.";
                                        $emptySubMessage = "Create your first enterprise asset to begin building the inventory.";
                                        require __DIR__ . '/components/asset_empty_state.php'; 
                                        ?>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($assets as $asset): ?>
                                    <tr class="hover:bg-slate-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-[#232426]">
                                            <a href="<?= BASE_URL ?>/modules/assets/view.php?id=<?= $asset['id'] ?>" class="hover:text-[#EF6351]"><?= htmlspecialchars($asset['asset_id']) ?></a>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-[#232426]">
                                            <?= htmlspecialchars($asset['asset_name']) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-[#232426]/70">
                                            <?= htmlspecialchars($asset['category_name'] ?? 'Unknown') ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-[#232426]/70">
                                            <?= htmlspecialchars($asset['owner_name'] ?? 'Unknown') ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-[#232426]/70">
                                            <?= htmlspecialchars($asset['department_name'] ?? 'Unknown') ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <?php 
                                            $badgeType = 'criticality'; $badgeValue = $asset['criticality'];
                                            require __DIR__ . '/components/asset_badge.php'; 
                                            ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <?php 
                                            $badgeType = 'status'; $badgeValue = $asset['status'];
                                            require __DIR__ . '/components/asset_badge.php'; 
                                            ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-[#232426]/70">
                                            <?= date('Y-m-d', strtotime($asset['updated_at'])) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex items-center justify-end space-x-2">
                                                <a href="<?= BASE_URL ?>/modules/assets/view.php?id=<?= $asset['id'] ?>" class="text-[#232426]/50 hover:text-[#EF6351]" title="View">
                                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                                </a>
                                                <a href="<?= BASE_URL ?>/modules/assets/edit.php?id=<?= $asset['id'] ?>" class="text-[#232426]/50 hover:text-brand-primary" title="Edit">
                                                    <i data-lucide="edit-2" class="w-4 h-4"></i>
                                                </a>
                                                <button type="button" onclick="confirmDelete(<?= $asset['id'] ?>, '<?= htmlspecialchars(addslashes($asset['asset_id'])) ?>')" class="text-[#232426]/50 hover:text-red-600" title="Delete">
                                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </main>
</div>

<!-- Hidden Delete Form -->
<form id="deleteForm" action="<?= BASE_URL ?>/modules/assets/delete.php" method="POST" class="hidden">
    <input type="hidden" name="id" id="delete_id">
</form>

<script>
function confirmDelete(id, assetId) {
    confirmAction(`Are you sure you want to delete ${assetId}?`, () => {
        document.getElementById('delete_id').value = id;
        document.getElementById('deleteForm').submit();
    });
}
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
