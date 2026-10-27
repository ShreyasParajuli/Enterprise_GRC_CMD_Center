<?php
/**
 * Renders an empty state for Asset tables
 * 
 * Expected variables:
 * $emptyMessage (string)
 * $emptySubMessage (string)
 */
?>
<div class="bg-white rounded-xl border border-[#232426]/10 shadow-sm p-12 text-center">
    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-slate-50 mb-4">
        <i data-lucide="server" class="w-8 h-8 text-[#232426]/30"></i>
    </div>
    <h3 class="text-lg font-medium text-[#232426] mb-1"><?= htmlspecialchars($emptyMessage ?? 'No data found') ?></h3>
    <p class="text-sm text-[#232426]/60 max-w-sm mx-auto">
        <?= htmlspecialchars($emptySubMessage ?? 'Get started by creating a new record.') ?>
    </p>
</div>
