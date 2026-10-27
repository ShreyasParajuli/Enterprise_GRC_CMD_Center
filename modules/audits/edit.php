<?php
require_once __DIR__ . '/../../config/init.php';
requireLogin();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "Invalid audit engagement ID.";
    header("Location: " . BASE_URL . "/modules/audits/index.php");
    exit;
}

$auditId = (int)$_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM audits WHERE id = ?");
$stmt->execute([$auditId]);
$audit = $stmt->fetch();

if (!$audit) {
    $_SESSION['error'] = "Audit engagement not found.";
    header("Location: " . BASE_URL . "/modules/audits/index.php");
    exit;
}

// Fetch Lookups
$departments = $pdo->query("SELECT id, name FROM departments ORDER BY name")->fetchAll();
$users = $pdo->query("SELECT id, username FROM users WHERE status = 'active' ORDER BY username")->fetchAll();

$pageTitle = 'Edit Audit: ' . htmlspecialchars($audit['audit_id']);
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
                    <li><a href="<?= BASE_URL ?>/modules/audits/view.php?id=<?= $audit['id'] ?>" class="hover:text-brand-primary"><?= htmlspecialchars($audit['audit_id']) ?></a></li>
                    <li><i data-lucide="chevron-right" class="w-3.5 h-3.5"></i></li>
                    <li class="text-brand-dark">Edit</li>
                </ol>
            </nav>

            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-[#232426]">Edit Audit: <?= htmlspecialchars($audit['audit_id']) ?></h2>
                    <p class="text-sm text-[#232426]/70 mt-0.5">Update scope, fieldwork progress, status, and evaluation rating.</p>
                </div>
                <a href="<?= BASE_URL ?>/modules/audits/view.php?id=<?= $audit['id'] ?>" class="px-4 py-2 bg-white border border-[#232426]/10 text-sm font-semibold text-brand-dark rounded-lg hover:bg-slate-50 transition-colors shadow-sm">
                    Back to Dossier
                </a>
            </div>

            <!-- Form Card -->
            <div class="bg-white rounded-xl border border-[#232426]/10 shadow-sm p-6 sm:p-8">
                <form action="<?= BASE_URL ?>/modules/audits/save.php" method="POST" class="space-y-6">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" value="<?= $audit['id'] ?>">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Audit Identifier -->
                        <div>
                            <label class="block text-xs font-bold text-[#232426]/70 uppercase tracking-wider mb-2">Audit Identifier *</label>
                            <input type="text" name="audit_id" required value="<?= htmlspecialchars($audit['audit_id']) ?>"
                                   class="w-full px-4 py-2.5 border border-[#232426]/15 rounded-lg text-sm font-mono focus:border-brand-primary focus:outline-none focus:ring-2 focus:ring-brand-primary/20">
                        </div>

                        <!-- Audit Type -->
                        <div>
                            <label class="block text-xs font-bold text-[#232426]/70 uppercase tracking-wider mb-2">Engagement Type *</label>
                            <select name="audit_type" required class="w-full px-4 py-2.5 border border-[#232426]/15 rounded-lg text-sm bg-white focus:border-brand-primary focus:outline-none">
                                <?php foreach (['Internal', 'External', 'Certification', 'Regulatory', 'Third-Party'] as $type): ?>
                                    <option value="<?= $type ?>" <?= $audit['audit_type'] === $type ? 'selected' : '' ?>><?= $type ?> Audit</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Title -->
                    <div>
                        <label class="block text-xs font-bold text-[#232426]/70 uppercase tracking-wider mb-2">Engagement Title *</label>
                        <input type="text" name="title" required value="<?= htmlspecialchars($audit['title']) ?>"
                               class="w-full px-4 py-2.5 border border-[#232426]/15 rounded-lg text-sm focus:border-brand-primary focus:outline-none focus:ring-2 focus:ring-brand-primary/20">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Framework -->
                        <div>
                            <label class="block text-xs font-bold text-[#232426]/70 uppercase tracking-wider mb-2">Compliance Framework *</label>
                            <input type="text" name="framework" required value="<?= htmlspecialchars($audit['framework']) ?>"
                                   class="w-full px-4 py-2.5 border border-[#232426]/15 rounded-lg text-sm focus:border-brand-primary focus:outline-none">
                        </div>

                        <!-- Audited Department -->
                        <div>
                            <label class="block text-xs font-bold text-[#232426]/70 uppercase tracking-wider mb-2">Audited Department *</label>
                            <select name="department_id" required class="w-full px-4 py-2.5 border border-[#232426]/15 rounded-lg text-sm bg-white focus:border-brand-primary focus:outline-none">
                                <?php foreach ($departments as $d): ?>
                                    <option value="<?= $d['id'] ?>" <?= $audit['department_id'] == $d['id'] ? 'selected' : '' ?>><?= htmlspecialchars($d['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Lead Auditor -->
                        <div>
                            <label class="block text-xs font-bold text-[#232426]/70 uppercase tracking-wider mb-2">Lead Auditor *</label>
                            <select name="lead_auditor_id" required class="w-full px-4 py-2.5 border border-[#232426]/15 rounded-lg text-sm bg-white focus:border-brand-primary focus:outline-none">
                                <?php foreach ($users as $u): ?>
                                    <option value="<?= $u['id'] ?>" <?= $audit['lead_auditor_id'] == $u['id'] ? 'selected' : '' ?>><?= htmlspecialchars($u['username']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Dates -->
                        <div>
                            <label class="block text-xs font-bold text-[#232426]/70 uppercase tracking-wider mb-2">Fieldwork Start Date</label>
                            <input type="date" name="start_date" value="<?= htmlspecialchars($audit['start_date'] ?? '') ?>"
                                   class="w-full px-4 py-2.5 border border-[#232426]/15 rounded-lg text-sm focus:border-brand-primary focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-[#232426]/70 uppercase tracking-wider mb-2">Expected Completion Date</label>
                            <input type="date" name="end_date" value="<?= htmlspecialchars($audit['end_date'] ?? '') ?>"
                                   class="w-full px-4 py-2.5 border border-[#232426]/15 rounded-lg text-sm focus:border-brand-primary focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Status -->
                        <div>
                            <label class="block text-xs font-bold text-[#232426]/70 uppercase tracking-wider mb-2">Audit Status</label>
                            <select name="status" class="w-full px-4 py-2.5 border border-[#232426]/15 rounded-lg text-sm bg-white focus:border-brand-primary focus:outline-none">
                                <?php foreach (['Planned', 'Fieldwork', 'Review', 'Finalized', 'Archived'] as $st): ?>
                                    <option value="<?= $st ?>" <?= $audit['status'] === $st ? 'selected' : '' ?>><?= $st ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Rating -->
                        <div>
                            <label class="block text-xs font-bold text-[#232426]/70 uppercase tracking-wider mb-2">Report Rating</label>
                            <select name="report_rating" class="w-full px-4 py-2.5 border border-[#232426]/15 rounded-lg text-sm bg-white focus:border-brand-primary focus:outline-none">
                                <?php foreach (['Pending Rating', 'Satisfactory', 'Needs Improvement', 'Unsatisfactory'] as $rt): ?>
                                    <option value="<?= $rt ?>" <?= $audit['report_rating'] === $rt ? 'selected' : '' ?>><?= $rt ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Scope -->
                    <div>
                        <label class="block text-xs font-bold text-[#232426]/70 uppercase tracking-wider mb-2">Audit Scope & Systems Boundary</label>
                        <textarea name="scope" rows="3" class="w-full px-4 py-2.5 border border-[#232426]/15 rounded-lg text-sm focus:border-brand-primary focus:outline-none"><?= htmlspecialchars($audit['scope'] ?? '') ?></textarea>
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="block text-xs font-bold text-[#232426]/70 uppercase tracking-wider mb-2">Objectives & Background Notes</label>
                        <textarea name="description" rows="3" class="w-full px-4 py-2.5 border border-[#232426]/15 rounded-lg text-sm focus:border-brand-primary focus:outline-none"><?= htmlspecialchars($audit['description'] ?? '') ?></textarea>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-[#232426]/10">
                        <a href="<?= BASE_URL ?>/modules/audits/view.php?id=<?= $audit['id'] ?>" class="px-5 py-2.5 border border-[#232426]/15 rounded-lg text-sm font-semibold text-brand-dark hover:bg-slate-50 transition-colors">
                            Cancel
                        </a>
                        <button type="submit" class="px-6 py-2.5 bg-brand-primary text-white text-sm font-semibold rounded-lg hover:opacity-95 shadow-md shadow-brand-primary/20 transition-all flex items-center gap-2">
                            <i data-lucide="save" class="w-4 h-4"></i>
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
