<?php
// Fetch Notifications Data
$notifications = [];

try {
    // 1. Critical Risks
    $critStmt = $pdo->query("SELECT id, risk_id, title, created_at FROM risks WHERE status != 'Closed' AND (likelihood * impact) >= 16 ORDER BY created_at DESC LIMIT 3");
    $critRisks = $critStmt->fetchAll();
    foreach($critRisks as $cr) {
        $notifications[] = [
            'type' => 'critical',
            'icon' => 'alert-octagon',
            'icon_color' => 'text-red-500',
            'bg_color' => 'bg-red-50',
            'title' => 'Critical Risk Identified',
            'message' => htmlspecialchars($cr['risk_id']) . ' has a critical risk score.',
            'time' => $cr['created_at'],
            'link' => BASE_URL . '/modules/risks/view.php?id=' . $cr['id']
        ];
    }
    
    // 2. Upcoming Reviews
    $revStmt = $pdo->query("SELECT id, risk_id, title, review_date FROM risks WHERE status != 'Closed' AND review_date IS NOT NULL AND review_date <= DATE_ADD(CURDATE(), INTERVAL 14 DAY) ORDER BY review_date ASC LIMIT 3");
    $revRisks = $revStmt->fetchAll();
    foreach($revRisks as $rr) {
        $isOverdue = strtotime($rr['review_date']) < time();
        $notifications[] = [
            'type' => 'review',
            'icon' => 'calendar-clock',
            'icon_color' => $isOverdue ? 'text-red-500' : 'text-orange-500',
            'bg_color' => $isOverdue ? 'bg-red-50' : 'bg-orange-50',
            'title' => $isOverdue ? 'Overdue Review' : 'Upcoming Review',
            'message' => htmlspecialchars($rr['risk_id']) . ' is ' . ($isOverdue ? 'overdue for review.' : 'due for review soon.'),
            'time' => $rr['review_date'],
            'link' => BASE_URL . '/modules/risks/view.php?id=' . $rr['id']
        ];
    }
    
    // 3. Recently Updated
    $updStmt = $pdo->query("SELECT id, risk_id, updated_at FROM risks WHERE status != 'Closed' ORDER BY updated_at DESC LIMIT 2");
    $updRisks = $updStmt->fetchAll();
    foreach($updRisks as $ur) {
        $notifications[] = [
            'type' => 'update',
            'icon' => 'refresh-cw',
            'icon_color' => 'text-blue-500',
            'bg_color' => 'bg-blue-50',
            'title' => 'Risk Updated',
            'message' => htmlspecialchars($ur['risk_id']) . ' was recently updated.',
            'time' => $ur['updated_at'],
            'link' => BASE_URL . '/modules/risks/view.php?id=' . $ur['id']
        ];
    }
    
    // Sort notifications by time descending
    usort($notifications, function($a, $b) {
        return strtotime($b['time']) <=> strtotime($a['time']);
    });
    
    // Limit to 5 total in dropdown
    $notifications = array_slice($notifications, 0, 5);
} catch (Exception $e) {
    // Fail silently for notifications if DB schema isn't ready
}
?>
<header class="bg-brand-dark h-16 flex items-center justify-between px-6 border-b border-white/10 sticky top-0 z-40 shadow-sm">
    <div class="flex items-center md:hidden">
        <button class="text-white/70 hover:text-white focus:outline-none">
            <i data-lucide="menu" class="w-6 h-6"></i>
        </button>
        <span class="ml-3 text-lg font-bold text-white">GRC Center</span>
    </div>
    
    <!-- Search / Breadcrumbs -->
    <div class="hidden md:block flex-1">
        <h1 class="text-xl font-semibold text-white"><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?></h1>
    </div>

    <!-- Right Navigation -->
    <div class="flex items-center space-x-4">
        
        <!-- Notifications Dropdown -->
        <div class="relative group">
            <button class="text-white/70 hover:text-white relative focus:outline-none py-2 px-1 rounded-lg hover:bg-white/5 transition-colors">
                <i data-lucide="bell" class="w-5 h-5"></i>
                <?php if(count($notifications) > 0): ?>
                <span class="absolute top-1 right-0 -mt-1 -mr-1 flex h-3 w-3">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#EF6351] opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-[#EF6351]"></span>
                </span>
                <?php endif; ?>
            </button>
            
            <div class="absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-xl border border-brand-dark/10 hidden group-hover:block z-50 overflow-hidden origin-top-right transform transition-all duration-200">
                <div class="px-4 py-3 border-b border-brand-dark/5 flex justify-between items-center bg-slate-50">
                    <h3 class="text-sm font-bold text-brand-dark">Notifications</h3>
                    <span class="text-xs bg-brand-dark text-white px-2 py-0.5 rounded-full"><?= count($notifications) ?> New</span>
                </div>
                <div class="max-h-96 overflow-y-auto">
                    <?php if(empty($notifications)): ?>
                        <div class="px-4 py-8 text-center text-brand-dark/50">
                            <i data-lucide="bell-off" class="w-8 h-8 mx-auto mb-2 text-brand-dark/20"></i>
                            <p class="text-sm">You're all caught up!</p>
                        </div>
                    <?php else: ?>
                        <?php foreach($notifications as $n): ?>
                        <a href="<?= $n['link'] ?>" class="flex items-start px-4 py-3 hover:bg-slate-50 transition-colors border-b border-brand-dark/5 last:border-0 group/item">
                            <div class="flex-shrink-0 mt-0.5">
                                <div class="w-8 h-8 rounded-full <?= $n['bg_color'] ?> flex items-center justify-center">
                                    <i data-lucide="<?= $n['icon'] ?>" class="w-4 h-4 <?= $n['icon_color'] ?>"></i>
                                </div>
                            </div>
                            <div class="ml-3 w-0 flex-1">
                                <p class="text-sm font-bold text-brand-dark group-hover/item:text-brand-primary transition-colors"><?= htmlspecialchars($n['title']) ?></p>
                                <p class="text-xs text-brand-dark/70 mt-0.5 leading-tight"><?= htmlspecialchars($n['message']) ?></p>
                                <p class="text-[10px] text-brand-dark/40 mt-1 font-medium"><?= date('M d, Y h:i A', strtotime($n['time'])) ?></p>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <a href="#" class="block bg-slate-50 text-center py-2 text-xs font-bold text-brand-primary hover:text-brand-dark transition-colors border-t border-brand-dark/5">
                    View All Notifications
                </a>
            </div>
        </div>
        
        <!-- User Profile Dropdown -->
        <div class="relative group">
            <button class="flex items-center space-x-3 focus:outline-none hover:bg-white/5 py-1 px-2 rounded-lg transition-colors">
                <div class="h-9 w-9 rounded-full bg-brand-primary flex items-center justify-center text-white font-bold border border-white/20 shadow-sm">
                    <i data-lucide="user" class="w-4 h-4"></i>
                </div>
                <div class="hidden md:block text-left">
                    <p class="text-sm font-medium text-white"><?= htmlspecialchars($_SESSION['username'] ?? 'User') ?></p>
                    <p class="text-xs text-white/50"><?= htmlspecialchars($_SESSION['role_name'] ?? 'Role') ?></p>
                </div>
                <i data-lucide="chevron-down" class="w-4 h-4 text-white/50"></i>
            </button>
            
            <div class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-xl border border-brand-dark/10 hidden group-hover:block z-50 overflow-hidden origin-top-right transform transition-all duration-200">
                <div class="px-4 py-3 border-b border-brand-dark/5 bg-slate-50">
                    <p class="text-xs text-brand-dark/50 font-semibold uppercase tracking-wider mb-1">Signed in as</p>
                    <p class="text-sm font-bold text-brand-dark truncate"><?= htmlspecialchars($_SESSION['username'] ?? 'User') ?></p>
                </div>
                <div class="py-1">
                    <a href="<?= BASE_URL ?>/pages/settings.php" class="flex items-center px-4 py-2 text-sm text-brand-dark hover:bg-slate-50 hover:text-brand-primary transition-colors">
                        <i data-lucide="settings" class="w-4 h-4 mr-2 text-brand-dark/50"></i> Settings
                    </a>
                    <a href="<?= BASE_URL ?>/pages/activity_logs.php" class="flex items-center px-4 py-2 text-sm text-brand-dark hover:bg-slate-50 hover:text-brand-primary transition-colors">
                        <i data-lucide="activity" class="w-4 h-4 mr-2 text-brand-dark/50"></i> Activity Logs
                    </a>
                </div>
                <div class="border-t border-brand-dark/5 py-1">
                    <a href="<?= BASE_URL ?>/pages/logout.php" class="flex items-center px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-50 transition-colors">
                        <i data-lucide="log-out" class="w-4 h-4 mr-2"></i> Sign out
                    </a>
                </div>
            </div>
        </div>
        
    </div>
</header>
