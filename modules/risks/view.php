<?php
require_once __DIR__ . '/../../config/init.php';
requireLogin();
require_once __DIR__ . '/helpers/risk_helper.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "Invalid risk ID.";
    header("Location: " . BASE_URL . "/modules/risks/index.php");
    exit;
}

$id = (int)$_GET['id'];

$stmt = $pdo->prepare("
    SELECT r.*, c.category_name, d.name as department_name, u.username as owner_name
    FROM risks r
    LEFT JOIN risk_categories c ON r.category_id = c.id
    LEFT JOIN departments d ON r.department_id = d.id
    LEFT JOIN users u ON r.owner_id = u.id
    WHERE r.id = ?
");
$stmt->execute([$id]);
$risk = $stmt->fetch();

if (!$risk) {
    $_SESSION['error'] = "Risk not found.";
    header("Location: " . BASE_URL . "/modules/risks/index.php");
    exit;
}

$pageTitle = 'Risk Profile: ' . htmlspecialchars($risk['risk_id']);
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';

$riskScore = calculateRiskScore($risk['likelihood'], $risk['impact']);
$riskLevel = getRiskLevel($riskScore);
$riskScoreBadgeClasses = getRiskScoreBadgeClasses($riskScore);
$recommendedTreatment = getRecommendedTreatment($riskScore);
?>

<div class="flex-1 flex flex-col min-w-0 overflow-hidden">
    <?php require_once __DIR__ . '/../../includes/navbar.php'; ?>

    <main class="flex-1 overflow-y-auto p-6 bg-[#FFFAF3]">
        <div class="max-w-7xl mx-auto space-y-6">
            
            <!-- Breadcrumb -->
            <nav class="text-sm font-medium text-brand-dark/50" aria-label="Breadcrumb">
                <ol class="list-none p-0 inline-flex items-center">
                    <li class="flex items-center">
                        <a href="<?= BASE_URL ?>/pages/dashboard.php" class="hover:text-brand-primary transition-colors">Dashboard</a>
                        <i data-lucide="chevron-right" class="w-4 h-4 mx-2"></i>
                    </li>
                    <li class="flex items-center">
                        <a href="<?= BASE_URL ?>/modules/risks/index.php" class="hover:text-brand-primary transition-colors">Risk Register</a>
                        <i data-lucide="chevron-right" class="w-4 h-4 mx-2"></i>
                    </li>
                    <li class="flex items-center text-[#232426]">
                        Risk Profile
                    </li>
                </ol>
            </nav>

            <!-- 1. Risk Header Section -->
            <div class="bg-white rounded-xl border border-[#232426]/10 shadow-sm p-6 hover:shadow-md transition-shadow">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-2">
                            <span class="px-2.5 py-1 text-xs font-bold bg-slate-100 text-[#232426]/70 rounded-md border border-slate-200"><?= htmlspecialchars($risk['risk_id']) ?></span>
                            <?php 
                                $statusClass = 'bg-slate-100 text-slate-800 border-slate-200';
                                if ($risk['status'] === 'Open') $statusClass = 'bg-blue-100 text-blue-800 border-blue-200';
                                if ($risk['status'] === 'In Progress') $statusClass = 'bg-yellow-100 text-yellow-800 border-yellow-200';
                                if ($risk['status'] === 'Closed') $statusClass = 'bg-[#BBC7B6]/30 text-[#232426] border-[#BBC7B6]/50';
                            ?>
                            <span class="px-3 py-1 <?= $statusClass ?> text-xs font-bold rounded-full border shadow-sm">
                                <?= htmlspecialchars($risk['status']) ?>
                            </span>
                        </div>
                        <h2 class="text-3xl font-bold text-[#232426] tracking-tight line-clamp-2"><?= htmlspecialchars($risk['title']) ?></h2>
                    </div>
                    <div class="flex flex-col md:flex-row items-start md:items-center gap-4">
                        <div class="flex flex-col text-right hidden md:flex">
                            <span class="text-xs text-[#232426]/60 font-medium">Created: <?= date('M d, Y', strtotime($risk['created_at'])) ?></span>
                            <span class="text-xs text-[#232426]/60 font-medium">Updated: <?= date('M d, Y', strtotime($risk['updated_at'])) ?></span>
                        </div>
                        <div class="px-5 py-2 <?= $riskScoreBadgeClasses ?> rounded-xl font-bold border shadow-sm flex flex-col items-center justify-center min-w-[100px]">
                            <span class="text-2xl leading-none mb-1"><?= $riskScore ?></span>
                            <span class="text-[10px] uppercase tracking-wider opacity-80"><?= $riskLevel ?></span>
                        </div>
                        <a href="<?= BASE_URL ?>/modules/risks/edit.php?id=<?= $risk['id'] ?>" class="bg-white border border-[#232426]/20 hover:bg-slate-50 text-[#232426] hover:text-[#EF6351] px-4 py-2 rounded-lg text-sm font-medium transition-all shadow-sm flex items-center h-full">
                            <i data-lucide="edit" class="w-4 h-4 mr-2"></i> Edit
                        </a>
                    </div>
                </div>
                <div class="flex md:hidden mt-4 pt-4 border-t border-[#232426]/10 flex-col gap-1 text-xs text-[#232426]/60 font-medium">
                    <span>Created: <?= date('M d, Y', strtotime($risk['created_at'])) ?></span>
                    <span>Updated: <?= date('M d, Y', strtotime($risk['updated_at'])) ?></span>
                </div>
            </div>

            <!-- Two Column Layout -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- Main Content Column -->
                <div class="lg:col-span-2 space-y-6">
                    
                    <!-- 2. Overview Section -->
                    <div class="bg-white rounded-xl border border-[#232426]/10 shadow-sm p-6 hover:shadow-md transition-shadow">
                        <h3 class="text-lg font-bold text-[#232426] mb-5 pb-3 border-b border-[#232426]/10 flex items-center">
                            <i data-lucide="layout-grid" class="w-5 h-5 mr-2 text-[#EF6351]"></i> Risk Overview
                        </h3>
                        <div class="space-y-6">
                            <div>
                                <h4 class="text-xs font-semibold text-[#232426]/60 uppercase tracking-wider mb-2">Description & Business Context</h4>
                                <div class="bg-slate-50 p-4 rounded-lg border border-slate-100 text-[#232426] text-sm leading-relaxed whitespace-pre-line">
                                    <?= htmlspecialchars($risk['description'] ?? 'No description provided.') ?>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div class="p-3 bg-white border border-[#232426]/10 rounded-lg shadow-sm">
                                    <span class="block text-[10px] font-bold text-[#232426]/50 uppercase tracking-wider mb-1">Category</span>
                                    <span class="text-sm font-semibold text-[#232426] flex items-center">
                                        <i data-lucide="tag" class="w-3.5 h-3.5 mr-1.5 text-[#C9AEF1]"></i> <?= htmlspecialchars($risk['category_name']) ?>
                                    </span>
                                </div>
                                <div class="p-3 bg-white border border-[#232426]/10 rounded-lg shadow-sm">
                                    <span class="block text-[10px] font-bold text-[#232426]/50 uppercase tracking-wider mb-1">Department</span>
                                    <span class="text-sm font-semibold text-[#232426] flex items-center">
                                        <i data-lucide="building" class="w-3.5 h-3.5 mr-1.5 text-[#232426]/50"></i> <?= htmlspecialchars($risk['department_name']) ?>
                                    </span>
                                </div>
                                <div class="p-3 bg-white border border-[#232426]/10 rounded-lg shadow-sm">
                                    <span class="block text-[10px] font-bold text-[#232426]/50 uppercase tracking-wider mb-1">Owner</span>
                                    <span class="text-sm font-semibold text-[#232426] flex items-center">
                                        <i data-lucide="user" class="w-3.5 h-3.5 mr-1.5 text-[#232426]/50"></i> <?= htmlspecialchars($risk['owner_name']) ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 4. Treatment Plan Section -->
                    <div class="bg-white rounded-xl border border-[#232426]/10 shadow-sm p-6 hover:shadow-md transition-shadow">
                        <h3 class="text-lg font-bold text-[#232426] mb-5 pb-3 border-b border-[#232426]/10 flex items-center">
                            <i data-lucide="shield-check" class="w-5 h-5 mr-2 text-[#EF6351]"></i> Treatment Plan
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-4">
                                <div>
                                    <span class="block text-[10px] font-bold text-[#232426]/50 uppercase tracking-wider mb-1">Selected Strategy</span>
                                    <span class="text-lg font-bold text-[#232426]"><?= htmlspecialchars($risk['treatment_strategy']) ?></span>
                                </div>
                                <div>
                                    <span class="block text-[10px] font-bold text-[#232426]/50 uppercase tracking-wider mb-1">System Recommendation</span>
                                    <span class="text-sm font-bold text-[#EF6351] flex items-center bg-[#EF6351]/5 px-3 py-1.5 rounded border border-[#EF6351]/20 inline-flex">
                                        <i data-lucide="zap" class="w-4 h-4 mr-1.5"></i> <?= $recommendedTreatment ?>
                                    </span>
                                </div>
                            </div>
                            <div class="space-y-4">
                                <div>
                                    <span class="block text-[10px] font-bold text-[#232426]/50 uppercase tracking-wider mb-1">Current Status</span>
                                    <span class="text-sm font-bold text-[#232426]"><?= htmlspecialchars($risk['status']) ?></span>
                                </div>
                                <div>
                                    <span class="block text-[10px] font-bold text-[#232426]/50 uppercase tracking-wider mb-1">Next Review Date</span>
                                    <span class="text-sm font-bold text-[#232426] flex items-center">
                                        <i data-lucide="calendar" class="w-4 h-4 mr-1.5 text-[#C9AEF1]"></i> <?= $risk['review_date'] ? date('M d, Y', strtotime($risk['review_date'])) : 'Not Scheduled' ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 6. Analyst Notes Section -->
                    <div class="bg-white rounded-xl border border-[#232426]/10 shadow-sm p-6 hover:shadow-md transition-shadow">
                        <div class="flex justify-between items-center mb-5 pb-3 border-b border-[#232426]/10">
                            <h3 class="text-lg font-bold text-[#232426] flex items-center">
                                <i data-lucide="file-edit" class="w-5 h-5 mr-2 text-[#EF6351]"></i> Analyst Notes
                            </h3>
                            <button type="button" class="text-xs font-semibold text-[#EF6351] hover:text-[#232426] transition-colors flex items-center">
                                <i data-lucide="plus" class="w-3 h-3 mr-1"></i> Add Note
                            </button>
                        </div>
                        <div class="space-y-4">
                            <!-- Placeholder Note using description fragment -->
                            <div class="p-4 bg-yellow-50/50 rounded-lg border border-yellow-100">
                                <div class="flex justify-between items-start mb-2">
                                    <span class="text-xs font-bold text-[#232426]">System Administrator</span>
                                    <span class="text-[10px] text-[#232426]/50"><?= date('M d, Y', strtotime($risk['created_at'])) ?></span>
                                </div>
                                <p class="text-sm text-[#232426]/80 italic">"Initial risk profile generated. Please review treatment strategy alignment during the next steering committee."</p>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Sidebar Details Column -->
                <div class="space-y-6">
                    
                    <!-- 3. Risk Assessment Section -->
                    <div class="bg-white rounded-xl border border-[#232426]/10 shadow-sm p-6 hover:shadow-md transition-shadow">
                        <h3 class="text-lg font-bold text-[#232426] mb-5 pb-3 border-b border-[#232426]/10 flex items-center">
                            <i data-lucide="activity" class="w-5 h-5 mr-2 text-[#EF6351]"></i> Assessment
                        </h3>
                        <div class="space-y-6">
                            <!-- Matrix Visualizer Placeholder -->
                            <div class="flex justify-between items-end gap-2 mb-2">
                                <div class="flex-1">
                                    <span class="block text-[10px] font-bold text-[#232426]/50 uppercase tracking-wider mb-1">Likelihood</span>
                                    <div class="h-2 w-full bg-slate-100 rounded-full overflow-hidden">
                                        <div class="h-full bg-blue-400 rounded-full" style="width: <?= ($risk['likelihood'] / 5) * 100 ?>%;"></div>
                                    </div>
                                    <span class="block text-sm font-bold text-[#232426] mt-1 text-right"><?= $risk['likelihood'] ?> / 5</span>
                                </div>
                                <div class="w-4 h-4 flex items-center justify-center text-[#232426]/30 font-bold mb-5">×</div>
                                <div class="flex-1">
                                    <span class="block text-[10px] font-bold text-[#232426]/50 uppercase tracking-wider mb-1">Impact</span>
                                    <div class="h-2 w-full bg-slate-100 rounded-full overflow-hidden">
                                        <div class="h-full bg-purple-400 rounded-full" style="width: <?= ($risk['impact'] / 5) * 100 ?>%;"></div>
                                    </div>
                                    <span class="block text-sm font-bold text-[#232426] mt-1 text-right"><?= $risk['impact'] ?> / 5</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 5. Timeline Placeholder Section -->
                    <div class="bg-white rounded-xl border border-[#232426]/10 shadow-sm p-6 hover:shadow-md transition-shadow">
                        <h3 class="text-lg font-bold text-[#232426] mb-5 pb-3 border-b border-[#232426]/10 flex items-center">
                            <i data-lucide="clock" class="w-5 h-5 mr-2 text-[#EF6351]"></i> Activity Timeline
                        </h3>
                        <div class="relative pl-4 border-l-2 border-slate-200 space-y-5">
                            <div class="relative">
                                <div class="absolute -left-[23px] bg-slate-200 w-3 h-3 rounded-full border-2 border-white top-1"></div>
                                <div class="text-xs font-bold text-[#232426] mb-0.5">Risk Updated</div>
                                <div class="text-[10px] text-[#232426]/50"><?= date('M d, Y', strtotime($risk['updated_at'])) ?> by System</div>
                            </div>
                            <?php if($risk['review_date']): ?>
                            <div class="relative">
                                <div class="absolute -left-[23px] bg-[#C9AEF1] w-3 h-3 rounded-full border-2 border-white top-1"></div>
                                <div class="text-xs font-bold text-[#232426] mb-0.5">Scheduled Review</div>
                                <div class="text-[10px] text-[#232426]/50">Target: <?= date('M d, Y', strtotime($risk['review_date'])) ?></div>
                            </div>
                            <?php endif; ?>
                            <div class="relative">
                                <div class="absolute -left-[23px] bg-[#BBC7B6] w-3 h-3 rounded-full border-2 border-white top-1"></div>
                                <div class="text-xs font-bold text-[#232426] mb-0.5">Risk Identified</div>
                                <div class="text-[10px] text-[#232426]/50"><?= date('M d, Y', strtotime($risk['created_at'])) ?> by <?= htmlspecialchars($risk['owner_name']) ?></div>
                            </div>
                        </div>
                    </div>

                    <!-- 7. Attachments Placeholder Section -->
                    <div class="bg-white rounded-xl border border-[#232426]/10 shadow-sm p-6 hover:shadow-md transition-shadow">
                        <div class="flex justify-between items-center mb-5 pb-3 border-b border-[#232426]/10">
                            <h3 class="text-lg font-bold text-[#232426] flex items-center">
                                <i data-lucide="paperclip" class="w-5 h-5 mr-2 text-[#EF6351]"></i> Attachments
                            </h3>
                            <button type="button" class="text-xs font-semibold text-[#EF6351] hover:text-[#232426] transition-colors flex items-center">
                                Upload
                            </button>
                        </div>
                        <div class="p-6 border-2 border-dashed border-slate-200 rounded-lg text-center bg-slate-50">
                            <i data-lucide="file-plus" class="w-6 h-6 text-[#232426]/30 mx-auto mb-2"></i>
                            <span class="block text-xs font-medium text-[#232426]/50">No files attached yet.</span>
                        </div>
                    </div>

                    <!-- 8. Related Modules Placeholder Section -->
                    <div class="bg-white rounded-xl border border-[#232426]/10 shadow-sm p-6 hover:shadow-md transition-shadow">
                        <h3 class="text-lg font-bold text-[#232426] mb-5 pb-3 border-b border-[#232426]/10 flex items-center">
                            <i data-lucide="link" class="w-5 h-5 mr-2 text-[#EF6351]"></i> Integrations
                        </h3>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="p-3 border border-slate-200 rounded-lg hover:border-[#EF6351]/30 hover:bg-slate-50 transition-colors cursor-pointer group">
                                <div class="flex items-center justify-between mb-1">
                                    <i data-lucide="server" class="w-4 h-4 text-[#232426]/50 group-hover:text-[#EF6351]"></i>
                                    <span class="text-xs font-bold bg-slate-100 px-1.5 py-0.5 rounded text-[#232426]/50 group-hover:text-[#232426]">0</span>
                                </div>
                                <span class="text-[10px] font-bold text-[#232426]/70 uppercase tracking-wider">Assets</span>
                            </div>
                            <div class="p-3 border border-slate-200 rounded-lg hover:border-[#EF6351]/30 hover:bg-slate-50 transition-colors cursor-pointer group">
                                <div class="flex items-center justify-between mb-1">
                                    <i data-lucide="shield" class="w-4 h-4 text-[#232426]/50 group-hover:text-[#EF6351]"></i>
                                    <span class="text-xs font-bold bg-slate-100 px-1.5 py-0.5 rounded text-[#232426]/50 group-hover:text-[#232426]">0</span>
                                </div>
                                <span class="text-[10px] font-bold text-[#232426]/70 uppercase tracking-wider">Controls</span>
                            </div>
                            <div class="p-3 border border-slate-200 rounded-lg hover:border-[#EF6351]/30 hover:bg-slate-50 transition-colors cursor-pointer group">
                                <div class="flex items-center justify-between mb-1">
                                    <i data-lucide="check-square" class="w-4 h-4 text-[#232426]/50 group-hover:text-[#EF6351]"></i>
                                    <span class="text-xs font-bold bg-slate-100 px-1.5 py-0.5 rounded text-[#232426]/50 group-hover:text-[#232426]">0</span>
                                </div>
                                <span class="text-[10px] font-bold text-[#232426]/70 uppercase tracking-wider">Audits</span>
                            </div>
                            <div class="p-3 border border-slate-200 rounded-lg hover:border-[#EF6351]/30 hover:bg-slate-50 transition-colors cursor-pointer group">
                                <div class="flex items-center justify-between mb-1">
                                    <i data-lucide="folder" class="w-4 h-4 text-[#232426]/50 group-hover:text-[#EF6351]"></i>
                                    <span class="text-xs font-bold bg-slate-100 px-1.5 py-0.5 rounded text-[#232426]/50 group-hover:text-[#232426]">0</span>
                                </div>
                                <span class="text-[10px] font-bold text-[#232426]/70 uppercase tracking-wider">Evidence</span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
