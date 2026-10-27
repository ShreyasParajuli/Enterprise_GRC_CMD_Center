<?php
/**
 * Renders the page header and breadcrumbs for Asset Module
 * 
 * Expected variables:
 * $pageTitle (string)
 * $pageSubtitle (string)
 * $breadcrumbs (array) - ['label' => 'url']
 * $primaryAction (array|null) - ['label' => '...', 'url' => '...', 'icon' => '...']
 * $secondaryAction (array|null) - ['label' => '...', 'url' => '...', 'icon' => '...']
 */
?>
<div class="mb-6">
    <nav class="text-sm font-medium text-brand-dark/50 mb-4" aria-label="Breadcrumb">
        <ol class="list-none p-0 inline-flex">
            <li class="flex items-center">
                <a href="<?= BASE_URL ?>/pages/dashboard.php" class="hover:text-brand-primary">Dashboard</a>
                <i data-lucide="chevron-right" class="w-4 h-4 mx-2"></i>
            </li>
            <?php foreach ($breadcrumbs as $label => $url): ?>
                <li class="flex items-center">
                    <?php if ($url): ?>
                        <a href="<?= htmlspecialchars($url) ?>" class="hover:text-brand-primary"><?= htmlspecialchars($label) ?></a>
                        <i data-lucide="chevron-right" class="w-4 h-4 mx-2"></i>
                    <?php else: ?>
                        <span class="text-[#232426]"><?= htmlspecialchars($label) ?></span>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ol>
    </nav>

    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h2 class="text-2xl font-bold text-[#232426]"><?= htmlspecialchars($pageTitle) ?></h2>
            <?php if (isset($pageSubtitle)): ?>
                <p class="text-[#232426]/70 mt-1"><?= htmlspecialchars($pageSubtitle) ?></p>
            <?php endif; ?>
        </div>
        <div class="flex space-x-3">
            <?php if (isset($secondaryAction)): ?>
                <a href="<?= htmlspecialchars($secondaryAction['url']) ?>" class="bg-white border border-[#232426]/20 hover:bg-slate-50 text-[#232426] px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm flex items-center inline-flex">
                    <?php if (isset($secondaryAction['icon'])): ?>
                        <i data-lucide="<?= $secondaryAction['icon'] ?>" class="w-4 h-4 mr-2"></i>
                    <?php endif; ?>
                    <?= htmlspecialchars($secondaryAction['label']) ?>
                </a>
            <?php endif; ?>
            
            <?php if (isset($primaryAction)): ?>
                <a href="<?= htmlspecialchars($primaryAction['url']) ?>" class="bg-[#EF6351] hover:opacity-90 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-lg flex items-center inline-flex">
                    <?php if (isset($primaryAction['icon'])): ?>
                        <i data-lucide="<?= $primaryAction['icon'] ?>" class="w-4 h-4 mr-2"></i>
                    <?php endif; ?>
                    <?= htmlspecialchars($primaryAction['label']) ?>
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>
