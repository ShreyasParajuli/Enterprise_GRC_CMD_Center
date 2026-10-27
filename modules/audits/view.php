<?php
require_once __DIR__ . '/../../config/init.php';
requireLogin();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "Invalid audit engagement ID.";
    header("Location: " . BASE_URL . "/modules/audits/index.php");
    exit;
}

$auditId = (int)$_GET['id'];

// Fetch Audit Engagement Details
$stmt = $pdo->prepare("
    SELECT a.*, 
           d.name AS department_name, 
           u.username AS auditor_name,
           u.email AS auditor_email
    FROM audits a
    LEFT JOIN departments d ON a.department_id = d.id
    LEFT JOIN users u ON a.lead_auditor_id = u.id
    WHERE a.id = ?
");
$stmt->execute([$auditId]);
$audit = $stmt->fetch();

if (!$audit) {
    $_SESSION['error'] = "Audit engagement not found.";
    header("Location: " . BASE_URL . "/modules/audits/index.php");
    exit;
}

require_once __DIR__ . '/helpers/audit_helper.php';

// Fetch Findings for this Audit
$stmt = $pdo->prepare("
    SELECT f.*, 
           r.risk_id, r.title AS risk_title,
           ast.asset_id AS asset_code, ast.asset_name,
           u.username AS assignee_name
    FROM audit_findings f
    LEFT JOIN risks r ON f.risk_id = r.id
    LEFT JOIN assets ast ON f.asset_id = ast.id
    LEFT JOIN users u ON f.assigned_to = u.id
    WHERE f.audit_id = ?
    ORDER BY FIELD(f.severity, 'Critical', 'High', 'Medium', 'Low'), f.id DESC
");
$stmt->execute([$auditId]);
$findings = $stmt->fetchAll();

// Fetch Control Checklists
$stmt = $pdo->prepare("SELECT * FROM audit_checklists WHERE audit_id = ? ORDER BY control_code ASC");
$stmt->execute([$auditId]);
$checklists = $stmt->fetchAll();

// Calculate Checklist Compliance Rate
$totalControls = count($checklists);
$compliantCount = 0;
foreach ($checklists as $c) {
    if ($c['status'] === 'Compliant') $compliantCount++;
}
$complianceRate = $totalControls > 0 ? round(($compliantCount / $totalControls) * 100) : 0;

// Fetch Lookup Lists for Add Finding Modal
$users = $pdo->query("SELECT id, username FROM users WHERE status = 'active' ORDER BY username")->fetchAll();
$risks = $pdo->query("SELECT id, risk_id, title FROM risks ORDER BY risk_id")->fetchAll();
$assets = $pdo->query("SELECT id, asset_id, asset_name FROM assets ORDER BY asset_id")->fetchAll();

$pageTitle = "Audit Dossier: " . htmlspecialchars($audit['audit_id']);
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>

<div class="flex-1 flex flex-col min-w-0 overflow-hidden">
    <?php require_once __DIR__ . '/../../includes/navbar.php'; ?>

    <main class="flex-1 overflow-y-auto p-6 bg-[#FFFAF3]">
        <div class="max-w-7xl mx-auto space-y-6">

            <!-- Breadcrumbs & Header -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <nav class="text-xs font-semibold text-brand-dark/50 uppercase tracking-wider mb-1" aria-label="Breadcrumb">
                        <ol class="list-none p-0 inline-flex items-center space-x-2">
                            <li><a href="<?= BASE_URL ?>/pages/dashboard.php" class="hover:text-brand-primary">Dashboard</a></li>
                            <li><i data-lucide="chevron-right" class="w-3.5 h-3.5"></i></li>
                            <li><a href="<?= BASE_URL ?>/modules/audits/index.php" class="hover:text-brand-primary">Audits</a></li>
                            <li><i data-lucide="chevron-right" class="w-3.5 h-3.5"></i></li>
                            <li class="text-brand-dark"><?= htmlspecialchars($audit['audit_id']) ?></li>
                        </ol>
                    </nav>
                    <div class="flex items-center gap-3">
                        <h2 class="text-2xl font-bold text-[#232426]"><?= htmlspecialchars($audit['title']) ?></h2>
                        <span class="text-sm font-bold text-brand-primary bg-brand-primary/10 px-2.5 py-0.5 rounded-md border border-brand-primary/20">
                            <?= htmlspecialchars($audit['audit_id']) ?>
                        </span>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <a href="<?= BASE_URL ?>/modules/audits/index.php" class="inline-flex items-center px-3.5 py-2 rounded-lg bg-white border border-[#232426]/10 text-sm font-semibold text-brand-dark hover:bg-slate-50 transition-colors shadow-sm">
                        <i data-lucide="arrow-left" class="w-4 h-4 mr-1.5"></i>
                        All Audits
                    </a>
                    <button type="button" onclick="document.getElementById('modal-add-finding').classList.remove('hidden')" class="inline-flex items-center px-3.5 py-2 rounded-lg bg-red-600 text-white text-sm font-semibold hover:bg-red-700 shadow-sm transition-all">
                        <i data-lucide="alert-circle" class="w-4 h-4 mr-1.5"></i>
                        Log Finding
                    </button>
                    <a href="<?= BASE_URL ?>/modules/audits/edit.php?id=<?= $audit['id'] ?>" class="inline-flex items-center px-4 py-2 rounded-lg bg-brand-primary text-white text-sm font-semibold hover:opacity-95 shadow-md shadow-brand-primary/20 transition-all">
                        <i data-lucide="edit-3" class="w-4 h-4 mr-1.5"></i>
                        Edit Audit
                    </a>
                </div>
            </div>

            <!-- Executive Dossier Overview Card -->
            <div class="bg-white rounded-xl border border-[#232426]/10 shadow-sm p-6 space-y-6">
                
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6 border-b border-[#232426]/10 pb-6">
                    <div>
                        <p class="text-xs font-semibold text-[#232426]/50 uppercase tracking-wider">Audit Type & Framework</p>
                        <p class="text-base font-bold text-[#232426] mt-1 flex items-center gap-2">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-slate-100 text-slate-800 border border-slate-200">
                                <?= htmlspecialchars($audit['audit_type']) ?>
                            </span>
                            <span><?= htmlspecialchars($audit['framework']) ?></span>
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-semibold text-[#232426]/50 uppercase tracking-wider">Audited Department</p>
                        <p class="text-base font-bold text-[#232426] mt-1 flex items-center gap-1.5">
                            <i data-lucide="building" class="w-4 h-4 text-slate-400"></i>
                            <?= htmlspecialchars($audit['department_name'] ?: 'Organization-wide') ?>
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-semibold text-[#232426]/50 uppercase tracking-wider">Lead Auditor</p>
                        <p class="text-base font-bold text-[#232426] mt-1 flex items-center gap-1.5">
                            <i data-lucide="user-check" class="w-4 h-4 text-brand-primary"></i>
                            <?= htmlspecialchars($audit['auditor_name'] ?: 'Unassigned') ?>
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-semibold text-[#232426]/50 uppercase tracking-wider">Status & Evaluation</p>
                        <div class="mt-1 flex items-center gap-2">
                            <?= getAuditStatusBadge($audit['status']) ?>
                            <?= getAuditRatingBadge($audit['report_rating']) ?>
                        </div>
                    </div>
                </div>

                <!-- 4 Performance Metric Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="bg-slate-50 p-4 rounded-lg border border-slate-200/70">
                        <p class="text-xs font-semibold text-[#232426]/60 uppercase">Controls Tested</p>
                        <p class="text-xl font-bold text-[#232426] mt-0.5"><?= $totalControls ?></p>
                        <p class="text-xs text-emerald-600 font-medium mt-0.5"><?= $complianceRate ?>% Compliant</p>
                    </div>

                    <div class="bg-slate-50 p-4 rounded-lg border border-slate-200/70">
                        <p class="text-xs font-semibold text-[#232426]/60 uppercase">Total Findings</p>
                        <p class="text-xl font-bold text-[#232426] mt-0.5"><?= count($findings) ?></p>
                        <p class="text-xs text-red-600 font-medium mt-0.5">
                            <?php 
                                $openCnt = 0; 
                                foreach($findings as $f) { if ($f['status'] !== 'Closed') $openCnt++; }
                                echo "{$openCnt} Open Deficiencies";
                            ?>
                        </p>
                    </div>

                    <div class="bg-slate-50 p-4 rounded-lg border border-slate-200/70">
                        <p class="text-xs font-semibold text-[#232426]/60 uppercase">Engagement Start</p>
                        <p class="text-xl font-bold text-[#232426] mt-0.5">
                            <?= $audit['start_date'] ? date('M j, Y', strtotime($audit['start_date'])) : 'Pending' ?>
                        </p>
                        <p class="text-xs text-[#232426]/50 mt-0.5">Fieldwork Inception</p>
                    </div>

                    <div class="bg-slate-50 p-4 rounded-lg border border-slate-200/70">
                        <p class="text-xs font-semibold text-[#232426]/60 uppercase">Engagement Target</p>
                        <p class="text-xl font-bold text-[#232426] mt-0.5">
                            <?= $audit['end_date'] ? date('M j, Y', strtotime($audit['end_date'])) : 'Open' ?>
                        </p>
                        <p class="text-xs text-[#232426]/50 mt-0.5">Final Report Delivery</p>
                    </div>
                </div>

                <!-- Scope & Description -->
                <?php if (!empty($audit['scope']) || !empty($audit['description'])): ?>
                    <div class="bg-[#FFFAF3] p-4 rounded-lg border border-brand-dark/10">
                        <h4 class="text-xs font-bold text-[#232426]/70 uppercase tracking-wider mb-1.5 flex items-center gap-1.5">
                            <i data-lucide="target" class="w-4 h-4 text-brand-primary"></i>
                            Audit Scope & Methodological Boundary
                        </h4>
                        <p class="text-sm text-[#232426]/80 leading-relaxed">
                            <?= nl2br(htmlspecialchars($audit['scope'] ?: $audit['description'])) ?>
                        </p>
                    </div>
                <?php endif; ?>

            </div>

            <!-- Tabbed / Multi-Section Container: Findings & Controls Checklist -->
            <div class="space-y-6">

                <!-- 1. Audit Findings Section -->
                <div class="bg-white rounded-xl border border-[#232426]/10 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-[#232426]/10 flex items-center justify-between bg-slate-50/60">
                        <div class="flex items-center gap-3">
                            <div class="p-2 rounded-lg bg-red-100 text-red-700">
                                <i data-lucide="alert-octagon" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-[#232426]">Deficiencies & Findings Register</h3>
                                <p class="text-xs text-[#232426]/60">Identified non-conformances, control gaps, and risk exposure.</p>
                            </div>
                        </div>
                        <button type="button" onclick="document.getElementById('modal-add-finding').classList.remove('hidden')" class="px-3.5 py-1.5 bg-red-600 text-white text-xs font-semibold rounded-lg hover:bg-red-700 transition-colors inline-flex items-center shadow-sm">
                            <i data-lucide="plus" class="w-3.5 h-3.5 mr-1"></i> Add Finding
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-[#232426]/10 text-sm">
                            <thead class="bg-slate-50/80">
                                <tr>
                                    <th class="px-5 py-3 text-left text-xs font-bold text-[#232426]/60 uppercase">Finding ID</th>
                                    <th class="px-5 py-3 text-left text-xs font-bold text-[#232426]/60 uppercase">Deficiency Title & Details</th>
                                    <th class="px-5 py-3 text-left text-xs font-bold text-[#232426]/60 uppercase">Severity</th>
                                    <th class="px-5 py-3 text-left text-xs font-bold text-[#232426]/60 uppercase">Remediation Status</th>
                                    <th class="px-5 py-3 text-left text-xs font-bold text-[#232426]/60 uppercase">Linked Risk / Asset</th>
                                    <th class="px-5 py-3 text-left text-xs font-bold text-[#232426]/60 uppercase">Assignee & Due Date</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#232426]/5 bg-white">
                                <?php if (empty($findings)): ?>
                                    <tr>
                                        <td colspan="6" class="px-6 py-8 text-center text-[#232426]/50">
                                            <i data-lucide="check-check" class="w-8 h-8 mx-auto text-emerald-400 mb-1"></i>
                                            <p class="font-semibold text-sm text-[#232426]">No Findings Logged</p>
                                            <p class="text-xs text-[#232426]/50">Zero non-conformances currently registered under this audit.</p>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($findings as $f): ?>
                                        <tr class="hover:bg-slate-50/60 transition-colors">
                                            <td class="px-5 py-3.5 font-bold text-xs text-red-600 whitespace-nowrap">
                                                <?= htmlspecialchars($f['finding_id']) ?>
                                            </td>
                                            <td class="px-5 py-3.5">
                                                <p class="font-semibold text-[#232426]"><?= htmlspecialchars($f['title']) ?></p>
                                                <?php if (!empty($f['description'])): ?>
                                                    <p class="text-xs text-[#232426]/60 line-clamp-2 mt-0.5"><?= htmlspecialchars($f['description']) ?></p>
                                                <?php endif; ?>
                                                <?php if (!empty($f['remediation_plan'])): ?>
                                                    <div class="text-[11px] text-blue-700 bg-blue-50 px-2 py-1 rounded mt-1.5 border border-blue-200/60">
                                                        <strong>Remediation:</strong> <?= htmlspecialchars($f['remediation_plan']) ?>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-5 py-3.5 whitespace-nowrap">
                                                <?= getFindingSeverityBadge($f['severity']) ?>
                                            </td>
                                            <td class="px-5 py-3.5 whitespace-nowrap">
                                                <?= getFindingStatusBadge($f['status']) ?>
                                            </td>
                                            <td class="px-5 py-3.5 whitespace-nowrap text-xs">
                                                <?php if ($f['risk_id']): ?>
                                                    <a href="<?= BASE_URL ?>/modules/risks/view.php?id=<?= $f['risk_id'] ?>" class="text-brand-primary hover:underline font-semibold block">
                                                        <i data-lucide="alert-triangle" class="w-3 h-3 inline mr-0.5"></i> <?= htmlspecialchars($f['risk_id_code'] ?? 'Risk Link') ?>
                                                    </a>
                                                <?php endif; ?>
                                                <?php if ($f['asset_id']): ?>
                                                    <a href="<?= BASE_URL ?>/modules/assets/view.php?id=<?= $f['asset_id'] ?>" class="text-blue-600 hover:underline font-medium block mt-0.5">
                                                        <i data-lucide="server" class="w-3 h-3 inline mr-0.5"></i> <?= htmlspecialchars($f['asset_code'] ?? 'Asset Link') ?>
                                                    </a>
                                                <?php endif; ?>
                                                <?php if (!$f['risk_id'] && !$f['asset_id']): ?>
                                                    <span class="text-[#232426]/40">General</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-5 py-3.5 whitespace-nowrap text-xs text-[#232426]/70">
                                                <div class="font-semibold text-[#232426]"><?= htmlspecialchars($f['assignee_name'] ?: 'Unassigned') ?></div>
                                                <div class="text-[11px] text-[#232426]/50">Due: <?= $f['due_date'] ? date('M j, Y', strtotime($f['due_date'])) : 'No date' ?></div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- 2. Controls Testing Checklist Section -->
                <div class="bg-white rounded-xl border border-[#232426]/10 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-[#232426]/10 flex items-center justify-between bg-slate-50/60">
                        <div class="flex items-center gap-3">
                            <div class="p-2 rounded-lg bg-blue-100 text-blue-700">
                                <i data-lucide="list-checks" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-[#232426]">Control Verification Matrix (Checklist)</h3>
                                <p class="text-xs text-[#232426]/60">Specific compliance controls evaluated against <?= htmlspecialchars($audit['framework']) ?>.</p>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-[#232426]/10 text-sm">
                            <thead class="bg-slate-50/80">
                                <tr>
                                    <th class="px-5 py-3 text-left text-xs font-bold text-[#232426]/60 uppercase">Control Code</th>
                                    <th class="px-5 py-3 text-left text-xs font-bold text-[#232426]/60 uppercase">Control Title & Evaluation Criterion</th>
                                    <th class="px-5 py-3 text-left text-xs font-bold text-[#232426]/60 uppercase">Verification Result</th>
                                    <th class="px-5 py-3 text-left text-xs font-bold text-[#232426]/60 uppercase">Auditor Notes & Evidence</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#232426]/5 bg-white">
                                <?php if (empty($checklists)): ?>
                                    <tr>
                                        <td colspan="4" class="px-6 py-8 text-center text-[#232426]/50">
                                            <p class="font-semibold text-sm text-[#232426]">No Checklist Controls Defined</p>
                                            <p class="text-xs text-[#232426]/50">No control matrix items currently mapped for this framework.</p>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($checklists as $item): ?>
                                        <tr class="hover:bg-slate-50/60 transition-colors">
                                            <td class="px-5 py-3.5 font-bold text-xs text-slate-800 whitespace-nowrap">
                                                <span class="px-2 py-0.5 rounded bg-slate-100 border border-slate-200">
                                                    <?= htmlspecialchars($item['control_code']) ?>
                                                </span>
                                            </td>
                                            <td class="px-5 py-3.5 font-semibold text-[#232426]">
                                                <?= htmlspecialchars($item['control_title']) ?>
                                            </td>
                                            <td class="px-5 py-3.5 whitespace-nowrap">
                                                <?= getControlStatusBadge($item['status']) ?>
                                            </td>
                                            <td class="px-5 py-3.5 text-xs text-[#232426]/70">
                                                <?= htmlspecialchars($item['notes'] ?: 'No additional notes logged.') ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

        </div>
    </main>
</div>

<!-- Add Finding Modal -->
<div id="modal-add-finding" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-brand-dark/50 backdrop-blur-sm transition-opacity" onclick="document.getElementById('modal-add-finding').classList.add('hidden')"></div>
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="relative transform overflow-hidden rounded-xl bg-white text-left shadow-2xl transition-all sm:w-full sm:max-w-lg border border-brand-dark/10">
            <form action="<?= BASE_URL ?>/modules/audits/save_finding.php" method="POST">
                <input type="hidden" name="audit_id" value="<?= $audit['id'] ?>">

                <div class="bg-white px-6 pt-5 pb-4">
                    <div class="flex items-center justify-between pb-3 border-b border-[#232426]/10 mb-4">
                        <h3 class="text-lg font-bold text-[#232426] flex items-center gap-2">
                            <i data-lucide="alert-octagon" class="w-5 h-5 text-red-600"></i>
                            Log Audit Finding / Deficiency
                        </h3>
                        <button type="button" onclick="document.getElementById('modal-add-finding').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-[#232426]/70 uppercase mb-1">Finding Title *</label>
                            <input type="text" name="title" required placeholder="e.g. Unencrypted S3 Storage Bucket Detected"
                                   class="w-full px-3 py-2 border border-[#232426]/15 rounded-lg text-sm focus:border-brand-primary focus:outline-none">
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-[#232426]/70 uppercase mb-1">Severity *</label>
                                <select name="severity" required class="w-full px-3 py-2 border border-[#232426]/15 rounded-lg text-sm bg-white">
                                    <option value="Critical">Critical</option>
                                    <option value="High">High</option>
                                    <option value="Medium" selected>Medium</option>
                                    <option value="Low">Low</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-[#232426]/70 uppercase mb-1">Remediation Status</label>
                                <select name="status" required class="w-full px-3 py-2 border border-[#232426]/15 rounded-lg text-sm bg-white">
                                    <option value="Open" selected>Open</option>
                                    <option value="Under Remediation">Under Remediation</option>
                                    <option value="Pending Verification">Pending Verification</option>
                                    <option value="Closed">Closed</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-[#232426]/70 uppercase mb-1">Deficiency Description</label>
                            <textarea name="description" rows="2" placeholder="Details of the non-compliance observed..."
                                      class="w-full px-3 py-2 border border-[#232426]/15 rounded-lg text-sm focus:border-brand-primary focus:outline-none"></textarea>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-[#232426]/70 uppercase mb-1">Remediation Plan & Corrective Action</label>
                            <textarea name="remediation_plan" rows="2" placeholder="Steps required to fix and verify compliance..."
                                      class="w-full px-3 py-2 border border-[#232426]/15 rounded-lg text-sm focus:border-brand-primary focus:outline-none"></textarea>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-[#232426]/70 uppercase mb-1">Assignee</label>
                                <select name="assigned_to" class="w-full px-3 py-2 border border-[#232426]/15 rounded-lg text-sm bg-white">
                                    <option value="">-- Unassigned --</option>
                                    <?php foreach ($users as $u): ?>
                                        <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['username']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-[#232426]/70 uppercase mb-1">Remediation Due Date</label>
                                <input type="date" name="due_date"
                                       class="w-full px-3 py-2 border border-[#232426]/15 rounded-lg text-sm focus:border-brand-primary focus:outline-none">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-[#232426]/70 uppercase mb-1">Link to Risk (Optional)</label>
                                <select name="risk_id" class="w-full px-3 py-2 border border-[#232426]/15 rounded-lg text-xs bg-white">
                                    <option value="">-- None --</option>
                                    <?php foreach ($risks as $r): ?>
                                        <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['risk_id'] . ' - ' . substr($r['title'], 0, 25)) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-[#232426]/70 uppercase mb-1">Link to Asset (Optional)</label>
                                <select name="asset_id" class="w-full px-3 py-2 border border-[#232426]/15 rounded-lg text-xs bg-white">
                                    <option value="">-- None --</option>
                                    <?php foreach ($assets as $ast): ?>
                                        <option value="<?= $ast['id'] ?>"><?= htmlspecialchars($ast['asset_id'] . ' - ' . substr($ast['asset_name'], 0, 25)) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-slate-50 px-6 py-3.5 flex justify-end gap-2 border-t border-[#232426]/10">
                    <button type="button" onclick="document.getElementById('modal-add-finding').classList.add('hidden')" class="px-4 py-2 bg-white border border-[#232426]/15 text-sm font-semibold rounded-lg text-brand-dark hover:bg-slate-50">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white text-sm font-semibold rounded-lg hover:bg-red-700">
                        Save Finding
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
