<?php
require_once __DIR__ . '/../../config/init.php';
requireLogin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    header("Location: " . BASE_URL . "/modules/assets/index.php");
    exit;
}

$stmt = $pdo->prepare("
    SELECT a.*, c.category_name, u.username as owner_name, d.name as department_name 
    FROM assets a
    LEFT JOIN asset_categories c ON a.category_id = c.id
    LEFT JOIN users u ON a.owner_id = u.id
    LEFT JOIN departments d ON a.department_id = d.id
    WHERE a.id = ?
");
$stmt->execute([$id]);
$assetData = $stmt->fetch();

if (!$assetData) {
    // 404 State
    $pageTitle = 'Asset Not Found';
    require_once __DIR__ . '/../../includes/header.php';
    require_once __DIR__ . '/../../includes/sidebar.php';
    ?>
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
        <?php require_once __DIR__ . '/../../includes/navbar.php'; ?>
        <main class="flex-1 overflow-y-auto p-6 bg-[#FFFAF3] flex items-center justify-center">
            <div class="text-center">
                <i data-lucide="server-off" class="w-16 h-16 text-[#232426]/30 mx-auto mb-4"></i>
                <h2 class="text-2xl font-bold text-[#232426] mb-2">Asset Not Found</h2>
                <p class="text-[#232426]/60 mb-6">Asset AST-9999 could not be found or does not exist.</p>
                <a href="<?= BASE_URL ?>/modules/assets/index.php" class="bg-[#EF6351] text-white px-6 py-2 rounded-lg shadow-lg">Back to Asset Register</a>
            </div>
        </main>
    </div>
    <?php require_once __DIR__ . '/../../includes/footer.php'; ?>
    <?php
    exit;
}

$pageTitle = 'Asset Profile';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
require_once __DIR__ . '/helpers/asset_helper.php';

// Breadcrumbs and Header Actions
$breadcrumbs = [
    'Asset Register' => BASE_URL . '/modules/assets/index.php',
    $assetData['asset_id'] => ''
];
$secondaryAction = ['label' => 'Back', 'url' => BASE_URL . '/modules/assets/index.php'];
$primaryAction = ['label' => 'Edit Asset', 'url' => BASE_URL . '/modules/assets/edit.php?id=' . $assetData['id'], 'icon' => 'edit'];
?>

<div class="flex-1 flex flex-col min-w-0 overflow-hidden">
    <?php require_once __DIR__ . '/../../includes/navbar.php'; ?>

    <main class="flex-1 overflow-y-auto p-6 bg-[#FFFAF3]">
        <div class="max-w-5xl mx-auto space-y-6">
            
            <?php 
            $pageSubtitle = "Comprehensive details and associations for this asset.";
            require_once __DIR__ . '/components/asset_header.php'; 
            ?>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left Column -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Asset Overview -->
                    <div class="bg-white rounded-xl border border-[#232426]/10 shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-[#232426]/10 bg-slate-50 flex justify-between items-center">
                            <h3 class="text-lg font-medium text-[#232426]">Asset Overview</h3>
                        </div>
                        <div class="p-6">
                            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-6">
                                <div>
                                    <dt class="text-sm font-medium text-[#232426]/60">Asset ID</dt>
                                    <dd class="mt-1 text-sm text-[#232426] font-semibold"><?= htmlspecialchars($assetData['asset_id']) ?></dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-[#232426]/60">Asset Name</dt>
                                    <dd class="mt-1 text-sm text-[#232426]"><?= htmlspecialchars($assetData['asset_name']) ?></dd>
                                </div>
                                <div class="sm:col-span-2">
                                    <dt class="text-sm font-medium text-[#232426]/60">Description</dt>
                                    <dd class="mt-1 text-sm text-[#232426]"><?= nl2br(htmlspecialchars($assetData['description'] ?: '-')) ?></dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-[#232426]/60">Category</dt>
                                    <dd class="mt-1 text-sm text-[#232426]"><?= htmlspecialchars($assetData['category_name'] ?? 'Unknown') ?></dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-[#232426]/60">Type</dt>
                                    <dd class="mt-1 text-sm text-[#232426]"><?= htmlspecialchars($assetData['asset_type']) ?></dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-[#232426]/60">Status</dt>
                                    <dd class="mt-1">
                                        <?php $badgeType = 'status'; $badgeValue = $assetData['status']; require __DIR__ . '/components/asset_badge.php'; ?>
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-[#232426]/60">Criticality</dt>
                                    <dd class="mt-1">
                                        <?php $badgeType = 'criticality'; $badgeValue = $assetData['criticality']; require __DIR__ . '/components/asset_badge.php'; ?>
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <!-- Ownership -->
                    <div class="bg-white rounded-xl border border-[#232426]/10 shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-[#232426]/10 bg-slate-50">
                            <h3 class="text-lg font-medium text-[#232426]">Ownership</h3>
                        </div>
                        <div class="p-6">
                            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-6">
                                <div>
                                    <dt class="text-sm font-medium text-[#232426]/60">Owner</dt>
                                    <dd class="mt-1 text-sm text-[#232426]"><?= htmlspecialchars($assetData['owner_name'] ?? 'Unknown') ?></dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-[#232426]/60">Department</dt>
                                    <dd class="mt-1 text-sm text-[#232426]"><?= htmlspecialchars($assetData['department_name'] ?? 'Unknown') ?></dd>
                                </div>
                                <div class="sm:col-span-2">
                                    <dt class="text-sm font-medium text-[#232426]/60">Location</dt>
                                    <dd class="mt-1 text-sm text-[#232426]"><?= htmlspecialchars($assetData['location'] ?: '-') ?></dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <!-- Related Risks (Placeholder) -->
                    <div class="bg-white rounded-xl border border-[#232426]/10 shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-[#232426]/10 bg-slate-50">
                            <h3 class="text-lg font-medium text-[#232426]">Associated Risks</h3>
                        </div>
                        <div class="p-6 text-center">
                            <i data-lucide="shield-alert" class="w-8 h-8 text-[#232426]/30 mx-auto mb-2"></i>
                            <p class="text-sm text-[#232426]/60">No risks associated yet.</p>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="space-y-6">
                    <!-- CIA Profile -->
                    <div class="bg-white rounded-xl border border-[#232426]/10 shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-[#232426]/10 bg-slate-50">
                            <h3 class="text-lg font-medium text-[#232426]">CIA Profile</h3>
                        </div>
                        <div class="p-6 space-y-4">
                            <div>
                                <div class="flex justify-between mb-1">
                                    <span class="text-sm font-medium text-[#232426]/60">Confidentiality</span>
                                    <span class="text-sm font-bold text-[#232426]"><?= (int)$assetData['confidentiality'] ?>/5</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-gray-400 h-2 rounded-full" style="width: <?= ((int)$assetData['confidentiality'] / 5) * 100 ?>%"></div>
                                </div>
                            </div>
                            <div>
                                <div class="flex justify-between mb-1">
                                    <span class="text-sm font-medium text-[#232426]/60">Integrity</span>
                                    <span class="text-sm font-bold text-[#232426]"><?= (int)$assetData['integrity'] ?>/5</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-gray-400 h-2 rounded-full" style="width: <?= ((int)$assetData['integrity'] / 5) * 100 ?>%"></div>
                                </div>
                            </div>
                            <div>
                                <div class="flex justify-between mb-1">
                                    <span class="text-sm font-medium text-[#232426]/60">Availability</span>
                                    <span class="text-sm font-bold text-[#232426]"><?= (int)$assetData['availability'] ?>/5</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-gray-400 h-2 rounded-full" style="width: <?= ((int)$assetData['availability'] / 5) * 100 ?>%"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Lifecycle -->
                    <div class="bg-white rounded-xl border border-[#232426]/10 shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-[#232426]/10 bg-slate-50">
                            <h3 class="text-lg font-medium text-[#232426]">Lifecycle</h3>
                        </div>
                        <div class="p-6 space-y-4">
                            <div>
                                <span class="block text-xs font-medium text-[#232426]/60 uppercase tracking-wider">Purchase Date</span>
                                <span class="text-sm text-[#232426] font-medium"><?= $assetData['purchase_date'] ? date('M d, Y', strtotime($assetData['purchase_date'])) : '-' ?></span>
                            </div>
                            <div>
                                <span class="block text-xs font-medium text-[#232426]/60 uppercase tracking-wider">Warranty Expiry</span>
                                <span class="text-sm text-[#232426] font-medium"><?= $assetData['warranty_expiry'] ? date('M d, Y', strtotime($assetData['warranty_expiry'])) : '-' ?></span>
                            </div>
                            <hr class="border-[#232426]/10">
                            <div>
                                <span class="block text-xs font-medium text-[#232426]/60 uppercase tracking-wider">Created</span>
                                <span class="text-sm text-[#232426] font-medium"><?= date('M d, Y', strtotime($assetData['created_at'])) ?></span>
                            </div>
                            <div>
                                <span class="block text-xs font-medium text-[#232426]/60 uppercase tracking-wider">Last Updated</span>
                                <span class="text-sm text-[#232426] font-medium"><?= date('M d, Y', strtotime($assetData['updated_at'])) ?></span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
