<?php
require_once __DIR__ . '/../../config/init.php';
requireLogin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    header("Location: " . BASE_URL . "/modules/assets/index.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM assets WHERE id = ?");
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

$pageTitle = 'Edit Asset';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';

// Fetch lookups
$categories = $pdo->query("SELECT id, category_name FROM asset_categories ORDER BY category_name")->fetchAll();
$departments = $pdo->query("SELECT id, name FROM departments ORDER BY name")->fetchAll();
$users = $pdo->query("SELECT id, username FROM users WHERE status = 'active' ORDER BY username")->fetchAll();

// Breadcrumbs and Header Actions
$breadcrumbs = [
    'Asset Register' => BASE_URL . '/modules/assets/index.php',
    'Edit ' . $assetData['asset_id'] => ''
];
$secondaryAction = ['label' => 'Cancel', 'url' => BASE_URL . '/modules/assets/index.php'];
?>

<div class="flex-1 flex flex-col min-w-0 overflow-hidden">
    <?php require_once __DIR__ . '/../../includes/navbar.php'; ?>

    <main class="flex-1 overflow-y-auto p-6 bg-[#FFFAF3]">
        <div class="max-w-4xl mx-auto space-y-6">
            
            <?php 
            $pageSubtitle = "Update enterprise asset details.";
            require_once __DIR__ . '/components/asset_header.php'; 
            ?>

            <form action="<?= BASE_URL ?>/modules/assets/save.php" method="POST" class="space-y-6">
                <input type="hidden" name="id" value="<?= $assetData['id'] ?>">
                
                <!-- Asset Information -->
                <div class="bg-white rounded-xl border border-[#232426]/10 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-[#232426]/10 bg-slate-50">
                        <h3 class="text-lg font-medium text-[#232426]">Asset Information</h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-[#232426] mb-1">Asset ID</label>
                                <input type="text" disabled value="<?= htmlspecialchars($assetData['asset_id']) ?>" class="block w-full border border-[#232426]/20 rounded-lg shadow-sm py-2 px-3 bg-gray-100 cursor-not-allowed">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-[#232426] mb-1">Asset Name <span class="text-[#EF6351]">*</span></label>
                                <input type="text" name="asset_name" value="<?= htmlspecialchars($assetData['asset_name']) ?>" required class="block w-full border border-[#232426]/20 rounded-lg shadow-sm py-2 px-3 focus:border-[#EF6351] focus:ring-[#EF6351] bg-slate-50">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-[#232426] mb-1">Description</label>
                                <textarea name="description" rows="3" class="block w-full border border-[#232426]/20 rounded-lg shadow-sm py-2 px-3 focus:border-[#EF6351] focus:ring-[#EF6351] bg-slate-50"><?= htmlspecialchars($assetData['description']) ?></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-[#232426] mb-1">Category <span class="text-[#EF6351]">*</span></label>
                                <select name="category_id" required class="block w-full border border-[#232426]/20 rounded-lg shadow-sm py-2 px-3 focus:border-[#EF6351] focus:ring-[#EF6351] bg-slate-50">
                                    <option value="">Select Category...</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= $cat['id'] ?>" <?= ($assetData['category_id'] == $cat['id']) ? 'selected' : '' ?>><?= htmlspecialchars($cat['category_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-[#232426] mb-1">Asset Type <span class="text-[#EF6351]">*</span></label>
                                <input type="text" name="asset_type" value="<?= htmlspecialchars($assetData['asset_type']) ?>" required class="block w-full border border-[#232426]/20 rounded-lg shadow-sm py-2 px-3 focus:border-[#EF6351] focus:ring-[#EF6351] bg-slate-50">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ownership -->
                <div class="bg-white rounded-xl border border-[#232426]/10 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-[#232426]/10 bg-slate-50">
                        <h3 class="text-lg font-medium text-[#232426]">Ownership</h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-[#232426] mb-1">Owner <span class="text-[#EF6351]">*</span></label>
                                <select name="owner_id" required class="block w-full border border-[#232426]/20 rounded-lg shadow-sm py-2 px-3 focus:border-[#EF6351] focus:ring-[#EF6351] bg-slate-50">
                                    <option value="">Select Owner...</option>
                                    <?php foreach ($users as $user): ?>
                                        <option value="<?= $user['id'] ?>" <?= ($assetData['owner_id'] == $user['id']) ? 'selected' : '' ?>><?= htmlspecialchars($user['username']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-[#232426] mb-1">Department <span class="text-[#EF6351]">*</span></label>
                                <select name="department_id" required class="block w-full border border-[#232426]/20 rounded-lg shadow-sm py-2 px-3 focus:border-[#EF6351] focus:ring-[#EF6351] bg-slate-50">
                                    <option value="">Select Department...</option>
                                    <?php foreach ($departments as $dept): ?>
                                        <option value="<?= $dept['id'] ?>" <?= ($assetData['department_id'] == $dept['id']) ? 'selected' : '' ?>><?= htmlspecialchars($dept['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Classification -->
                <div class="bg-white rounded-xl border border-[#232426]/10 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-[#232426]/10 bg-slate-50">
                        <h3 class="text-lg font-medium text-[#232426]">Classification</h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-[#232426] mb-1">Criticality <span class="text-[#EF6351]">*</span></label>
                                <select name="criticality" required class="block w-full border border-[#232426]/20 rounded-lg shadow-sm py-2 px-3 focus:border-[#EF6351] focus:ring-[#EF6351] bg-slate-50">
                                    <?php foreach(['Low', 'Medium', 'High', 'Critical'] as $crit): ?>
                                        <option value="<?= $crit ?>" <?= ($assetData['criticality'] === $crit) ? 'selected' : '' ?>><?= $crit ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-[#232426] mb-1">Status <span class="text-[#EF6351]">*</span></label>
                                <select name="status" required class="block w-full border border-[#232426]/20 rounded-lg shadow-sm py-2 px-3 focus:border-[#EF6351] focus:ring-[#EF6351] bg-slate-50">
                                    <?php foreach(['Active', 'Inactive', 'Maintenance', 'Pending Deployment', 'Retired'] as $stat): ?>
                                        <option value="<?= $stat ?>" <?= ($assetData['status'] === $stat) ? 'selected' : '' ?>><?= $stat ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CIA Profile -->
                <div class="bg-white rounded-xl border border-[#232426]/10 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-[#232426]/10 bg-slate-50">
                        <h3 class="text-lg font-medium text-[#232426]">CIA Profile (1-5)</h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-[#232426] mb-1">Confidentiality <span class="text-[#EF6351]">*</span></label>
                                <select name="confidentiality" required class="block w-full border border-[#232426]/20 rounded-lg shadow-sm py-2 px-3 focus:border-[#EF6351] focus:ring-[#EF6351] bg-slate-50">
                                    <?php for($i=1; $i<=5; $i++): ?>
                                        <option value="<?=$i?>" <?= ($assetData['confidentiality'] == $i) ? 'selected' : '' ?>><?=$i?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-[#232426] mb-1">Integrity <span class="text-[#EF6351]">*</span></label>
                                <select name="integrity" required class="block w-full border border-[#232426]/20 rounded-lg shadow-sm py-2 px-3 focus:border-[#EF6351] focus:ring-[#EF6351] bg-slate-50">
                                    <?php for($i=1; $i<=5; $i++): ?>
                                        <option value="<?=$i?>" <?= ($assetData['integrity'] == $i) ? 'selected' : '' ?>><?=$i?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-[#232426] mb-1">Availability <span class="text-[#EF6351]">*</span></label>
                                <select name="availability" required class="block w-full border border-[#232426]/20 rounded-lg shadow-sm py-2 px-3 focus:border-[#EF6351] focus:ring-[#EF6351] bg-slate-50">
                                    <?php for($i=1; $i<=5; $i++): ?>
                                        <option value="<?=$i?>" <?= ($assetData['availability'] == $i) ? 'selected' : '' ?>><?=$i?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Location & Lifecycle -->
                <div class="bg-white rounded-xl border border-[#232426]/10 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-[#232426]/10 bg-slate-50">
                        <h3 class="text-lg font-medium text-[#232426]">Location & Lifecycle</h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-[#232426] mb-1">Location</label>
                                <input type="text" name="location" value="<?= htmlspecialchars($assetData['location']) ?>" class="block w-full border border-[#232426]/20 rounded-lg shadow-sm py-2 px-3 focus:border-[#EF6351] focus:ring-[#EF6351] bg-slate-50">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-[#232426] mb-1">Purchase Date</label>
                                <input type="date" name="purchase_date" value="<?= htmlspecialchars($assetData['purchase_date'] ?? '') ?>" class="block w-full border border-[#232426]/20 rounded-lg shadow-sm py-2 px-3 focus:border-[#EF6351] focus:ring-[#EF6351] bg-slate-50">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-[#232426] mb-1">Warranty Expiry</label>
                                <input type="date" name="warranty_expiry" value="<?= htmlspecialchars($assetData['warranty_expiry'] ?? '') ?>" class="block w-full border border-[#232426]/20 rounded-lg shadow-sm py-2 px-3 focus:border-[#EF6351] focus:ring-[#EF6351] bg-slate-50">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end space-x-3">
                    <a href="<?= BASE_URL ?>/modules/assets/index.php" class="bg-white border border-[#232426]/20 hover:bg-slate-50 text-[#232426] px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm">
                        Cancel
                    </a>
                    <button type="submit" class="bg-[#EF6351] hover:opacity-90 text-white px-4 py-2 rounded-lg text-sm font-medium shadow-lg flex items-center">
                        <i data-lucide="save" class="w-4 h-4 mr-2"></i> Update Asset
                    </button>
                </div>
            </form>

        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
