<?php
require_once __DIR__ . '/../../config/init.php';
requireLogin();

$pageTitle = 'Risk Register';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
require_once __DIR__ . '/helpers/risk_helper.php';

// Pagination setup
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Filter, Search, and Sort setup
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';
$category_id = $_GET['category_id'] ?? '';
$department_id = $_GET['department_id'] ?? '';
$level = $_GET['level'] ?? '';
$treatment = $_GET['treatment'] ?? '';
$sort = $_GET['sort'] ?? 'new';

// Base Query
$whereSql = "WHERE 1=1";
$params = [];

if (!empty($search)) {
    $whereSql .= " AND (r.risk_id LIKE ? OR r.title LIKE ? OR r.description LIKE ? OR u.username LIKE ?)";
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
}

if (!empty($status)) {
    $whereSql .= " AND r.status = ?";
    $params[] = $status;
}

if (!empty($category_id)) {
    $whereSql .= " AND r.category_id = ?";
    $params[] = $category_id;
}

if (!empty($department_id)) {
    $whereSql .= " AND r.department_id = ?";
    $params[] = $department_id;
}

if (!empty($treatment)) {
    $whereSql .= " AND r.treatment_strategy = ?";
    $params[] = $treatment;
}

if (!empty($level)) {
    if ($level === 'Critical') {
        $whereSql .= " AND (r.likelihood * r.impact) >= 16";
    } elseif ($level === 'High') {
        $whereSql .= " AND (r.likelihood * r.impact) >= 11 AND (r.likelihood * r.impact) <= 15";
    } elseif ($level === 'Medium') {
        $whereSql .= " AND (r.likelihood * r.impact) >= 6 AND (r.likelihood * r.impact) <= 10";
    } elseif ($level === 'Low') {
        $whereSql .= " AND (r.likelihood * r.impact) <= 5";
    }
}

// Order By
$orderBySql = "ORDER BY r.id DESC";
if ($sort === 'score_desc') $orderBySql = "ORDER BY (r.likelihood * r.impact) DESC";
if ($sort === 'score_asc') $orderBySql = "ORDER BY (r.likelihood * r.impact) ASC";
if ($sort === 'updated') $orderBySql = "ORDER BY r.updated_at DESC";
if ($sort === 'review') $orderBySql = "ORDER BY r.review_date ASC";
if ($sort === 'id') $orderBySql = "ORDER BY r.risk_id ASC";

// Get total count
$countStmt = $pdo->prepare("
    SELECT COUNT(*) FROM risks r
    LEFT JOIN users u ON r.owner_id = u.id
    $whereSql
");
$countStmt->execute($params);
$totalRows = $countStmt->fetchColumn();
$totalPages = ceil($totalRows / $limit);

// Fetch data
$query = "
    SELECT r.*, c.category_name, d.name as department_name, u.username as owner_name,
    (r.likelihood * r.impact) as risk_score
    FROM risks r
    LEFT JOIN risk_categories c ON r.category_id = c.id
    LEFT JOIN departments d ON r.department_id = d.id
    LEFT JOIN users u ON r.owner_id = u.id
    $whereSql
    $orderBySql
    LIMIT $limit OFFSET $offset
";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$risks = $stmt->fetchAll();

// For summary cards (needs unrestricted data or unfiltered?) 
// We will show the summary based on the CURRENT filter to be useful.
// No wait, the prompt says "Summary Cards At the top of the Risk Register create summary cards showing Total Risks, Critical Risks, High Risks, Medium Risks, Low Risks". Standard is across the whole register.
$allRisksStmt = $pdo->query("SELECT likelihood, impact FROM risks");
$allRisksForSummary = $allRisksStmt->fetchAll();
$summary = summarizeRisks($allRisksForSummary);

// Fetch lookups for filters
$categories = $pdo->query("SELECT id, category_name FROM risk_categories ORDER BY category_name")->fetchAll();
$departments = $pdo->query("SELECT id, name FROM departments ORDER BY name")->fetchAll();

?>

<div class="flex-1 flex flex-col min-w-0 overflow-hidden">
    <?php require_once __DIR__ . '/../../includes/navbar.php'; ?>

    <main class="flex-1 overflow-y-auto p-6 bg-[#FFFAF3]">
        <div class="max-w-7xl mx-auto space-y-6">
            <!-- Breadcrumb -->
            <nav class="text-sm font-medium text-brand-dark/50" aria-label="Breadcrumb">
                <ol class="list-none p-0 inline-flex">
                    <li class="flex items-center">
                        <a href="<?= BASE_URL ?>/pages/dashboard.php" class="hover:text-brand-primary">Dashboard</a>
                        <i data-lucide="chevron-right" class="w-4 h-4 mx-2"></i>
                    </li>
                    <li class="flex items-center text-[#232426]">
                        Risk Register
                    </li>
                </ol>
            </nav>

            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-bold text-[#232426]">Risk Register</h2>
                    <p class="text-[#232426]/70 mt-1">Manage enterprise risks across the organization.</p>
                </div>
                <div class="flex space-x-3">
                    <div class="relative group">
                        <button type="button" class="bg-white border border-[#232426]/20 hover:bg-slate-50 text-[#232426] px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm flex items-center inline-flex">
                            <i data-lucide="download" class="w-4 h-4 mr-2"></i> Export
                            <i data-lucide="chevron-down" class="w-3 h-3 ml-2"></i>
                        </button>
                        <div class="absolute right-0 mt-2 w-36 bg-white rounded-lg shadow-lg border border-[#232426]/10 hidden group-hover:block z-50">
                            <button type="button" onclick="document.getElementById('export_format').value='csv'; document.getElementById('exportForm').submit();" class="block w-full text-left px-4 py-2 text-sm text-[#232426] hover:bg-slate-50 hover:text-[#EF6351] rounded-t-lg transition-colors">
                                <i data-lucide="file-spreadsheet" class="w-4 h-4 inline mr-2 text-green-600"></i> as CSV
                            </button>
                            <button type="button" onclick="document.getElementById('export_format').value='pdf'; document.getElementById('exportForm').submit();" class="block w-full text-left px-4 py-2 text-sm text-[#232426] hover:bg-slate-50 hover:text-[#EF6351] rounded-b-lg transition-colors border-t border-[#232426]/5">
                                <i data-lucide="file-text" class="w-4 h-4 inline mr-2 text-red-600"></i> as PDF
                            </button>
                        </div>
                    </div>
                    <a href="<?= BASE_URL ?>/modules/risks/create.php" class="bg-[#EF6351] hover:opacity-90 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-lg flex items-center inline-flex">
                        <i data-lucide="plus" class="w-4 h-4 mr-2"></i> Create Risk
                    </a>
                </div>
            </div>

            <!-- Hidden form to handle export with current filters -->
            <form id="exportForm" action="<?= BASE_URL ?>/modules/risks/export.php" method="GET" class="hidden">
                <input type="hidden" name="format" id="export_format" value="csv">
                <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
                <input type="hidden" name="status" value="<?= htmlspecialchars($status) ?>">
                <input type="hidden" name="category_id" value="<?= htmlspecialchars($category_id) ?>">
                <input type="hidden" name="department_id" value="<?= htmlspecialchars($department_id) ?>">
                <input type="hidden" name="level" value="<?= htmlspecialchars($level) ?>">
                <input type="hidden" name="treatment" value="<?= htmlspecialchars($treatment) ?>">
                <input type="hidden" name="sort" value="<?= htmlspecialchars($sort) ?>">
            </form>



            <!-- Summary Cards -->
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
                <div class="bg-white rounded-xl border border-[#232426]/10 shadow-sm p-4 flex flex-col items-center justify-center transition-transform hover:scale-105">
                    <span class="text-sm font-semibold text-[#232426]/60 uppercase tracking-wider mb-1">Total</span>
                    <span class="text-3xl font-bold text-[#232426]"><?= $summary['Total'] ?></span>
                </div>
                <div class="bg-red-50 rounded-xl border border-red-200 shadow-sm p-4 flex flex-col items-center justify-center transition-transform hover:scale-105">
                    <span class="text-sm font-semibold text-red-800 uppercase tracking-wider mb-1">Critical</span>
                    <span class="text-3xl font-bold text-red-700"><?= $summary['Critical'] ?></span>
                </div>
                <div class="bg-orange-50 rounded-xl border border-orange-200 shadow-sm p-4 flex flex-col items-center justify-center transition-transform hover:scale-105">
                    <span class="text-sm font-semibold text-orange-800 uppercase tracking-wider mb-1">High</span>
                    <span class="text-3xl font-bold text-orange-700"><?= $summary['High'] ?></span>
                </div>
                <div class="bg-yellow-50 rounded-xl border border-yellow-200 shadow-sm p-4 flex flex-col items-center justify-center transition-transform hover:scale-105">
                    <span class="text-sm font-semibold text-yellow-800 uppercase tracking-wider mb-1">Medium</span>
                    <span class="text-3xl font-bold text-yellow-700"><?= $summary['Medium'] ?></span>
                </div>
                <div class="bg-green-50 rounded-xl border border-green-200 shadow-sm p-4 flex flex-col items-center justify-center transition-transform hover:scale-105">
                    <span class="text-sm font-semibold text-green-800 uppercase tracking-wider mb-1">Low</span>
                    <span class="text-3xl font-bold text-green-700"><?= $summary['Low'] ?></span>
                </div>
            </div>

            <!-- Filter & Search Bar -->
            <div class="bg-white rounded-xl border border-[#232426]/10 shadow-sm p-4">
                <form action="<?= BASE_URL ?>/modules/risks/index.php" method="GET" class="space-y-4">
                    <!-- Search & Sort Row -->
                    <div class="flex flex-col md:flex-row gap-4">
                        <div class="flex-1 relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i data-lucide="search" class="w-5 h-5 text-gray-400"></i>
                            </div>
                            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search by Risk ID, Title, Description, or Owner..." class="block w-full pl-10 pr-3 py-2 border border-[#232426]/20 rounded-lg shadow-sm focus:border-[#EF6351] focus:ring-[#EF6351] sm:text-sm bg-slate-50">
                        </div>
                        <div class="w-full md:w-64">
                            <select name="sort" onchange="this.form.submit()" class="block w-full py-2 border border-[#232426]/20 rounded-lg shadow-sm focus:border-[#EF6351] focus:ring-[#EF6351] sm:text-sm bg-slate-50">
                                <option value="new" <?= $sort === 'new' ? 'selected' : '' ?>>Sort: Recently Created</option>
                                <option value="updated" <?= $sort === 'updated' ? 'selected' : '' ?>>Sort: Recently Updated</option>
                                <option value="score_desc" <?= $sort === 'score_desc' ? 'selected' : '' ?>>Sort: Highest Risk Score</option>
                                <option value="score_asc" <?= $sort === 'score_asc' ? 'selected' : '' ?>>Sort: Lowest Risk Score</option>
                                <option value="review" <?= $sort === 'review' ? 'selected' : '' ?>>Sort: Review Date</option>
                                <option value="id" <?= $sort === 'id' ? 'selected' : '' ?>>Sort: Risk ID</option>
                            </select>
                        </div>
                        <div class="hidden md:block">
                            <button type="submit" class="bg-[#232426] text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-black transition-colors h-full shadow-sm">
                                Apply
                            </button>
                        </div>
                    </div>
                    
                    <!-- Advanced Filters Row -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                        <select name="status" onchange="this.form.submit()" class="block w-full py-2 border border-[#232426]/20 rounded-lg shadow-sm focus:border-[#EF6351] focus:ring-[#EF6351] sm:text-sm bg-slate-50 text-gray-700">
                            <option value="">All Statuses</option>
                            <option value="Open" <?= $status === 'Open' ? 'selected' : '' ?>>Open</option>
                            <option value="In Progress" <?= $status === 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                            <option value="Closed" <?= $status === 'Closed' ? 'selected' : '' ?>>Closed</option>
                        </select>
                        <select name="level" onchange="this.form.submit()" class="block w-full py-2 border border-[#232426]/20 rounded-lg shadow-sm focus:border-[#EF6351] focus:ring-[#EF6351] sm:text-sm bg-slate-50 text-gray-700">
                            <option value="">All Levels</option>
                            <option value="Critical" <?= $level === 'Critical' ? 'selected' : '' ?>>Critical (16-25)</option>
                            <option value="High" <?= $level === 'High' ? 'selected' : '' ?>>High (11-15)</option>
                            <option value="Medium" <?= $level === 'Medium' ? 'selected' : '' ?>>Medium (6-10)</option>
                            <option value="Low" <?= $level === 'Low' ? 'selected' : '' ?>>Low (1-5)</option>
                        </select>
                        <select name="category_id" onchange="this.form.submit()" class="block w-full py-2 border border-[#232426]/20 rounded-lg shadow-sm focus:border-[#EF6351] focus:ring-[#EF6351] sm:text-sm bg-slate-50 text-gray-700">
                            <option value="">All Categories</option>
                            <?php foreach($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= $category_id == $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['category_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <select name="department_id" onchange="this.form.submit()" class="block w-full py-2 border border-[#232426]/20 rounded-lg shadow-sm focus:border-[#EF6351] focus:ring-[#EF6351] sm:text-sm bg-slate-50 text-gray-700">
                            <option value="">All Departments</option>
                            <?php foreach($departments as $dept): ?>
                                <option value="<?= $dept['id'] ?>" <?= $department_id == $dept['id'] ? 'selected' : '' ?>><?= htmlspecialchars($dept['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <select name="treatment" onchange="this.form.submit()" class="block w-full py-2 border border-[#232426]/20 rounded-lg shadow-sm focus:border-[#EF6351] focus:ring-[#EF6351] sm:text-sm bg-slate-50 text-gray-700">
                            <option value="">All Strategies</option>
                            <option value="Mitigate" <?= $treatment === 'Mitigate' ? 'selected' : '' ?>>Mitigate</option>
                            <option value="Transfer" <?= $treatment === 'Transfer' ? 'selected' : '' ?>>Transfer</option>
                            <option value="Avoid" <?= $treatment === 'Avoid' ? 'selected' : '' ?>>Avoid</option>
                            <option value="Accept" <?= $treatment === 'Accept' ? 'selected' : '' ?>>Accept</option>
                        </select>
                    </div>
                    
                    <div class="md:hidden pt-2">
                        <button type="submit" class="w-full bg-[#232426] text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-black transition-colors shadow-sm">
                            Apply Filters
                        </button>
                    </div>
                </form>
            </div>

            <!-- Risk Table Component (Wrapped in Form for Bulk Actions) -->
            <form id="bulkForm" action="<?= BASE_URL ?>/modules/risks/bulk.php" method="POST">
                <input type="hidden" name="bulk_action" id="bulk_action_input" value="">
                
                <!-- Bulk Action Controls -->
                <div class="flex flex-wrap items-center gap-3 mb-4 hidden bg-white p-3 rounded-xl border border-[#232426]/10 shadow-sm" id="bulkControls">
                    <span class="text-sm font-bold text-[#232426] bg-slate-100 px-3 py-1.5 rounded-lg border border-slate-200"><span id="selectedCount">0</span> selected</span>
                    <button type="button" onclick="submitBulk('delete')" class="bg-red-50 text-red-700 hover:bg-red-100 border border-red-200 px-3 py-1.5 rounded-lg text-sm font-medium transition-colors shadow-sm">
                        <i data-lucide="trash-2" class="w-4 h-4 inline mr-1"></i> Delete
                    </button>
                    <button type="button" onclick="submitBulk('close')" class="bg-[#BBC7B6]/30 text-[#232426] hover:bg-[#BBC7B6]/50 border border-[#BBC7B6] px-3 py-1.5 rounded-lg text-sm font-medium transition-colors shadow-sm">
                        <i data-lucide="check-circle" class="w-4 h-4 inline mr-1"></i> Close
                    </button>
                    <div class="h-6 w-px bg-[#232426]/10 mx-1"></div>
                    <div class="flex items-center space-x-2">
                        <select id="bulk_status" class="py-1.5 pl-3 pr-8 border border-[#232426]/20 rounded-lg shadow-sm focus:border-[#EF6351] focus:ring-[#EF6351] text-sm bg-slate-50 text-gray-700">
                            <option value="">Set Status...</option>
                            <option value="Open">Open</option>
                            <option value="In Progress">In Progress</option>
                            <option value="Closed">Closed</option>
                        </select>
                        <button type="button" onclick="submitBulkWithVal('set_status', 'bulk_status')" class="bg-white border border-[#232426]/20 text-[#232426] hover:bg-slate-50 px-3 py-1.5 rounded-lg text-sm font-medium transition-colors shadow-sm">
                            Apply
                        </button>
                    </div>
                    <div class="flex items-center space-x-2">
                        <select id="bulk_treatment" class="py-1.5 pl-3 pr-8 border border-[#232426]/20 rounded-lg shadow-sm focus:border-[#EF6351] focus:ring-[#EF6351] text-sm bg-slate-50 text-gray-700">
                            <option value="">Set Treatment...</option>
                            <option value="Mitigate">Mitigate</option>
                            <option value="Transfer">Transfer</option>
                            <option value="Avoid">Avoid</option>
                            <option value="Accept">Accept</option>
                        </select>
                        <button type="button" onclick="submitBulkWithVal('set_treatment', 'bulk_treatment')" class="bg-white border border-[#232426]/20 text-[#232426] hover:bg-slate-50 px-3 py-1.5 rounded-lg text-sm font-medium transition-colors shadow-sm">
                            Apply
                        </button>
                    </div>
                </div>

                <?php require_once __DIR__ . '/components/risk_table.php'; ?>
            </form>
            
            <!-- Pagination Controls -->
            <?php if ($totalPages > 1): ?>
                <div class="flex items-center justify-between border-t border-[#232426]/10 bg-white px-4 py-3 sm:px-6 rounded-xl shadow-sm mt-6">
                    <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm text-[#232426]/70">
                                Showing <span class="font-medium text-[#232426]"><?= $totalRows == 0 ? 0 : $offset + 1 ?></span> to <span class="font-medium text-[#232426]"><?= min($offset + $limit, $totalRows) ?></span> of <span class="font-medium text-[#232426]"><?= $totalRows ?></span> Risks
                            </p>
                        </div>
                        <div>
                            <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                                <?php
                                // Rebuild query string for pagination links
                                $queryParams = $_GET;
                                unset($queryParams['page']);
                                $queryString = http_build_query($queryParams);
                                $queryString = $queryString ? '&' . $queryString : '';
                                ?>
                                
                                <a href="?page=<?= max(1, $page - 1) . $queryString ?>" class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-[#232426]/20 hover:bg-gray-50 focus:z-20 focus:outline-offset-0 <?= $page <= 1 ? 'pointer-events-none opacity-50' : '' ?>">
                                    <span class="sr-only">Previous</span>
                                    <i data-lucide="chevron-left" class="h-5 w-5"></i>
                                </a>
                                
                                <?php for($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                    <a href="?page=<?= $i . $queryString ?>" class="relative inline-flex items-center px-4 py-2 text-sm font-semibold <?= $i == $page ? 'bg-[#EF6351] text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#EF6351] z-10' : 'text-[#232426] ring-1 ring-inset ring-[#232426]/20 hover:bg-gray-50' ?>">
                                        <?= $i ?>
                                    </a>
                                <?php endfor; ?>
                                
                                <a href="?page=<?= min($totalPages, $page + 1) . $queryString ?>" class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-[#232426]/20 hover:bg-gray-50 focus:z-20 focus:outline-offset-0 <?= $page >= $totalPages ? 'pointer-events-none opacity-50' : '' ?>">
                                    <span class="sr-only">Next</span>
                                    <i data-lucide="chevron-right" class="h-5 w-5"></i>
                                </a>
                            </nav>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<script>
function toggleBulkSelect(source) {
    checkboxes = document.getElementsByName('risk_ids[]');
    let count = 0;
    for(var i=0, n=checkboxes.length;i<n;i++) {
        checkboxes[i].checked = source.checked;
        if(checkboxes[i].checked) count++;
    }
    updateBulkUI(count);
}

function updateBulkSelect() {
    checkboxes = document.getElementsByName('risk_ids[]');
    let count = 0;
    for(var i=0, n=checkboxes.length;i<n;i++) {
        if(checkboxes[i].checked) count++;
    }
    updateBulkUI(count);
    
    // Update master checkbox state
    let master = document.getElementById('selectAll');
    if (master) {
        master.checked = (count === checkboxes.length && count > 0);
    }
}

function updateBulkUI(count) {
    const controls = document.getElementById('bulkControls');
    const countDisplay = document.getElementById('selectedCount');
    if(count > 0) {
        controls.classList.remove('hidden');
        countDisplay.innerText = count;
    } else {
        controls.classList.add('hidden');
    }
}

function submitBulk(action) {
    if(selectedRisks.length === 0) return;
    
    confirmAction('Are you sure you want to perform this action on the selected risks?', () => {
        document.getElementById('bulk_action_input').value = action;
        document.getElementById('bulkForm').submit();
    });
}

function submitBulkWithVal(action, selectId) {
    if(selectedRisks.length === 0) return;
    const val = document.getElementById(selectId).value;
    if(!val) {
        alert('Please select a value first.');
        return;
    }
    
    confirmAction('Are you sure you want to apply this change to the selected risks?', () => {
        document.getElementById('bulk_action_input').value = action + ':' + val;
        document.getElementById('bulkForm').submit();
    });
}
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
