<?php
require_once __DIR__ . '/../config/init.php';
requireLogin();

// Simple pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

$totalStmt = $pdo->query("SELECT COUNT(*) FROM activity_logs");
$totalLogs = $totalStmt->fetchColumn();
$totalPages = ceil($totalLogs / $limit);

$stmt = $pdo->prepare("
    SELECT a.*, u.username 
    FROM activity_logs a 
    LEFT JOIN users u ON a.user_id = u.id 
    ORDER BY a.created_at DESC 
    LIMIT ? OFFSET ?
");
$stmt->bindValue(1, $limit, PDO::PARAM_INT);
$stmt->bindValue(2, $offset, PDO::PARAM_INT);
$stmt->execute();
$logs = $stmt->fetchAll();

$pageTitle = 'Activity Logs';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<div class="flex-1 flex flex-col min-w-0 overflow-hidden">
    <?php require_once __DIR__ . '/../includes/navbar.php'; ?>

    <main class="flex-1 overflow-y-auto p-6 bg-[#FFFAF3]">
        <div class="max-w-7xl mx-auto space-y-6">
            
            <div class="flex justify-between items-end">
                <div>
                    <h2 class="text-2xl font-bold text-[#232426]">System Activity Logs</h2>
                    <p class="text-[#232426]/70 mt-1">Audit trail of all enterprise risk management events.</p>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-[#232426]/10 shadow-sm flex flex-col">
                <div class="p-0 overflow-x-auto">
                    <table class="w-full text-sm text-left text-[#232426]">
                        <thead class="text-xs text-[#232426]/70 uppercase bg-slate-50 border-b border-[#232426]/10">
                            <tr>
                                <th scope="col" class="px-6 py-4 font-semibold w-48">Timestamp</th>
                                <th scope="col" class="px-6 py-4 font-semibold w-40">User</th>
                                <th scope="col" class="px-6 py-4 font-semibold">Action / Description</th>
                                <th scope="col" class="px-6 py-4 font-semibold w-32">IP Address</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php if (empty($logs)): ?>
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center text-[#232426]/50">
                                        <i data-lucide="activity" class="w-12 h-12 mx-auto mb-3 text-[#232426]/20"></i>
                                        <p class="text-base font-semibold">No activity logs found.</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($logs as $log): 
                                    // Try to parse the module pattern from the action string "[Module] Action"
                                    $actionStr = htmlspecialchars($log['action']);
                                    $moduleMatch = [];
                                    $module = 'System';
                                    if(preg_match('/^\[(.*?)\] (.*)$/', $actionStr, $moduleMatch)) {
                                        $module = $moduleMatch[1];
                                        $actionStr = $moduleMatch[2];
                                    }
                                ?>
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap text-xs font-medium text-[#232426]/60">
                                            <?= date('M d, Y h:i:s A', strtotime($log['created_at'])) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap font-bold text-[#232426]">
                                            <?= htmlspecialchars($log['username'] ?? 'System') ?>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-start">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-[#232426]/5 text-[#232426]/70 border border-[#232426]/10 mr-2 mt-0.5 uppercase tracking-wider">
                                                    <?= $module ?>
                                                </span>
                                                <span class="text-[#232426]"><?= $actionStr ?></span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-xs font-mono text-[#232426]/50">
                                            <?= htmlspecialchars($log['ip_address'] ?? 'N/A') ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php if ($totalPages > 1): ?>
                <div class="p-4 border-t border-[#232426]/10 flex items-center justify-between bg-white rounded-b-xl">
                    <span class="text-sm text-[#232426]/70">
                        Showing <?= $offset + 1 ?> to <?= min($offset + $limit, $totalLogs) ?> of <?= $totalLogs ?> logs
                    </span>
                    <div class="inline-flex shadow-sm rounded-lg border border-[#232426]/10 overflow-hidden">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?= $page - 1 ?>" class="px-4 py-2 bg-white text-[#232426] hover:bg-slate-50 text-sm font-medium border-r border-[#232426]/10 transition-colors">Previous</a>
                        <?php else: ?>
                            <span class="px-4 py-2 bg-slate-50 text-[#232426]/30 text-sm font-medium border-r border-[#232426]/10">Previous</span>
                        <?php endif; ?>
                        
                        <?php if ($page < $totalPages): ?>
                            <a href="?page=<?= $page + 1 ?>" class="px-4 py-2 bg-white text-[#232426] hover:bg-slate-50 text-sm font-medium transition-colors">Next</a>
                        <?php else: ?>
                            <span class="px-4 py-2 bg-slate-50 text-[#232426]/30 text-sm font-medium">Next</span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
