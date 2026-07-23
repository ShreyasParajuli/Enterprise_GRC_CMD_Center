<?php
// Reusable Risk Table Component
$risks = $risks ?? [];
$hasRisks = !empty($risks);

require_once __DIR__ . '/../helpers/risk_helper.php';
?>

<div class="bg-white rounded-xl border border-[#232426]/10 shadow-sm overflow-hidden">
    <?php if (!$hasRisks): ?>
        <div class="p-16 text-center">
            <div class="text-5xl mb-4 transition-transform hover:scale-110 inline-block">
                🛡️
            </div>
            <h3 class="text-xl font-bold text-[#232426] mb-2">No enterprise risks match your search.</h3>
            <p class="text-[#232426]/60 mb-6 max-w-md mx-auto text-base">Try adjusting your filters or create a new risk.</p>
            <a href="<?= BASE_URL ?>/modules/risks/create.php" class="bg-[#EF6351] hover:opacity-90 text-white px-6 py-2.5 rounded-lg text-sm font-semibold transition-colors inline-flex items-center shadow-md hover:shadow-lg">
                <i data-lucide="plus" class="w-4 h-4 mr-2"></i> Create Risk
            </a>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-[#232426]">
                <thead class="text-xs text-[#232426]/70 uppercase bg-slate-50 border-b border-[#232426]/10">
                    <tr>
                        <th scope="col" class="px-4 py-4 font-semibold tracking-wider w-10">
                            <input type="checkbox" id="selectAll" onclick="toggleBulkSelect(this)" class="rounded border-[#232426]/30 text-[#EF6351] focus:ring-[#EF6351]">
                        </th>
                        <th scope="col" class="px-4 py-4 font-semibold tracking-wider">Risk ID & Title</th>
                        <th scope="col" class="px-4 py-4 font-semibold tracking-wider">Category / Dept</th>
                        <th scope="col" class="px-4 py-4 font-semibold tracking-wider">Owner</th>
                        <th scope="col" class="px-4 py-4 font-semibold tracking-wider text-center">Score & Level</th>
                        <th scope="col" class="px-4 py-4 font-semibold tracking-wider text-center">Strategy / Recommendation</th>
                        <th scope="col" class="px-4 py-4 font-semibold tracking-wider text-center">Status</th>
                        <th scope="col" class="px-4 py-4 font-semibold tracking-wider text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php foreach ($risks as $risk): 
                        $score = calculateRiskScore($risk['likelihood'], $risk['impact']);
                        $level = getRiskLevel($score);
                        $badgeClasses = getRiskScoreBadgeClasses($score);
                        $recommendedTreatment = getRecommendedTreatment($score);
                    ?>
                        <tr class="hover:bg-slate-50 transition-colors group">
                            <td class="px-4 py-4">
                                <input type="checkbox" name="risk_ids[]" value="<?= $risk['id'] ?>" onclick="updateBulkSelect()" class="rounded border-[#232426]/30 text-[#EF6351] focus:ring-[#EF6351]">
                            </td>
                            <td class="px-4 py-4">
                                <div class="font-bold text-[#232426] group-hover:text-[#EF6351] transition-colors"><?= htmlspecialchars($risk['risk_id']) ?></div>
                                <div class="text-[#232426]/70 mt-1 max-w-[200px] truncate" title="<?= htmlspecialchars($risk['title']) ?>"><?= htmlspecialchars($risk['title']) ?></div>
                            </td>
                            <td class="px-4 py-4">
                                <div class="text-[#232426] font-medium"><?= htmlspecialchars($risk['category_name']) ?></div>
                                <div class="text-xs text-[#232426]/60 mt-1 flex items-center">
                                    <i data-lucide="building" class="w-3 h-3 mr-1"></i> <?= htmlspecialchars($risk['department_name']) ?>
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex items-center whitespace-nowrap text-[#232426] font-medium">
                                    <i data-lucide="user" class="w-4 h-4 mr-2 text-[#232426]/50"></i>
                                    <?= htmlspecialchars($risk['owner_name']) ?>
                                </div>
                            </td>
                            <td class="px-4 py-4 text-center whitespace-nowrap">
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold border <?= $badgeClasses ?>">
                                    <?= $score ?> (<?= $level ?>)
                                </span>
                            </td>
                            <td class="px-4 py-4 text-center">
                                <div class="text-[#232426] font-medium"><?= htmlspecialchars($risk['treatment_strategy']) ?></div>
                                <div class="text-xs text-[#EF6351] font-semibold mt-1 flex items-center justify-center">
                                    <i data-lucide="zap" class="w-3 h-3 mr-1"></i> Rec: <?= $recommendedTreatment ?>
                                </div>
                            </td>
                            <td class="px-4 py-4 text-center">
                                <?php 
                                    $statusClass = 'bg-slate-100 text-slate-800 border-slate-200';
                                    if ($risk['status'] === 'Open') $statusClass = 'bg-blue-100 text-blue-800 border-blue-200';
                                    if ($risk['status'] === 'In Progress') $statusClass = 'bg-yellow-100 text-yellow-800 border-yellow-200';
                                    if ($risk['status'] === 'Closed') $statusClass = 'bg-[#BBC7B6]/30 text-[#232426] border-[#BBC7B6]/50';
                                ?>
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold border <?= $statusClass ?> whitespace-nowrap">
                                    <?= htmlspecialchars($risk['status']) ?>
                                </span>
                            </td>
                            <td class="px-4 py-4 text-right whitespace-nowrap">
                                <a href="<?= BASE_URL ?>/modules/risks/view.php?id=<?= $risk['id'] ?>" class="text-[#C9AEF1] hover:text-[#EF6351] font-medium mr-3 transition-colors hover:scale-110 inline-block" title="View">
                                    <i data-lucide="eye" class="w-5 h-5"></i>
                                </a>
                                <a href="<?= BASE_URL ?>/modules/risks/edit.php?id=<?= $risk['id'] ?>" class="text-blue-500 hover:text-blue-700 font-medium mr-3 transition-colors hover:scale-110 inline-block" title="Edit">
                                    <i data-lucide="edit" class="w-5 h-5"></i>
                                </a>
                                <a href="<?= BASE_URL ?>/modules/risks/delete.php?id=<?= $risk['id'] ?>" class="text-red-500 hover:text-red-700 font-medium transition-colors hover:scale-110 inline-block" onclick="event.preventDefault(); let url=this.href; confirmAction('Are you sure you want to delete this risk?', () => window.location.href=url);" title="Delete">
                                    <i data-lucide="trash-2" class="w-5 h-5"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
