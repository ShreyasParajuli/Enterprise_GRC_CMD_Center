<?php
require_once __DIR__ . '/../../config/init.php';
requireLogin();

// Fetch Lookups
$departments = $pdo->query("SELECT id, name FROM departments ORDER BY name")->fetchAll();
$users = $pdo->query("SELECT id, username FROM users WHERE status = 'active' ORDER BY username")->fetchAll();

// Auto-generate next Audit ID
$lastId = $pdo->query("SELECT id FROM audits ORDER BY id DESC LIMIT 1")->fetchColumn();
$nextNum = str_pad(($lastId ?: 0) + 1, 3, '0', STR_PAD_LEFT);
$defaultAuditId = "AUD-" . date('Y') . "-" . $nextNum;

$pageTitle = 'Schedule New Audit Engagement';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>

<div class="flex-1 flex flex-col min-w-0 overflow-hidden">
    <?php require_once __DIR__ . '/../../includes/navbar.php'; ?>

    <main class="flex-1 overflow-y-auto p-6 bg-[#FFFAF3]">
        <div class="max-w-4xl mx-auto space-y-6">

            <!-- Breadcrumbs -->
            <nav class="text-xs font-semibold text-brand-dark/50 uppercase tracking-wider" aria-label="Breadcrumb">
                <ol class="list-none p-0 inline-flex items-center space-x-2">
                    <li><a href="<?= BASE_URL ?>/pages/dashboard.php" class="hover:text-brand-primary">Dashboard</a></li>
                    <li><i data-lucide="chevron-right" class="w-3.5 h-3.5"></i></li>
                    <li><a href="<?= BASE_URL ?>/modules/audits/index.php" class="hover:text-brand-primary">Audits</a></li>
                    <li><i data-lucide="chevron-right" class="w-3.5 h-3.5"></i></li>
                    <li class="text-brand-dark">New Engagement</li>
                </ol>
            </nav>

            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-[#232426]">Schedule New Audit Engagement</h2>
                    <p class="text-sm text-[#232426]/70 mt-0.5">Initialize a new compliance evaluation, certification, or operational audit.</p>
                </div>
                <a href="<?= BASE_URL ?>/modules/audits/index.php" class="px-4 py-2 bg-white border border-[#232426]/10 text-sm font-semibold text-brand-dark rounded-lg hover:bg-slate-50 transition-colors shadow-sm">
                    Cancel
                </a>
            </div>

            <!-- Form Card -->
            <div class="bg-white rounded-xl border border-[#232426]/10 shadow-sm p-6 sm:p-8">
                <form action="<?= BASE_URL ?>/modules/audits/save.php" method="POST" class="space-y-6">
                    <input type="hidden" name="action" value="create">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Audit Identifier -->
                        <div>
                            <label class="block text-xs font-bold text-[#232426]/70 uppercase tracking-wider mb-2">Audit Identifier *</label>
                            <input type="text" name="audit_id" required value="<?= htmlspecialchars($defaultAuditId) ?>"
                                   class="w-full px-4 py-2.5 border border-[#232426]/15 rounded-lg text-sm font-mono focus:border-brand-primary focus:outline-none focus:ring-2 focus:ring-brand-primary/20">
                            <span class="text-[11px] text-[#232426]/50 mt-1 block">Unique formal reference code</span>
                        </div>

                        <!-- Audit Type -->
                        <div>
                            <label class="block text-xs font-bold text-[#232426]/70 uppercase tracking-wider mb-2">Engagement Type *</label>
                            <select name="audit_type" required class="w-full px-4 py-2.5 border border-[#232426]/15 rounded-lg text-sm bg-white focus:border-brand-primary focus:outline-none">
                                <option value="Internal">Internal Audit</option>
                                <option value="External">External Independent Audit</option>
                                <option value="Certification">Accredited Certification Audit</option>
                                <option value="Regulatory">Regulatory Mandate</option>
                                <option value="Third-Party">Third-Party Vendor Review</option>
                            </select>
                        </div>
                    </div>

                    <!-- Title -->
                    <div>
                        <label class="block text-xs font-bold text-[#232426]/70 uppercase tracking-wider mb-2">Engagement Title *</label>
                        <input type="text" name="title" required placeholder="e.g. ISO/IEC 27001 Annual Surveillance Audit"
                               class="w-full px-4 py-2.5 border border-[#232426]/15 rounded-lg text-sm focus:border-brand-primary focus:outline-none focus:ring-2 focus:ring-brand-primary/20">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Framework -->
                        <div>
                            <label class="block text-xs font-bold text-[#232426]/70 uppercase tracking-wider mb-2">Compliance Framework *</label>
                            <input type="text" name="framework" required placeholder="e.g. ISO/IEC 27001, SOC 2, NIST CSF"
                                   list="framework-presets"
                                   class="w-full px-4 py-2.5 border border-[#232426]/15 rounded-lg text-sm focus:border-brand-primary focus:outline-none">
                            <datalist id="framework-presets">
                                <option value="ISO/IEC 27001">
                                <option value="SOC 2 Type II">
                                <option value="NIST CSF v2.0">
                                <option value="PCI-DSS v4.0">
                                <option value="GDPR / Privacy">
                                <option value="HIPAA Security Rule">
                                <option value="CIS Controls v8">
                            </datalist>
                        </div>

                        <!-- Audited Department -->
                        <div>
                            <label class="block text-xs font-bold text-[#232426]/70 uppercase tracking-wider mb-2">Audited Department *</label>
                            <select name="department_id" required class="w-full px-4 py-2.5 border border-[#232426]/15 rounded-lg text-sm bg-white focus:border-brand-primary focus:outline-none">
                                <option value="">-- Select Department --</option>
                                <?php foreach ($departments as $d): ?>
                                    <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Lead Auditor -->
                        <div>
                            <label class="block text-xs font-bold text-[#232426]/70 uppercase tracking-wider mb-2">Lead Auditor *</label>
                            <select name="lead_auditor_id" required class="w-full px-4 py-2.5 border border-[#232426]/15 rounded-lg text-sm bg-white focus:border-brand-primary focus:outline-none">
                                <option value="">-- Select Auditor --</option>
                                <?php foreach ($users as $u): ?>
                                    <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['username']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Dates -->
                        <div>
                            <label class="block text-xs font-bold text-[#232426]/70 uppercase tracking-wider mb-2">Fieldwork Start Date</label>
                            <input type="date" name="start_date" value="<?= date('Y-m-d') ?>"
                                   class="w-full px-4 py-2.5 border border-[#232426]/15 rounded-lg text-sm focus:border-brand-primary focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-[#232426]/70 uppercase tracking-wider mb-2">Expected Completion Date</label>
                            <input type="date" name="end_date" value="<?= date('Y-m-d', strtotime('+30 days')) ?>"
                                   class="w-full px-4 py-2.5 border border-[#232426]/15 rounded-lg text-sm focus:border-brand-primary focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Initial Status -->
                        <div>
                            <label class="block text-xs font-bold text-[#232426]/70 uppercase tracking-wider mb-2">Initial Status</label>
                            <select name="status" class="w-full px-4 py-2.5 border border-[#232426]/15 rounded-lg text-sm bg-white focus:border-brand-primary focus:outline-none">
                                <option value="Planned" selected>Planned</option>
                                <option value="Fieldwork">In Fieldwork</option>
                                <option value="Review">Under Review</option>
                                <option value="Finalized">Finalized</option>
                            </select>
                        </div>

                        <!-- Initial Rating -->
                        <div>
                            <label class="block text-xs font-bold text-[#232426]/70 uppercase tracking-wider mb-2">Report Rating</label>
                            <select name="report_rating" class="w-full px-4 py-2.5 border border-[#232426]/15 rounded-lg text-sm bg-white focus:border-brand-primary focus:outline-none">
                                <option value="Pending Rating" selected>Pending Rating</option>
                                <option value="Satisfactory">Satisfactory</option>
                                <option value="Needs Improvement">Needs Improvement</option>
                                <option value="Unsatisfactory">Unsatisfactory</option>
                            </select>
                        </div>
                    </div>

                    <!-- Scope & Methodological Boundary -->
                    <div>
                        <label class="block text-xs font-bold text-[#232426]/70 uppercase tracking-wider mb-2">Audit Scope & Systems Boundary</label>
                        <textarea name="scope" rows="3" placeholder="Define in-scope systems, data repositories, cloud accounts, and organizational units..."
                                  class="w-full px-4 py-2.5 border border-[#232426]/15 rounded-lg text-sm focus:border-brand-primary focus:outline-none focus:ring-2 focus:ring-brand-primary/20"></textarea>
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="block text-xs font-bold text-[#232426]/70 uppercase tracking-wider mb-2">Objectives & Background Notes</label>
                        <textarea name="description" rows="3" placeholder="Context, previous findings to follow up, or special audit directives..."
                                  class="w-full px-4 py-2.5 border border-[#232426]/15 rounded-lg text-sm focus:border-brand-primary focus:outline-none focus:ring-2 focus:ring-brand-primary/20"></textarea>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-[#232426]/10">
                        <a href="<?= BASE_URL ?>/modules/audits/index.php" class="px-5 py-2.5 border border-[#232426]/15 rounded-lg text-sm font-semibold text-brand-dark hover:bg-slate-50 transition-colors">
                            Cancel
                        </a>
                        <button type="submit" class="px-6 py-2.5 bg-brand-primary text-white text-sm font-semibold rounded-lg hover:opacity-95 shadow-md shadow-brand-primary/20 transition-all flex items-center gap-2">
                            <i data-lucide="check" class="w-4 h-4"></i>
                            Create Engagement
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
