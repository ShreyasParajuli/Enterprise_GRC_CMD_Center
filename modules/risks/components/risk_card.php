<?php
// Reusable Risk Card Component
$risk = $risk ?? [];

require_once __DIR__ . '/../helpers/risk_helper.php';

// Safe fallbacks if $risk is missing keys
$score = calculateRiskScore($risk['likelihood'] ?? 0, $risk['impact'] ?? 0);
$level = getRiskLevel($score);
$badgeClasses = getRiskScoreBadgeClasses($score);
$recommendedTreatment = getRecommendedTreatment($score);
?>
<div class="bg-white rounded-xl border border-[#232426]/10 shadow-sm p-5 hover:shadow-lg transition-all hover:-translate-y-1 group">
    <div class="flex justify-between items-start mb-3">
        <span class="text-xs font-bold text-[#232426]/50 tracking-wider group-hover:text-[#EF6351] transition-colors"><?= htmlspecialchars($risk['risk_id'] ?? 'RSK-0000') ?></span>
        <span class="px-2.5 py-1 rounded-full text-xs font-bold border <?= $badgeClasses ?>">
            <?= $score ?> (<?= $level ?>)
        </span>
    </div>
    <h4 class="text-base font-bold text-[#232426] mb-3 line-clamp-1" title="<?= htmlspecialchars($risk['title'] ?? 'Placeholder Risk') ?>"><?= htmlspecialchars($risk['title'] ?? 'Placeholder Risk') ?></h4>
    
    <div class="space-y-2 mb-4">
        <div class="text-xs text-[#232426]/70 flex justify-between items-center bg-slate-50 p-2 rounded-lg border border-slate-100">
            <span class="font-semibold text-[#232426]/60 uppercase">Action:</span>
            <span class="font-bold text-[#EF6351]"><?= $recommendedTreatment ?></span>
        </div>
        <div class="text-xs text-[#232426]/70 flex justify-between items-center">
            <span class="font-semibold text-[#232426]/60">Review:</span>
            <span class="font-medium"><?= !empty($risk['review_date']) ? htmlspecialchars($risk['review_date']) : 'Not set' ?></span>
        </div>
    </div>
    
    <div class="flex items-center justify-end pt-3 border-t border-[#232426]/5">
        <a href="<?= BASE_URL ?>/modules/risks/view.php?id=<?= $risk['id'] ?? 0 ?>" class="text-[#C9AEF1] hover:text-[#EF6351] text-sm font-bold transition-colors flex items-center">
            View Details <i data-lucide="arrow-right" class="w-4 h-4 ml-1"></i>
        </a>
    </div>
</div>
