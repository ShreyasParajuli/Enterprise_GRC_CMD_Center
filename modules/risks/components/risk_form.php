<?php
// Reusable Risk Form Component
$isEdit = $isEdit ?? false;
$riskData = $riskData ?? [];
$categories = $categories ?? [];
$departments = $departments ?? [];
$users = $users ?? [];
?>

<div class="bg-white rounded-xl border border-[#232426]/10 shadow-sm overflow-hidden">
    <form action="<?= BASE_URL ?>/modules/risks/save.php" method="POST" class="p-6 space-y-6">
        
        <?php if ($isEdit): ?>
            <input type="hidden" name="id" value="<?= (int)($riskData['id'] ?? 0) ?>">
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-[#232426] mb-1">Risk Title <span class="text-[#EF6351]">*</span></label>
                <input type="text" name="title" class="w-full border-[#232426]/20 rounded-lg shadow-sm focus:border-[#EF6351] focus:ring-[#EF6351] bg-slate-50" placeholder="e.g. Data Breach via Third-Party" value="<?= htmlspecialchars($riskData['title'] ?? '') ?>" required>
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-[#232426] mb-1">Description</label>
                <textarea name="description" rows="4" class="w-full border-[#232426]/20 rounded-lg shadow-sm focus:border-[#EF6351] focus:ring-[#EF6351] bg-slate-50" placeholder="Detailed description of the risk..."><?= htmlspecialchars($riskData['description'] ?? '') ?></textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-[#232426] mb-1">Category <span class="text-[#EF6351]">*</span></label>
                <select name="category_id" class="w-full border-[#232426]/20 rounded-lg shadow-sm focus:border-[#EF6351] focus:ring-[#EF6351] bg-slate-50" required>
                    <option value="">Select Category</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= (($riskData['category_id'] ?? '') == $cat['id']) ? 'selected' : '' ?>><?= htmlspecialchars($cat['category_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-[#232426] mb-1">Department <span class="text-[#EF6351]">*</span></label>
                <select name="department_id" class="w-full border-[#232426]/20 rounded-lg shadow-sm focus:border-[#EF6351] focus:ring-[#EF6351] bg-slate-50" required>
                    <option value="">Select Department</option>
                    <?php foreach ($departments as $dept): ?>
                        <option value="<?= $dept['id'] ?>" <?= (($riskData['department_id'] ?? '') == $dept['id']) ? 'selected' : '' ?>><?= htmlspecialchars($dept['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-[#232426] mb-1">Risk Owner <span class="text-[#EF6351]">*</span></label>
                <select name="owner_id" class="w-full border-[#232426]/20 rounded-lg shadow-sm focus:border-[#EF6351] focus:ring-[#EF6351] bg-slate-50" required>
                    <option value="">Select Owner</option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?= $user['id'] ?>" <?= (($riskData['owner_id'] ?? '') == $user['id']) ? 'selected' : '' ?>><?= htmlspecialchars($user['username']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-[#232426] mb-1">Treatment Strategy <span class="text-[#EF6351]">*</span></label>
                <select name="treatment_strategy" class="w-full border-[#232426]/20 rounded-lg shadow-sm focus:border-[#EF6351] focus:ring-[#EF6351] bg-slate-50" required>
                    <?php foreach (['Mitigate', 'Transfer', 'Avoid', 'Accept'] as $strategy): ?>
                        <option value="<?= $strategy ?>" <?= (($riskData['treatment_strategy'] ?? 'Mitigate') === $strategy) ? 'selected' : '' ?>><?= $strategy ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-[#232426] mb-1">Likelihood (1-5) <span class="text-[#EF6351]">*</span></label>
                <select name="likelihood" class="w-full border-[#232426]/20 rounded-lg shadow-sm focus:border-[#EF6351] focus:ring-[#EF6351] bg-slate-50" required>
                    <option value="">Select Likelihood</option>
                    <?php for($i=1; $i<=5; $i++): ?>
                        <option value="<?= $i ?>" <?= (($riskData['likelihood'] ?? '') == $i) ? 'selected' : '' ?>><?= $i ?></option>
                    <?php endfor; ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-[#232426] mb-1">Impact (1-5) <span class="text-[#EF6351]">*</span></label>
                <select name="impact" class="w-full border-[#232426]/20 rounded-lg shadow-sm focus:border-[#EF6351] focus:ring-[#EF6351] bg-slate-50" required>
                    <option value="">Select Impact</option>
                    <?php for($i=1; $i<=5; $i++): ?>
                        <option value="<?= $i ?>" <?= (($riskData['impact'] ?? '') == $i) ? 'selected' : '' ?>><?= $i ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-[#232426] mb-1">Status <span class="text-[#EF6351]">*</span></label>
                <select name="status" class="w-full border-[#232426]/20 rounded-lg shadow-sm focus:border-[#EF6351] focus:ring-[#EF6351] bg-slate-50" required>
                    <?php foreach (['Open', 'In Progress', 'Closed', 'Archived'] as $status): ?>
                        <option value="<?= $status ?>" <?= (($riskData['status'] ?? 'Open') === $status) ? 'selected' : '' ?>><?= $status ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-[#232426] mb-1">Review Date</label>
                <input type="date" name="review_date" class="w-full border-[#232426]/20 rounded-lg shadow-sm focus:border-[#EF6351] focus:ring-[#EF6351] bg-slate-50" value="<?= htmlspecialchars($riskData['review_date'] ?? '') ?>">
            </div>
        </div>

        <div class="pt-6 border-t border-[#232426]/10 flex justify-end space-x-3">
            <a href="<?= BASE_URL ?>/modules/risks/index.php" class="bg-white border border-[#232426]/20 text-[#232426] hover:bg-slate-50 px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm">
                Cancel
            </a>
            <button type="submit" class="bg-[#EF6351] hover:opacity-90 text-white px-5 py-2 rounded-lg text-sm font-medium transition-colors shadow-md flex items-center">
                <i data-lucide="save" class="w-4 h-4 mr-2"></i> Save Risk
            </button>
        </div>
    </form>
</div>
