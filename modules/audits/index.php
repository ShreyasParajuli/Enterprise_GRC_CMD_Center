<?php
require_once __DIR__ . '/../../config/init.php';
requireLogin();

$pageTitle = 'Audit Management & Compliance Engagements';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
require_once __DIR__ . '/helpers/audit_helper.php';

// Fetch Filters
$search = trim($_GET['search'] ?? '');
$statusFilter = trim($_GET['status'] ?? '');
$typeFilter = trim($_GET['type'] ?? '');
$frameworkFilter = trim($_GET['framework'] ?? '');

// Build Query
$where = ["1=1"];
$params = [];

if (!empty($search)) {
    $where[] = "(a.audit_id LIKE ? OR a.title LIKE ? OR a.framework LIKE ? OR d.name LIKE ?)";
    $like = "%{$search}%";
    $params = array_merge($params, [$like, $like, $like, $like]);
}

if (!empty($statusFilter)) {
    $where[] = "a.status = ?";
    $params[] = $statusFilter;
}

if (!empty($typeFilter)) {
    $where[] = "a.audit_type = ?";
    $params[] = $typeFilter;
}

if (!empty($frameworkFilter)) {
    $where[] = "a.framework = ?";
    $params[] = $frameworkFilter;
}

$whereClause = implode(" AND ", $where);

// Fetch Audits
$sql = "
    SELECT a.*, 
           d.name AS department_name, 
           u.username AS auditor_name,
           (SELECT COUNT(*) FROM audit_findings f WHERE f.audit_id = a.id) AS total_findings,
           (SELECT COUNT(*) FROM audit_findings f WHERE f.audit_id = a.id AND f.status != 'Closed') AS open_findings,
           (SELECT COUNT(*) FROM audit_findings f WHERE f.audit_id = a.id AND f.severity = 'Critical' AND f.status != 'Closed') AS critical_findings
    FROM audits a
    LEFT JOIN departments d ON a.department_id = d.id
    LEFT JOIN users u ON a.lead_auditor_id = u.id
    WHERE {$whereClause}
    ORDER BY a.id DESC
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$audits = $stmt->fetchAll();

// KPI Calculations
$totalAuditsCount = $pdo->query("SELECT COUNT(*) FROM audits")->fetchColumn();
$fieldworkCount = $pdo->query("SELECT COUNT(*) FROM audits WHERE status = 'Fieldwork'")->fetchColumn();
$openFindingsCount = $pdo->query("SELECT COUNT(*) FROM audit_findings WHERE status != 'Closed'")->fetchColumn();
$satisfactoryCount = $pdo->query("SELECT COUNT(*) FROM audits WHERE report_rating = 'Satisfactory'")->fetchColumn();
$satisfactoryRate = $totalAuditsCount > 0 ? round(($satisfactoryCount / $totalAuditsCount) * 100) : 0;

// Unique Frameworks for filter dropdown
$frameworks = $pdo->query("SELECT DISTINCT framework FROM audits WHERE framework IS NOT NULL ORDER BY framework")->fetchAll(PDO::FETCH_COLUMN);
?>

<div class="flex-1 flex flex-col min-w-0 overflow-hidden">
    <?php require_once __DIR__ . '/../../includes/navbar.php'; ?>

    <main class="flex-1 overflow-y-auto p-6 bg-[#FFFAF3]">
        <div class="max-w-7xl mx-auto space-y-6">

            <!-- Breadcrumb & Top Bar -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <nav class="text-xs font-semibold text-brand-dark/50 uppercase tracking-wider mb-1" aria-label="Breadcrumb">
                        <ol class="list-none p-0 inline-flex items-center space-x-2">
                            <li><a href="<?= BASE_URL ?>/pages/dashboard.php" class="hover:text-brand-primary">Dashboard</a></li>
                            <li><i data-lucide="chevron-right" class="w-3.5 h-3.5"></i></li>
                            <li class="text-brand-dark">Audit Management</li>
                        </ol>
                    </nav>
                    <h2 class="text-2xl font-bold text-[#232426]">Audit & Compliance Engagements</h2>
                    <p class="text-[#232426]/70 text-sm mt-0.5">Plan, execute, and monitor internal, external, and certification audits.</p>
                </div>

                <div class="flex items-center gap-3">
                    <a href="<?= BASE_URL ?>/modules/workflows/index.php" class="inline-flex items-center px-3.5 py-2 rounded-lg bg-white border border-[#232426]/10 text-sm font-semibold text-brand-dark hover:bg-slate-50 transition-colors shadow-sm">
                        <i data-lucide="git-merge" class="w-4 h-4 mr-2 text-brand-primary"></i>
                        Workflow Engine
                    </a>
                    <a href="<?= BASE_URL ?>/modules/audits/create.php" class="inline-flex items-center px-4 py-2 rounded-lg bg-brand-primary text-white text-sm font-semibold hover:opacity-95 shadow-md shadow-brand-primary/20 transition-all">
                        <i data-lucide="plus-circle" class="w-4 h-4 mr-2"></i>
                        New Audit Engagement
                    </a>
                </div>
            </div>

            <!-- KPI Summary Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <!-- Total Engagements -->
                <div class="bg-white rounded-xl p-5 border border-[#232426]/10 shadow-sm flex items-center justify-between">
                    <div>
                        <p class="text-[11px] font-bold text-[#232426]/50 uppercase tracking-wider">Total Audits</p>
                        <p class="text-2xl font-bold text-[#232426] mt-1"><?= $totalAuditsCount ?></p>
                        <span class="text-xs text-[#232426]/60">Across all frameworks</span>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                        <i data-lucide="file-check-2" class="w-6 h-6"></i>
                    </div>
                </div>

                <!-- Active in Fieldwork -->
                <div class="bg-white rounded-xl p-5 border border-orange-200 shadow-sm flex items-center justify-between relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-1.5 h-full bg-orange-500"></div>
                    <div>
                        <p class="text-[11px] font-bold text-orange-800/60 uppercase tracking-wider">In Fieldwork</p>
                        <p class="text-2xl font-bold text-orange-600 mt-1"><?= $fieldworkCount ?></p>
                        <span class="text-xs text-orange-700/70">Actively testing controls</span>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center">
                        <i data-lucide="activity" class="w-6 h-6"></i>
                    </div>
                </div>

                <!-- Open Deficiencies / Findings -->
                <div class="bg-white rounded-xl p-5 border border-red-200 shadow-sm flex items-center justify-between relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-1.5 h-full bg-red-500"></div>
                    <div>
                        <p class="text-[11px] font-bold text-red-800/60 uppercase tracking-wider">Open Findings</p>
                        <p class="text-2xl font-bold text-red-600 mt-1"><?= $openFindingsCount ?></p>
                        <span class="text-xs text-red-700/70">Awaiting remediation</span>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-red-50 text-red-600 flex items-center justify-center">
                        <i data-lucide="alert-triangle" class="w-6 h-6"></i>
                    </div>
                </div>

                <!-- Compliance / Rating Rate -->
                <div class="bg-white rounded-xl p-5 border border-emerald-200 shadow-sm flex items-center justify-between relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-1.5 h-full bg-emerald-500"></div>
                    <div>
                        <p class="text-[11px] font-bold text-emerald-800/60 uppercase tracking-wider">Satisfactory Rate</p>
                        <p class="text-2xl font-bold text-emerald-600 mt-1"><?= $satisfactoryRate ?>%</p>
                        <span class="text-xs text-emerald-700/70"><?= $satisfactoryCount ?> Satisfactory ratings</span>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <i data-lucide="shield-check" class="w-6 h-6"></i>
                    </div>
                </div>
            </div>

            <!-- Search & Filters Bar -->
            <div class="bg-white p-4 rounded-xl border border-[#232426]/10 shadow-sm">
                <form method="GET" action="<?= BASE_URL ?>/modules/audits/index.php" class="flex flex-col md:flex-row gap-3 items-center">
                    
                    <!-- Search Input -->
                    <div class="relative flex-1 w-full">
                        <i data-lucide="search" class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-[#232426]/40"></i>
                        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" 
                               placeholder="Search by Audit ID, Title, Framework, or Department..."
                               class="w-full pl-10 pr-4 py-2 border border-[#232426]/15 rounded-lg text-sm text-[#232426] placeholder-[#232426]/40 focus:outline-none focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary">
                    </div>

                    <!-- Status Filter -->
                    <div class="w-full md:w-44">
                        <select name="status" class="w-full py-2 px-3 border border-[#232426]/15 rounded-lg text-sm text-[#232426] bg-white focus:outline-none focus:border-brand-primary">
                            <option value="">All Statuses</option>
                            <option value="Planned" <?= $statusFilter === 'Planned' ? 'selected' : '' ?>>Planned</option>
                            <option value="Fieldwork" <?= $statusFilter === 'Fieldwork' ? 'selected' : '' ?>>Fieldwork</option>
                            <option value="Review" <?= $statusFilter === 'Review' ? 'selected' : '' ?>>Review</option>
                            <option value="Finalized" <?= $statusFilter === 'Finalized' ? 'selected' : '' ?>>Finalized</option>
                            <option value="Archived" <?= $statusFilter === 'Archived' ? 'selected' : '' ?>>Archived</option>
                        </select>
                    </div>

                    <!-- Type Filter -->
                    <div class="w-full md:w-40">
                        <select name="type" class="w-full py-2 px-3 border border-[#232426]/15 rounded-lg text-sm text-[#232426] bg-white focus:outline-none focus:border-brand-primary">
                            <option value="">All Types</option>
                            <option value="Internal" <?= $typeFilter === 'Internal' ? 'selected' : '' ?>>Internal</option>
                            <option value="External" <?= $typeFilter === 'External' ? 'selected' : '' ?>>External</option>
                            <option value="Regulatory" <?= $typeFilter === 'Regulatory' ? 'selected' : '' ?>>Regulatory</option>
                            <option value="Certification" <?= $typeFilter === 'Certification' ? 'selected' : '' ?>>Certification</option>
                            <option value="Third-Party" <?= $typeFilter === 'Third-Party' ? 'selected' : '' ?>>Third-Party</option>
                        </select>
                    </div>

                    <!-- Framework Filter -->
                    <div class="w-full md:w-44">
                        <select name="framework" class="w-full py-2 px-3 border border-[#232426]/15 rounded-lg text-sm text-[#232426] bg-white focus:outline-none focus:border-brand-primary">
                            <option value="">All Frameworks</option>
                            <?php foreach ($frameworks as $fw): ?>
                                <option value="<?= htmlspecialchars($fw) ?>" <?= $frameworkFilter === $fw ? 'selected' : '' ?>><?= htmlspecialchars($fw) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="flex items-center gap-2 w-full md:w-auto">
                        <button type="submit" class="w-full md:w-auto px-4 py-2 bg-brand-primary text-white text-sm font-semibold rounded-lg hover:opacity-90 transition-opacity">
                            Filter
                        </button>
                        <?php if (!empty($search) || !empty($statusFilter) || !empty($typeFilter) || !empty($frameworkFilter)): ?>
                            <a href="<?= BASE_URL ?>/modules/audits/index.php" class="px-3 py-2 text-sm text-[#232426]/60 hover:text-brand-primary">
                                Reset
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Audits Data Table -->
            <div class="bg-white rounded-xl border border-[#232426]/10 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-[#232426]/10">
                        <thead class="bg-slate-50/80">
                            <tr>
                                <th scope="col" class="px-5 py-3.5 text-left text-xs font-bold text-[#232426]/60 uppercase tracking-wider">Audit Identifier</th>
                                <th scope="col" class="px-5 py-3.5 text-left text-xs font-bold text-[#232426]/60 uppercase tracking-wider">Title & Scope</th>
                                <th scope="col" class="px-5 py-3.5 text-left text-xs font-bold text-[#232426]/60 uppercase tracking-wider">Framework</th>
                                <th scope="col" class="px-5 py-3.5 text-left text-xs font-bold text-[#232426]/60 uppercase tracking-wider">Audited Unit</th>
                                <th scope="col" class="px-5 py-3.5 text-left text-xs font-bold text-[#232426]/60 uppercase tracking-wider">Lead Auditor</th>
                                <th scope="col" class="px-5 py-3.5 text-left text-xs font-bold text-[#232426]/60 uppercase tracking-wider">Timeline</th>
                                <th scope="col" class="px-5 py-3.5 text-left text-xs font-bold text-[#232426]/60 uppercase tracking-wider">Findings</th>
                                <th scope="col" class="px-5 py-3.5 text-left text-xs font-bold text-[#232426]/60 uppercase tracking-wider">Status / Rating</th>
                                <th scope="col" class="px-5 py-3.5 text-right text-xs font-bold text-[#232426]/60 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#232426]/5 bg-white">
                            <?php if (empty($audits)): ?>
                                <tr>
                                    <td colspan="9" class="px-6 py-12 text-center text-[#232426]/50">
                                        <div class="max-w-xs mx-auto text-center space-y-2">
                                            <i data-lucide="clipboard-x" class="w-10 h-10 mx-auto text-slate-300"></i>
                                            <p class="font-semibold text-base text-[#232426]">No Audits Found</p>
                                            <p class="text-xs text-[#232426]/50">Try clearing your filters or create a new audit engagement to get started.</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($audits as $audit): ?>
                                    <tr class="hover:bg-slate-50/60 transition-colors">
                                        
                                        <!-- Identifier -->
                                        <td class="px-5 py-4 whitespace-nowrap">
                                            <a href="<?= BASE_URL ?>/modules/audits/view.php?id=<?= $audit['id'] ?>" class="font-bold text-sm text-brand-primary hover:underline">
                                                <?= htmlspecialchars($audit['audit_id']) ?>
                                            </a>
                                            <div class="text-[11px] text-[#232426]/50 mt-0.5">
                                                <?= htmlspecialchars($audit['audit_type']) ?>
                                            </div>
                                        </td>

                                        <!-- Title & Scope -->
                                        <td class="px-5 py-4">
                                            <a href="<?= BASE_URL ?>/modules/audits/view.php?id=<?= $audit['id'] ?>" class="text-sm font-semibold text-[#232426] hover:text-brand-primary line-clamp-1">
                                                <?= htmlspecialchars($audit['title']) ?>
                                            </a>
                                            <p class="text-xs text-[#232426]/60 line-clamp-1 mt-0.5">
                                                <?= htmlspecialchars($audit['scope'] ?: $audit['description']) ?>
                                            </p>
                                        </td>

                                        <!-- Framework -->
                                        <td class="px-5 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                                <i data-lucide="layers" class="w-3 h-3 mr-1 text-slate-500"></i>
                                                <?= htmlspecialchars($audit['framework']) ?>
                                            </span>
                                        </td>

                                        <!-- Audited Unit -->
                                        <td class="px-5 py-4 whitespace-nowrap text-sm text-[#232426]/80 font-medium">
                                            <?= htmlspecialchars($audit['department_name'] ?: 'N/A') ?>
                                        </td>

                                        <!-- Auditor -->
                                        <td class="px-5 py-4 whitespace-nowrap text-sm text-[#232426]/80">
                                            <div class="flex items-center gap-2">
                                                <div class="w-6 h-6 rounded-full bg-brand-primary/10 text-brand-primary flex items-center justify-center text-xs font-bold">
                                                    <?= strtoupper(substr($audit['auditor_name'] ?? 'U', 0, 1)) ?>
                                                </div>
                                                <span><?= htmlspecialchars($audit['auditor_name'] ?: 'Unassigned') ?></span>
                                            </div>
                                        </td>

                                        <!-- Timeline -->
                                        <td class="px-5 py-4 whitespace-nowrap text-xs text-[#232426]/70">
                                            <div class="flex items-center gap-1 font-medium text-[#232426]">
                                                <i data-lucide="calendar" class="w-3.5 h-3.5 text-slate-400"></i>
                                                <?= $audit['start_date'] ? date('M j, Y', strtotime($audit['start_date'])) : 'TBD' ?>
                                            </div>
                                            <div class="text-[11px] text-[#232426]/50 pl-4.5">
                                                to <?= $audit['end_date'] ? date('M j, Y', strtotime($audit['end_date'])) : 'Open' ?>
                                            </div>
                                        </td>

                                        <!-- Findings -->
                                        <td class="px-5 py-4 whitespace-nowrap">
                                            <?php if ($audit['open_findings'] > 0): ?>
                                                <div class="flex items-center gap-1.5">
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-red-50 text-red-700 border border-red-200">
                                                        <?= $audit['open_findings'] ?> Open
                                                    </span>
                                                    <?php if ($audit['critical_findings'] > 0): ?>
                                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-extrabold bg-red-600 text-white" title="Critical Severity Deficiencies">
                                                            <?= $audit['critical_findings'] ?> Crit
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-xs text-emerald-600 font-medium inline-flex items-center">
                                                    <i data-lucide="check" class="w-3.5 h-3.5 mr-0.5"></i> 0 Open
                                                </span>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Status & Rating -->
                                        <td class="px-5 py-4 whitespace-nowrap space-y-1">
                                            <div><?= getAuditStatusBadge($audit['status']) ?></div>
                                            <?php if ($audit['report_rating'] && $audit['report_rating'] !== 'Pending Rating'): ?>
                                                <div><?= getAuditRatingBadge($audit['report_rating']) ?></div>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Actions -->
                                        <td class="px-5 py-4 whitespace-nowrap text-right text-sm">
                                            <div class="flex items-center justify-end gap-2">
                                                <a href="<?= BASE_URL ?>/modules/audits/view.php?id=<?= $audit['id'] ?>" class="p-1 text-slate-500 hover:text-brand-primary rounded hover:bg-slate-100" title="View Audit Details">
                                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                                </a>
                                                <a href="<?= BASE_URL ?>/modules/audits/edit.php?id=<?= $audit['id'] ?>" class="p-1 text-slate-500 hover:text-blue-600 rounded hover:bg-slate-100" title="Edit Audit">
                                                    <i data-lucide="edit-3" class="w-4 h-4"></i>
                                                </a>
                                                <button type="button" onclick="confirmAction('Are you sure you want to delete audit <?= htmlspecialchars($audit['audit_id']) ?>?', function() { window.location.href = '<?= BASE_URL ?>/modules/audits/delete.php?id=<?= $audit['id'] ?>'; })" 
                                                        class="p-1 text-slate-400 hover:text-red-600 rounded hover:bg-slate-100" title="Delete Audit">
                                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Footer Summary Bar -->
                <div class="px-6 py-3.5 bg-slate-50/70 border-t border-[#232426]/10 flex items-center justify-between text-xs text-[#232426]/60">
                    <div>
                        Showing <strong><?= count($audits) ?></strong> of <strong><?= $totalAuditsCount ?></strong> audit engagements
                    </div>
                    <div class="flex items-center gap-4">
                        <span class="inline-flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-blue-500"></span> Planned</span>
                        <span class="inline-flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-orange-500"></span> Fieldwork</span>
                        <span class="inline-flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-purple-500"></span> Review</span>
                        <span class="inline-flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-emerald-500"></span> Finalized</span>
                    </div>
                </div>
            </div>

        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
