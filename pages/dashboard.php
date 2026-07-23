<?php
require_once __DIR__ . '/../config/init.php';
requireLogin();

$pageTitle = 'Executive Risk Dashboard';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
require_once __DIR__ . '/../modules/risks/helpers/risk_helper.php';

// 1. Data Fetching
// Total Active Risks
$totalRisks = $pdo->query("SELECT COUNT(*) FROM risks WHERE status != 'Closed'")->fetchColumn();

// Risks by Score (Active)
$risksScoreQuery = $pdo->query("SELECT id, title, risk_id, likelihood, impact, created_at, review_date FROM risks WHERE status != 'Closed'");
$activeRisks = $risksScoreQuery->fetchAll();

$criticalCount = 0;
$highCount = 0;
$mediumCount = 0;
$lowCount = 0;
$totalScore = 0;

$highestRisks = [];

foreach ($activeRisks as $risk) {
    $score = calculateRiskScore($risk['likelihood'], $risk['impact']);
    $totalScore += $score;
    $level = getRiskLevel($score);
    
    if ($level === 'Critical') $criticalCount++;
    if ($level === 'High') $highCount++;
    if ($level === 'Medium') $mediumCount++;
    if ($level === 'Low') $lowCount++;
    
    $highestRisks[] = [
        'id' => $risk['id'],
        'risk_id' => $risk['risk_id'],
        'title' => $risk['title'],
        'score' => $score,
        'level' => $level
    ];
}

$averageScore = count($activeRisks) > 0 ? round($totalScore / count($activeRisks), 1) : 0;
$overallLevel = getRiskLevel($averageScore);

// Sort highest risks
usort($highestRisks, function($a, $b) {
    return $b['score'] <=> $a['score'];
});
$topHighestRisks = array_slice($highestRisks, 0, 5);

// Due for Review
$dueForReviewStmt = $pdo->query("SELECT COUNT(*) FROM risks WHERE status != 'Closed' AND review_date IS NOT NULL AND review_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)");
$dueForReview = $dueForReviewStmt->fetchColumn();

// Recent Risks
$recentRisks = $pdo->query("SELECT id, risk_id, title, likelihood, impact, created_at FROM risks ORDER BY created_at DESC LIMIT 5")->fetchAll();

// Upcoming Reviews
$upcomingReviews = $pdo->query("SELECT id, risk_id, title, review_date FROM risks WHERE status != 'Closed' AND review_date IS NOT NULL ORDER BY review_date ASC LIMIT 5")->fetchAll();

// Risks by Department
$deptRisks = $pdo->query("
    SELECT d.name, COUNT(r.id) as count 
    FROM risks r 
    JOIN departments d ON r.department_id = d.id 
    WHERE r.status != 'Closed' 
    GROUP BY d.id 
    ORDER BY count DESC
")->fetchAll();

// Closed Risks Fetching
$closedRisks = $pdo->query("SELECT r.id, r.risk_id, r.title, r.updated_at, r.treatment_strategy, u.username as owner_name FROM risks r LEFT JOIN users u ON r.owner_id = u.id WHERE r.status = 'Closed' ORDER BY r.updated_at DESC LIMIT 5")->fetchAll();
$totalClosed = $pdo->query("SELECT COUNT(*) FROM risks WHERE status = 'Closed'")->fetchColumn();

// Chart Data Prep
$chartDeptNames = [];
$chartDeptCounts = [];
foreach($deptRisks as $dr) {
    $chartDeptNames[] = $dr['name'];
    $chartDeptCounts[] = $dr['count'];
}
?>

<div class="flex-1 flex flex-col min-w-0 overflow-hidden">
    <?php require_once __DIR__ . '/../includes/navbar.php'; ?>

    <main class="flex-1 overflow-y-auto p-6 bg-[#FFFAF3]">
        <div class="max-w-7xl mx-auto space-y-6">
            
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
                <div>
                    <h2 class="text-2xl font-bold text-[#232426]">Executive Risk Analytics</h2>
                    <p class="text-[#232426]/70 mt-1">Real-time overview of the enterprise risk posture.</p>
                </div>
                
                <!-- Enterprise Risk Score -->
                <div class="mt-4 md:mt-0 flex items-center bg-white px-5 py-3 rounded-xl border border-[#232426]/10 shadow-sm">
                    <div class="mr-4">
                        <p class="text-[10px] font-bold text-[#232426]/50 uppercase tracking-wider">Enterprise Risk Score</p>
                        <p class="text-xs font-semibold text-[#232426]/80 text-right">Avg: <?= $averageScore ?></p>
                    </div>
                    <?php $ovClasses = getRiskScoreBadgeClasses($averageScore); ?>
                    <div class="px-4 py-1.5 <?= $ovClasses ?> rounded-lg font-bold border shadow-sm flex items-center justify-center">
                        <span class="text-sm uppercase tracking-wider"><?= $overallLevel ?></span>
                    </div>
                </div>
            </div>

            <!-- KPI Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Total Risks -->
                <div class="bg-white rounded-xl p-5 border border-[#232426]/10 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-[11px] font-bold text-[#232426]/60 uppercase tracking-wider">Active Risks</p>
                            <p class="text-3xl font-bold text-[#232426] mt-1"><?= $totalRisks ?></p>
                        </div>
                        <div class="p-3 bg-blue-50 text-blue-600 rounded-xl">
                            <i data-lucide="activity" class="w-6 h-6"></i>
                        </div>
                    </div>
                </div>

                <!-- Critical Risks -->
                <div class="bg-white rounded-xl p-5 border border-red-200 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-1 h-full bg-red-500"></div>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-[11px] font-bold text-red-800/70 uppercase tracking-wider">Critical Risks</p>
                            <p class="text-3xl font-bold text-red-600 mt-1"><?= $criticalCount ?></p>
                        </div>
                        <div class="p-3 bg-red-50 text-red-600 rounded-xl">
                            <i data-lucide="alert-octagon" class="w-6 h-6"></i>
                        </div>
                    </div>
                </div>

                <!-- High Risks -->
                <div class="bg-white rounded-xl p-5 border border-orange-200 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-1 h-full bg-orange-400"></div>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-[11px] font-bold text-orange-800/70 uppercase tracking-wider">High Risks</p>
                            <p class="text-3xl font-bold text-orange-600 mt-1"><?= $highCount ?></p>
                        </div>
                        <div class="p-3 bg-orange-50 text-orange-600 rounded-xl">
                            <i data-lucide="alert-triangle" class="w-6 h-6"></i>
                        </div>
                    </div>
                </div>

                <!-- Due for Review -->
                <div class="bg-white rounded-xl p-5 border border-[#232426]/10 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-[11px] font-bold text-[#232426]/60 uppercase tracking-wider">Due for Review</p>
                            <p class="text-3xl font-bold text-[#232426] mt-1"><?= $dueForReview ?></p>
                        </div>
                        <div class="p-3 bg-[#C9AEF1]/20 text-[#232426] rounded-xl">
                            <i data-lucide="clock" class="w-6 h-6"></i>
                        </div>
                    </div>
                    <p class="text-xs text-[#232426]/50 mt-2 font-medium">Within next 30 days</p>
                </div>
            </div>

            <!-- Dashboard Main Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- Left Column (Wider) -->
                <div class="lg:col-span-2 space-y-6">
                    
                    <!-- Highest Risk Items -->
                    <div class="bg-white rounded-xl border border-[#232426]/10 shadow-sm">
                        <div class="p-5 border-b border-[#232426]/10 flex justify-between items-center">
                            <h3 class="text-lg font-bold text-[#232426] flex items-center">
                                <i data-lucide="trending-up" class="w-5 h-5 mr-2 text-[#EF6351]"></i> Highest Risk Items
                            </h3>
                        </div>
                        <div class="p-0 overflow-x-auto">
                            <table class="w-full text-sm text-left text-[#232426]">
                                <thead class="text-xs text-[#232426]/70 uppercase bg-slate-50 border-b border-[#232426]/10">
                                    <tr>
                                        <th scope="col" class="px-5 py-3 font-semibold">Risk ID</th>
                                        <th scope="col" class="px-5 py-3 font-semibold">Title</th>
                                        <th scope="col" class="px-5 py-3 font-semibold text-center">Score</th>
                                        <th scope="col" class="px-5 py-3 font-semibold text-center">Level</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <?php if(empty($topHighestRisks)): ?>
                                        <tr><td colspan="4" class="px-5 py-4 text-center text-[#232426]/50">No active risks found.</td></tr>
                                    <?php else: ?>
                                        <?php foreach($topHighestRisks as $hr): 
                                            $bc = getRiskScoreBadgeClasses($hr['score']);
                                        ?>
                                        <tr class="hover:bg-slate-50 transition-colors group">
                                            <td class="px-5 py-4 font-bold text-[#232426] group-hover:text-[#EF6351] transition-colors whitespace-nowrap">
                                                <a href="<?= BASE_URL ?>/modules/risks/view.php?id=<?= $hr['id'] ?>"><?= htmlspecialchars($hr['risk_id']) ?></a>
                                            </td>
                                            <td class="px-5 py-4 truncate max-w-[200px]" title="<?= htmlspecialchars($hr['title']) ?>">
                                                <?= htmlspecialchars($hr['title']) ?>
                                            </td>
                                            <td class="px-5 py-4 text-center font-bold text-lg"><?= $hr['score'] ?></td>
                                            <td class="px-5 py-4 text-center">
                                                <span class="px-2.5 py-1 rounded-full text-xs font-bold border <?= $bc ?>">
                                                    <?= $hr['level'] ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Closed Risks Section (Archived/Resolved) -->
                    <div class="bg-white rounded-xl border border-[#BBC7B6]/50 shadow-sm relative overflow-hidden">
                        <div class="absolute top-0 left-0 w-1 h-full bg-[#BBC7B6]"></div>
                        <div class="p-5 border-b border-[#232426]/10 flex justify-between items-center bg-[#BBC7B6]/5">
                            <h3 class="text-lg font-bold text-[#232426] flex items-center">
                                <i data-lucide="check-circle" class="w-5 h-5 mr-2 text-[#BBC7B6]"></i> Resolved & Closed Risks
                            </h3>
                            <span class="bg-[#BBC7B6]/30 text-[#232426] px-2.5 py-1 rounded-full text-xs font-bold border border-[#BBC7B6]/50">
                                <?= $totalClosed ?> Total Closed
                            </span>
                        </div>
                        <div class="p-0 overflow-x-auto">
                            <table class="w-full text-sm text-left text-[#232426]">
                                <thead class="text-xs text-[#232426]/70 uppercase bg-slate-50 border-b border-[#232426]/10">
                                    <tr>
                                        <th scope="col" class="px-5 py-3 font-semibold">Risk ID</th>
                                        <th scope="col" class="px-5 py-3 font-semibold">Title</th>
                                        <th scope="col" class="px-5 py-3 font-semibold">Strategy Used</th>
                                        <th scope="col" class="px-5 py-3 font-semibold text-right">Closed On</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <?php if(empty($closedRisks)): ?>
                                        <tr><td colspan="4" class="px-5 py-8 text-center text-[#232426]/50">
                                            <i data-lucide="shield-check" class="w-8 h-8 mx-auto mb-2 text-[#BBC7B6]/50"></i>
                                            <p>No risks have been closed yet.</p>
                                        </td></tr>
                                    <?php else: ?>
                                        <?php foreach($closedRisks as $cr): ?>
                                        <tr class="hover:bg-slate-50 transition-colors group">
                                            <td class="px-5 py-4 font-bold text-[#232426]/60 group-hover:text-[#EF6351] transition-colors whitespace-nowrap line-through decoration-[#BBC7B6]">
                                                <a href="<?= BASE_URL ?>/modules/risks/view.php?id=<?= $cr['id'] ?>"><?= htmlspecialchars($cr['risk_id']) ?></a>
                                            </td>
                                            <td class="px-5 py-4 text-[#232426]/80 truncate max-w-[200px]" title="<?= htmlspecialchars($cr['title']) ?>">
                                                <?= htmlspecialchars($cr['title']) ?>
                                            </td>
                                            <td class="px-5 py-4 text-[#232426]/70">
                                                <?= htmlspecialchars($cr['treatment_strategy']) ?>
                                            </td>
                                            <td class="px-5 py-4 text-right text-xs text-[#232426]/60 font-medium whitespace-nowrap">
                                                <?= date('M d, Y', strtotime($cr['updated_at'])) ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                            <?php if(!empty($closedRisks)): ?>
                            <div class="p-3 border-t border-[#232426]/10 bg-slate-50 text-center">
                                <a href="<?= BASE_URL ?>/modules/risks/index.php?status=Closed" class="text-sm font-semibold text-[#EF6351] hover:text-[#232426] transition-colors">View All Closed Risks &rarr;</a>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Charts Row -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-white rounded-xl border border-[#232426]/10 shadow-sm p-5">
                            <h3 class="text-sm font-bold text-[#232426]/70 uppercase tracking-wider mb-4">Active Risks by Level</h3>
                            <div class="relative h-48 w-full flex justify-center">
                                <canvas id="levelChart"></canvas>
                            </div>
                        </div>
                        <div class="bg-white rounded-xl border border-[#232426]/10 shadow-sm p-5">
                            <h3 class="text-sm font-bold text-[#232426]/70 uppercase tracking-wider mb-4">Active Risks by Department</h3>
                            <div class="relative h-48 w-full flex justify-center">
                                <canvas id="deptChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column (Sidebar) -->
                <div class="space-y-6">
                    
                    <!-- Quick Actions -->
                    <div class="bg-white rounded-xl border border-[#232426]/10 shadow-sm">
                        <div class="p-5 border-b border-[#232426]/10">
                            <h3 class="text-lg font-bold text-[#232426] flex items-center">
                                <i data-lucide="zap" class="w-5 h-5 mr-2 text-[#EF6351]"></i> Quick Actions
                            </h3>
                        </div>
                        <div class="p-5 space-y-3">
                            <a href="<?= BASE_URL ?>/modules/risks/create.php" class="flex items-center p-3 text-sm font-bold text-[#232426] rounded-lg bg-slate-50 hover:bg-[#EF6351] hover:text-white group transition-all border border-slate-100 shadow-sm">
                                <div class="flex items-center justify-center w-8 h-8 rounded-full bg-white text-[#EF6351] group-hover:text-[#EF6351] transition-colors shadow-sm">
                                    <i data-lucide="plus" class="w-4 h-4"></i>
                                </div>
                                <span class="flex-1 ml-3 whitespace-nowrap">Create Risk</span>
                            </a>
                            <a href="<?= BASE_URL ?>/modules/risks/index.php" class="flex items-center p-3 text-sm font-bold text-[#232426] rounded-lg bg-slate-50 hover:bg-[#C9AEF1] hover:text-[#232426] group transition-all border border-slate-100 shadow-sm">
                                <div class="flex items-center justify-center w-8 h-8 rounded-full bg-white text-[#C9AEF1] transition-colors shadow-sm">
                                    <i data-lucide="layout-list" class="w-4 h-4"></i>
                                </div>
                                <span class="flex-1 ml-3 whitespace-nowrap">View Risk Register</span>
                            </a>
                            <a href="<?= BASE_URL ?>/pages/departments/index.php" class="flex items-center p-3 text-sm font-bold text-[#232426] rounded-lg bg-slate-50 hover:bg-[#232426] hover:text-white group transition-all border border-slate-100 shadow-sm">
                                <div class="flex items-center justify-center w-8 h-8 rounded-full bg-white text-[#232426] transition-colors shadow-sm">
                                    <i data-lucide="building" class="w-4 h-4"></i>
                                </div>
                                <span class="flex-1 ml-3 whitespace-nowrap">Manage Departments</span>
                            </a>
                        </div>
                    </div>

                    <!-- Upcoming Reviews -->
                    <div class="bg-white rounded-xl border border-[#232426]/10 shadow-sm">
                        <div class="p-5 border-b border-[#232426]/10">
                            <h3 class="text-lg font-bold text-[#232426] flex items-center">
                                <i data-lucide="calendar-clock" class="w-5 h-5 mr-2 text-[#EF6351]"></i> Upcoming Reviews
                            </h3>
                        </div>
                        <div class="p-0">
                            <ul class="divide-y divide-slate-100">
                                <?php if(empty($upcomingReviews)): ?>
                                    <li class="p-5 text-center text-sm text-[#232426]/50 font-medium">No upcoming reviews.</li>
                                <?php else: ?>
                                    <?php foreach($upcomingReviews as $ur): 
                                        $isOverdue = strtotime($ur['review_date']) < time();
                                    ?>
                                    <li class="p-4 hover:bg-slate-50 transition-colors">
                                        <div class="flex justify-between items-start">
                                            <div class="pr-2">
                                                <a href="<?= BASE_URL ?>/modules/risks/view.php?id=<?= $ur['id'] ?>" class="text-sm font-bold text-[#232426] hover:text-[#EF6351] transition-colors line-clamp-1 block mb-1">
                                                    <?= htmlspecialchars($ur['risk_id']) ?>: <?= htmlspecialchars($ur['title']) ?>
                                                </a>
                                                <span class="text-xs font-semibold <?= $isOverdue ? 'text-red-600 bg-red-50 px-2 py-0.5 rounded border border-red-200' : 'text-[#232426]/60' ?>">
                                                    <?= $isOverdue ? 'Overdue: ' : 'Due: ' ?><?= date('M d, Y', strtotime($ur['review_date'])) ?>
                                                </span>
                                            </div>
                                        </div>
                                    </li>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>

                    <!-- Recent Risks -->
                    <div class="bg-white rounded-xl border border-[#232426]/10 shadow-sm">
                        <div class="p-5 border-b border-[#232426]/10">
                            <h3 class="text-lg font-bold text-[#232426] flex items-center">
                                <i data-lucide="clock" class="w-5 h-5 mr-2 text-[#EF6351]"></i> Recently Added
                            </h3>
                        </div>
                        <div class="p-0">
                            <ul class="divide-y divide-slate-100">
                                <?php if(empty($recentRisks)): ?>
                                    <li class="p-5 text-center text-sm text-[#232426]/50 font-medium">No recent risks.</li>
                                <?php else: ?>
                                    <?php foreach($recentRisks as $rr): 
                                        $sc = calculateRiskScore($rr['likelihood'], $rr['impact']);
                                        $lv = getRiskLevel($sc);
                                    ?>
                                    <li class="p-4 hover:bg-slate-50 transition-colors flex items-center justify-between">
                                        <div class="pr-3 flex-1">
                                            <a href="<?= BASE_URL ?>/modules/risks/view.php?id=<?= $rr['id'] ?>" class="text-sm font-bold text-[#232426] hover:text-[#EF6351] transition-colors line-clamp-1 block mb-1">
                                                <?= htmlspecialchars($rr['risk_id']) ?>
                                            </a>
                                            <span class="text-[10px] font-bold text-[#232426]/50 uppercase tracking-wider block">
                                                <?= date('M d', strtotime($rr['created_at'])) ?>
                                            </span>
                                        </div>
                                        <?php $bc = getRiskScoreBadgeClasses($sc); ?>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold border <?= $bc ?>">
                                            <?= $lv ?>
                                        </span>
                                    </li>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </main>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Risks by Level Chart
    const ctxLevel = document.getElementById('levelChart').getContext('2d');
    new Chart(ctxLevel, {
        type: 'doughnut',
        data: {
            labels: ['Critical', 'High', 'Medium', 'Low'],
            datasets: [{
                data: [<?= $criticalCount ?>, <?= $highCount ?>, <?= $mediumCount ?>, <?= $lowCount ?>],
                backgroundColor: [
                    '#ef4444', // red-500
                    '#f97316', // orange-500
                    '#eab308', // yellow-500
                    '#22c55e'  // green-500
                ],
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: {
                legend: {
                    position: 'right',
                    labels: {
                        font: { size: 11, family: "'Inter', sans-serif", weight: 'bold' },
                        color: '#232426',
                        usePointStyle: true,
                        padding: 15
                    }
                }
            }
        }
    });

    // Risks by Department Chart
    const ctxDept = document.getElementById('deptChart').getContext('2d');
    new Chart(ctxDept, {
        type: 'bar',
        data: {
            labels: <?= json_encode($chartDeptNames) ?>,
            datasets: [{
                label: 'Total Risks',
                data: <?= json_encode($chartDeptCounts) ?>,
                backgroundColor: '#EF6351',
                borderRadius: 4,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { precision: 0, font: { size: 10, family: "'Inter', sans-serif" } },
                    grid: { color: '#f1f5f9' }
                },
                x: {
                    ticks: { font: { size: 10, family: "'Inter', sans-serif" } },
                    grid: { display: false }
                }
            }
        }
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
