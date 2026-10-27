<?php
/**
 * Renders a KPI summary card for the Asset Dashboard
 * 
 * Expected variables:
 * $cardTitle (string)
 * $cardValue (mixed)
 * $cardColor (string) - e.g. 'brand', 'red', 'orange', 'yellow', 'green', 'gray'
 */

if (!isset($cardTitle) || !isset($cardValue)) {
    return;
}

$colorMap = [
    'brand' => ['bg' => 'bg-white', 'border' => 'border-[#232426]/10', 'text' => 'text-[#232426]', 'label' => 'text-[#232426]/60'],
    'red' => ['bg' => 'bg-red-50', 'border' => 'border-red-200', 'text' => 'text-red-700', 'label' => 'text-red-800'],
    'orange' => ['bg' => 'bg-orange-50', 'border' => 'border-orange-200', 'text' => 'text-orange-700', 'label' => 'text-orange-800'],
    'yellow' => ['bg' => 'bg-yellow-50', 'border' => 'border-yellow-200', 'text' => 'text-yellow-700', 'label' => 'text-yellow-800'],
    'green' => ['bg' => 'bg-green-50', 'border' => 'border-green-200', 'text' => 'text-green-700', 'label' => 'text-green-800'],
    'gray' => ['bg' => 'bg-gray-50', 'border' => 'border-gray-200', 'text' => 'text-gray-700', 'label' => 'text-gray-800'],
];

$theme = $colorMap[$cardColor ?? 'brand'] ?? $colorMap['brand'];
?>
<div class="<?= $theme['bg'] ?> <?= $theme['border'] ?> rounded-xl border shadow-sm p-4 flex flex-col items-center justify-center transition-transform hover:scale-105">
    <span class="text-sm font-semibold <?= $theme['label'] ?> uppercase tracking-wider mb-1"><?= htmlspecialchars($cardTitle) ?></span>
    <span class="text-3xl font-bold <?= $theme['text'] ?>"><?= htmlspecialchars($cardValue) ?></span>
</div>
