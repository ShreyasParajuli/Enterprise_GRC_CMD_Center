<?php
// Sidebar Navigation
$currentPage = basename($_SERVER['PHP_SELF']);
$currentDir = basename(dirname($_SERVER['PHP_SELF']));
$parentDir = basename(dirname(dirname($_SERVER['PHP_SELF'])));
$isRisks = ($currentDir === 'risks' && $parentDir === 'modules');

$navItems = [
    ['label' => 'Dashboard', 'url' => '/pages/dashboard.php', 'icon' => 'layout-dashboard', 'active' => $currentPage === 'dashboard.php'],
    ['label' => 'Risk Register', 'url' => '/modules/risks/index.php', 'icon' => 'alert-triangle', 'active' => $isRisks],
    ['label' => 'Users', 'url' => '/pages/users/index.php', 'icon' => 'users', 'active' => $currentDir === 'users'],
    ['label' => 'Roles', 'url' => '/pages/roles/index.php', 'icon' => 'shield', 'active' => $currentDir === 'roles'],
    ['label' => 'Departments', 'url' => '/pages/departments/index.php', 'icon' => 'building', 'active' => $currentDir === 'departments'],
];
?>
<aside class="w-64 bg-brand-dark text-white hidden md:flex flex-col h-screen sticky top-0">
    <div class="h-16 flex items-center px-6 border-b border-white/10">
        <i data-lucide="shield-check" class="text-brand-primary mr-3"></i>
        <span class="text-xl font-bold tracking-wide text-white">GRC Center</span>
    </div>
    
    <div class="flex-1 overflow-y-auto py-4">
        <nav class="space-y-1 px-3">
            <p class="px-3 text-xs font-semibold text-white/50 uppercase tracking-wider mb-2">Main Menu</p>
            <?php foreach ($navItems as $item): ?>
                <a href="<?= BASE_URL . $item['url'] ?>" 
                   class="group flex items-center px-3 py-2 text-sm font-medium rounded-md <?= $item['active'] ? 'bg-brand-primary text-white' : 'text-white/70 hover:bg-white/10 hover:text-white' ?>">
                    <i data-lucide="<?= $item['icon'] ?>" class="mr-3 w-5 h-5 <?= $item['active'] ? 'text-white' : 'text-white/50 group-hover:text-white' ?>"></i>
                    <?= htmlspecialchars($item['label']) ?>
                </a>
            <?php endforeach; ?>
        </nav>
        
        <nav class="space-y-1 px-3 mt-8">
            <p class="px-3 text-xs font-semibold text-white/50 uppercase tracking-wider mb-2">Administration</p>
            <a href="<?= BASE_URL ?>/pages/activity_logs.php" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md <?= $currentPage === 'activity_logs.php' ? 'bg-brand-primary text-white' : 'text-white/70 hover:bg-white/10 hover:text-white' ?>">
                <i data-lucide="activity" class="mr-3 w-5 h-5 <?= $currentPage === 'activity_logs.php' ? 'text-white' : 'text-white/50 group-hover:text-white' ?>"></i>
                Activity Logs
            </a>
            <a href="<?= BASE_URL ?>/pages/settings.php" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md <?= $currentPage === 'settings.php' ? 'bg-brand-primary text-white' : 'text-white/70 hover:bg-white/10 hover:text-white' ?>">
                <i data-lucide="settings" class="mr-3 w-5 h-5 <?= $currentPage === 'settings.php' ? 'text-white' : 'text-white/50 group-hover:text-white' ?>"></i>
                Settings
            </a>
        </nav>

        <!-- Placeholder for future modules -->
        <nav class="space-y-1 px-3 mt-8">
            <p class="px-3 text-xs font-semibold text-brand-dark/70 uppercase tracking-wider mb-2">GRC Modules (Soon)</p>
            <a href="#" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md text-brand-dark/70 cursor-not-allowed">
                <i data-lucide="list-check" class="mr-3 w-5 h-5 text-brand-dark"></i>
                Audits
            </a>
        </nav>
    </div>
    
    <div class="p-4 border-t border-brand-dark/10">
        <a href="<?= BASE_URL ?>/pages/logout.php" class="flex items-center px-3 py-2 text-sm font-medium rounded-md text-brand-dark/70 hover:bg-red-900/20 hover:text-red-300">
            <i data-lucide="log-out" mr-3 text-lg"></i>
            Logout
        </a>
    </div>
</aside>
