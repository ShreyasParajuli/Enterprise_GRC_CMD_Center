<?php
require_once __DIR__ . '/../../config/init.php';
requireLogin();

$pageTitle = 'Create Asset';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';

// Fetch lookups
$categories = $pdo->query("SELECT id, category_name FROM asset_categories ORDER BY category_name")->fetchAll();
$departments = $pdo->query("SELECT id, name FROM departments ORDER BY name")->fetchAll();
$users = $pdo->query("SELECT id, username FROM users WHERE status = 'active' ORDER BY username")->fetchAll();

// Breadcrumbs and Header Actions
$breadcrumbs = [
    'Asset Register' => BASE_URL . '/modules/assets/index.php',
    'Create' => ''
];
$secondaryAction = ['label' => 'Cancel', 'url' => BASE_URL . '/modules/assets/index.php'];
?>

<div class="flex-1 flex flex-col min-w-0 overflow-hidden">
    <?php require_once __DIR__ . '/../../includes/navbar.php'; ?>

    <main class="flex-1 overflow-y-auto p-6 bg-[#FFFAF3]">
        <div class="max-w-4xl mx-auto space-y-6">
            
            <?php 
            $pageSubtitle = "Add a new asset to the enterprise inventory.";
            require_once __DIR__ . '/components/asset_header.php'; 
            ?>

            <form action="<?= BASE_URL ?>/modules/assets/save.php" method="POST" class="space-y-6">
                
                <!-- Asset Information -->
                <div class="bg-white rounded-xl border border-[#232426]/10 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-[#232426]/10 bg-slate-50">
                        <h3 class="text-lg font-medium text-[#232426]">Asset Information</h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-[#232426] mb-1">Asset Name <span class="text-[#EF6351]">*</span></label>
                                <input type="text" name="asset_name" required placeholder="e.g. Finance Database" class="block w-full border border-[#232426]/20 rounded-lg shadow-sm py-2 px-3 focus:border-[#EF6351] focus:ring-[#EF6351] bg-slate-50">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-[#232426] mb-1">Description</label>
                                <textarea name="description" rows="3" class="block w-full border border-[#232426]/20 rounded-lg shadow-sm py-2 px-3 focus:border-[#EF6351] focus:ring-[#EF6351] bg-slate-50"></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-[#232426] mb-1">Category <span class="text-[#EF6351]">*</span></label>
                                <select name="category_id" required class="block w-full border border-[#232426]/20 rounded-lg shadow-sm py-2 px-3 focus:border-[#EF6351] focus:ring-[#EF6351] bg-slate-50">
                                    <option value="">Select Category...</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['category_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-[#232426] mb-1">Asset Type <span class="text-[#EF6351]">*</span></label>
                                <input type="text" name="asset_type" required placeholder="e.g. Relational Database" class="block w-full border border-[#232426]/20 rounded-lg shadow-sm py-2 px-3 focus:border-[#EF6351] focus:ring-[#EF6351] bg-slate-50">
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
                                        <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['username']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-[#232426] mb-1">Department <span class="text-[#EF6351]">*</span></label>
                                <select name="department_id" required class="block w-full border border-[#232426]/20 rounded-lg shadow-sm py-2 px-3 focus:border-[#EF6351] focus:ring-[#EF6351] bg-slate-50">
                                    <option value="">Select Department...</option>
                                    <?php foreach ($departments as $dept): ?>
                                        <option value="<?= $dept['id'] ?>"><?= htmlspecialchars($dept['name']) ?></option>
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
                                <label class="block text-sm font-medium text-[#232426] mb-1">Calculated Criticality</label>
                                <div class="w-full border border-[#232426]/20 rounded-lg shadow-sm py-2 px-3 bg-gray-100 flex justify-between items-center h-[42px]">
                                    <span id="preview_criticality" class="text-sm font-medium text-[#232426]/50">Awaiting CIA...</span>
                                    <span id="preview_score" class="text-xs font-bold text-[#232426]/50">--/15</span>
                                </div>
                                <p class="text-xs text-[#232426]/50 mt-1">Derived from CIA Profile automatically.</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-[#232426] mb-1">Status <span class="text-[#EF6351]">*</span></label>
                                <select name="status" required class="block w-full border border-[#232426]/20 rounded-lg shadow-sm py-2 px-3 focus:border-[#EF6351] focus:ring-[#EF6351] bg-slate-50">
                                    <option value="">Select Status...</option>
                                    <option value="Active">Active</option>
                                    <option value="Inactive">Inactive</option>
                                    <option value="Maintenance">Maintenance</option>
                                    <option value="Pending Deployment">Pending Deployment</option>
                                    <option value="Retired">Retired</option>
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
                                    <option value="">Select (1-5)</option>
                                    <?php for($i=1; $i<=5; $i++): ?><option value="<?=$i?>"><?=$i?></option><?php endfor; ?>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-[#232426] mb-1">Integrity <span class="text-[#EF6351]">*</span></label>
                                <select name="integrity" required class="block w-full border border-[#232426]/20 rounded-lg shadow-sm py-2 px-3 focus:border-[#EF6351] focus:ring-[#EF6351] bg-slate-50">
                                    <option value="">Select (1-5)</option>
                                    <?php for($i=1; $i<=5; $i++): ?><option value="<?=$i?>"><?=$i?></option><?php endfor; ?>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-[#232426] mb-1">Availability <span class="text-[#EF6351]">*</span></label>
                                <select name="availability" required class="block w-full border border-[#232426]/20 rounded-lg shadow-sm py-2 px-3 focus:border-[#EF6351] focus:ring-[#EF6351] bg-slate-50">
                                    <option value="">Select (1-5)</option>
                                    <?php for($i=1; $i<=5; $i++): ?><option value="<?=$i?>"><?=$i?></option><?php endfor; ?>
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
                                <input type="text" name="location" placeholder="e.g. AWS us-east-1" class="block w-full border border-[#232426]/20 rounded-lg shadow-sm py-2 px-3 focus:border-[#EF6351] focus:ring-[#EF6351] bg-slate-50">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-[#232426] mb-1">Purchase Date</label>
                                <input type="date" name="purchase_date" class="block w-full border border-[#232426]/20 rounded-lg shadow-sm py-2 px-3 focus:border-[#EF6351] focus:ring-[#EF6351] bg-slate-50">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-[#232426] mb-1">Warranty Expiry</label>
                                <input type="date" name="warranty_expiry" class="block w-full border border-[#232426]/20 rounded-lg shadow-sm py-2 px-3 focus:border-[#EF6351] focus:ring-[#EF6351] bg-slate-50">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end space-x-3">
                    <a href="<?= BASE_URL ?>/modules/assets/index.php" class="bg-white border border-[#232426]/20 hover:bg-slate-50 text-[#232426] px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm">
                        Cancel
                    </a>
                    <button type="submit" class="bg-[#EF6351] hover:opacity-90 text-white px-4 py-2 rounded-lg text-sm font-medium shadow-lg flex items-center">
                        <i data-lucide="save" class="w-4 h-4 mr-2"></i> Save Asset
                    </button>
                </div>
            </form>

        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
