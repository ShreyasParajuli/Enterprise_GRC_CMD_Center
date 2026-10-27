<?php
/**
 * Renders a standardized badge for Asset Criticality or Status
 * 
 * Expected variables:
 * $badgeType (string) - 'criticality' or 'status'
 * $badgeValue (string) - The value to display
 */

if (!isset($badgeType) || !isset($badgeValue)) {
    return;
}

$badgeClass = getAssetBadgeClass($badgeType, $badgeValue);
?>
<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border <?= $badgeClass ?>">
    <?= htmlspecialchars($badgeValue) ?>
</span>
