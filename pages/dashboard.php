<?php
require_once __DIR__ . '/../config/init.php';
requireLogin();

$pageTitle = 'Command Center Dashboard';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<div class="flex-1 flex flex-col min-w-0 overflow-hidden">
    <?php require_once __DIR__ . '/../includes/navbar.php'; ?>

    <main class="flex-1 overflow-y-auto p-6">
        <div class="max-w-7xl mx-auto space-y-6">
            
            <!-- Welcome Banner -->
            <div class=" grc-panel rounded-xl p-6 border border-brand-dark/10 shadow-lg flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-brand-dark">Welcome back, <?= htmlspecialchars($_SESSION['username'] ?? 'User') ?>!</h2>
                    <p class="text-brand-dark/70 mt-1">Here is the current security and compliance posture of your enterprise.</p>
                </div>
                <div class="hidden sm:block">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-black/5 text-brand-dark/70 border border-brand-dark/10">
                        <i class="fa-solid fa-circle text-xs mr-2 animate-pulse"></i> System Secure
                    </span>
                </div>
            </div>

            <!-- KPI Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Card 1 -->
                <div class="bg-white rounded-xl p-6 border border-brand-dark/10 shadow-sm hover:border-grc-primary transition-colors">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-brand-dark/70">Total Active Risks</p>
                            <p class="text-3xl font-bold text-brand-dark mt-1">24</p>
                        </div>
                        <div class="p-3 bg-red-900/20 rounded-lg text-brand-dark/70">
                            <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center text-sm">
                        <span class="text-brand-dark/70 flex items-center"><i class="fa-solid fa-arrow-down mr-1"></i> 12%</span>
                        <span class="text-brand-dark/70 ml-2">from last month</span>
                    </div>
                </div>

                <!-- Card 2 -->
                <div class="bg-white rounded-xl p-6 border border-brand-dark/10 shadow-sm hover:border-grc-primary transition-colors">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-brand-dark/70">Compliance Score</p>
                            <p class="text-3xl font-bold text-brand-dark mt-1">92%</p>
                        </div>
                        <div class="p-3 bg-emerald-900/20 rounded-lg text-brand-dark/70">
                            <i class="fa-solid fa-check-double class="w-5 h-5"></i>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center text-sm">
                        <span class="text-brand-dark/70 flex items-center"><i class="fa-solid fa-arrow-up mr-1"></i> 3%</span>
                        <span class="text-brand-dark/70 ml-2">from last audit</span>
                    </div>
                </div>

                <!-- Card 3 -->
                <div class="bg-white rounded-xl p-6 border border-brand-dark/10 shadow-sm hover:border-grc-primary transition-colors">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-brand-dark/70">Open Incidents</p>
                            <p class="text-3xl font-bold text-brand-dark mt-1">7</p>
                        </div>
                        <div class="p-3 bg-orange-900/20 rounded-lg text-brand-dark/70">
                            <i class="fa-solid fa-fire class="w-5 h-5"></i>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center text-sm">
                        <span class="text-brand-dark/70 flex items-center"><i class="fa-solid fa-arrow-up mr-1"></i> 2</span>
                        <span class="text-brand-dark/70 ml-2">since yesterday</span>
                    </div>
                </div>

                <!-- Card 4 -->
                <div class="bg-white rounded-xl p-6 border border-brand-dark/10 shadow-sm hover:border-grc-primary transition-colors">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-brand-dark/70">Pending Audits</p>
                            <p class="text-3xl font-bold text-brand-dark mt-1">3</p>
                        </div>
                        <div class="p-3 bg-black/5 rounded-lg text-brand-dark/70">
                            <i class="fa-solid fa-clipboard-list class="w-5 h-5"></i>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center text-sm">
                        <span class="text-brand-dark/70 flex items-center"><i class="fa-solid fa-minus mr-1"></i> 0</span>
                        <span class="text-brand-dark/70 ml-2">no change</span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Recent Activity -->
                <div class="lg:col-span-2 bg-white rounded-xl border border-brand-dark/10 shadow-sm flex flex-col">
                    <div class="p-6 border-b border-brand-dark/10 flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-brand-dark">Recent System Activity</h3>
                        <a href="#" class="text-sm text-brand-primary hover:opacity-80">View All</a>
                    </div>
                    <div class="flex-1 p-0 overflow-x-auto">
                        <table class="w-full text-sm text-left text-brand-dark">
                            <thead class="text-xs text-brand-dark/70 uppercase bg-slate-50/50 border-b border-brand-dark/10">
                                <tr>
                                    <th scope="col" class="px-6 py-3">Action</th>
                                    <th scope="col" class="px-6 py-3">User</th>
                                    <th scope="col" class="px-6 py-3">Date/Time</th>
                                    <th scope="col" class="px-6 py-3">IP Address</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Fetch recent activity logs from DB
                                try {
                                    $stmt = $pdo->query("SELECT a.*, u.username FROM activity_logs a LEFT JOIN users u ON a.user_id = u.id ORDER BY a.created_at DESC LIMIT 5");
                                    $logs = $stmt->fetchAll();
                                    
                                    if (count($logs) > 0) {
                                        foreach ($logs as $log) {
                                            echo '<tr class="border-b border-brand-dark/10 hover:bg-brand-dark/5/50">';
                                            echo '<td class="px-6 py-4">' . htmlspecialchars($log['action']) . '</td>';
                                            echo '<td class="px-6 py-4">' . htmlspecialchars($log['username'] ?? 'System') . '</td>';
                                            echo '<td class="px-6 py-4 whitespace-nowrap">' . htmlspecialchars($log['created_at']) . '</td>';
                                            echo '<td class="px-6 py-4 text-brand-dark/70">' . htmlspecialchars($log['ip_address'] ?? 'N/A') . '</td>';
                                            echo '</tr>';
                                        }
                                    } else {
                                        echo '<tr><td colspan="4" class="px-6 py-4 text-center text-brand-dark/70">No recent activity.</td></tr>';
                                    }
                                } catch (PDOException $e) {
                                    echo '<tr><td colspan="4" class="px-6 py-4 text-center text-brand-dark/70">Error loading logs.</td></tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white rounded-xl border border-brand-dark/10 shadow-sm flex flex-col">
                    <div class="p-6 border-b border-brand-dark/10">
                        <h3 class="text-lg font-semibold text-brand-dark">Quick Actions</h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <a href="<?= BASE_URL ?>/pages/users/create.php" class="flex items-center p-3 text-base font-bold text-brand-dark rounded-lg bg-slate-50 hover:bg-brand-dark/5 hover:text-brand-dark group transition-all">
                            <div class="flex items-center justify-center w-8 h-8 rounded-full bg-black/5 text-brand-primary group-hover:opacity-90 group-hover:text-brand-dark transition-colors">
                                <i data-lucide="user-plus" class="w-4 h-4"></i>
                            </div>
                            <span class="flex-1 ml-3 whitespace-nowrap">Add New User</span>
                        </a>
                        <a href="<?= BASE_URL ?>/pages/roles/create.php" class="flex items-center p-3 text-base font-bold text-brand-dark rounded-lg bg-slate-50 hover:bg-brand-dark/5 hover:text-brand-dark group transition-all">
                            <div class="flex items-center justify-center w-8 h-8 rounded-full bg-black/5 text-brand-dark/70 group-hover:bg-brand-primary group-hover:text-brand-dark transition-colors">
                                <i data-lucide="shield" class="w-4 h-4"></i>
                            </div>
                            <span class="flex-1 ml-3 whitespace-nowrap">Add New Role</span>
                        </a>
                        <a href="<?= BASE_URL ?>/pages/departments/create.php" class="flex items-center p-3 text-base font-bold text-brand-dark rounded-lg bg-slate-50 hover:bg-brand-dark/5 hover:text-brand-dark group transition-all">
                            <div class="flex items-center justify-center w-8 h-8 rounded-full bg-black/5 text-brand-dark/70 group-hover:bg-brand-primary group-hover:text-brand-dark transition-colors">
                                <i data-lucide="building" class="w-4 h-4"></i>
                            </div>
                            <span class="flex-1 ml-3 whitespace-nowrap">Add New Department</span>
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
