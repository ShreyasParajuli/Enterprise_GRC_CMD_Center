<?php
require_once __DIR__ . '/../../config/init.php';
requireLogin();

$pageTitle = 'Visual Workflow Engine & Orchestration Studio';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>

<!-- Workflow Studio Specific Styling Overrides -->
<link rel="stylesheet" href="<?= BASE_URL ?>/modules/workflows/css/style.css">

<div class="flex-1 flex flex-col min-w-0 overflow-hidden h-screen">
    <?php require_once __DIR__ . '/../../includes/navbar.php'; ?>

    <main class="flex-1 overflow-hidden relative flex flex-col bg-[#0c0d0f] p-0">
        
        <div id="app-container">

            <!-- ====================================================================
                 1. TOP NAVIGATION TOOLBAR
                 ==================================================================== -->
            <header class="wf-header">
                <div class="wf-header-left">
                    <span class="wf-logo-badge">
                        <i data-lucide="shield-alert" class="w-3.5 h-3.5 inline mr-1"></i>
                        GRC ENGINE
                    </span>
                    <input type="text" id="wf-title-input" class="wf-title-input" value="Security Assessment Approval" placeholder="Workflow Title...">
                    
                    <select id="template-select" class="wf-template-select" title="Load Pre-configured GRC Workflow Template">
                        <option value="security_assessment">Template: Security Assessment Approval</option>
                        <option value="incident_escalation">Template: Critical Incident Escalation</option>
                        <option value="vendor_review">Template: Third-Party Vendor Review</option>
                    </select>
                </div>

                <div class="wf-header-center">
                    <button id="btn-new" class="wf-btn" title="Create New Empty Workflow">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        New
                    </button>
                    <button id="btn-save" class="wf-btn" title="Save to Browser LocalStorage">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                        Save
                    </button>
                    <button id="btn-load" class="wf-btn" title="Restore from LocalStorage">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        Load
                    </button>
                    <div style="width: 1px; height: 20px; background: var(--border-color); margin: 0 4px;"></div>
                    <button id="btn-validate" class="wf-btn" title="Check Workflow Topology and Reachability">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        Validate
                    </button>
                    <button id="btn-run" class="wf-btn wf-btn-accent" title="Run Interactive Step-by-Step Simulation">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                        Run Workflow
                    </button>
                </div>

                <div class="wf-header-right">
                    <button id="btn-fullscreen" class="wf-btn" title="Toggle Fullscreen Canvas Focus">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7"/></svg>
                        Focus Mode
                    </button>
                    <button id="btn-export" class="wf-btn" title="Export Workflow as JSON file">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                        Export JSON
                    </button>
                    <button id="btn-import" class="wf-btn" title="Import Workflow from JSON file">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="15" x2="15" y2="15"/></svg>
                        Import JSON
                    </button>
                    <input type="file" id="file-input-import" accept=".json" style="display: none;">
                </div>
            </header>

            <!-- ====================================================================
                 2. MAIN WORKSPACE
                 ==================================================================== -->
            <div class="wf-main-body">

                <!-- LEFT SIDEBAR: NODE PALETTE -->
                <aside class="wf-sidebar-left">
                    <div class="wf-sidebar-header">
                        <span>GRC Elements</span>
                        <span style="font-size: 10px; color: var(--text-muted); font-weight: normal;">Drag & Drop</span>
                    </div>
                    <div id="wf-palette-list" class="wf-palette-list">
                        <!-- Dynamic rendering from nodes.js -->
                    </div>
                    <div class="wf-palette-hint">
                        <strong>Tactical Guide:</strong><br>
                        • Drag elements onto canvas or click to add.<br>
                        • Connect nodes by dragging right output port to left input.<br>
                        • Double-click route wire to label decision branches (YES / NO).
                    </div>
                </aside>

                <!-- CENTER: VISUAL CANVAS VIEWPORT -->
                <main id="wf-canvas" class="wf-canvas-viewport">
                    <!-- SVG Layer for connections -->
                    <svg id="wf-svg-layer" class="wf-svg-layer"></svg>

                    <!-- HTML Layer for draggable nodes -->
                    <div id="wf-nodes-layer" class="wf-nodes-layer"></div>

                    <!-- Canvas Zoom & Pan Controls -->
                    <div class="wf-canvas-controls">
                        <button id="btn-zoom-out" class="wf-btn wf-btn-sm" title="Zoom Out">-</button>
                        <span id="zoom-level-indicator" class="zoom-indicator">100%</span>
                        <button id="btn-zoom-in" class="wf-btn wf-btn-sm" title="Zoom In">+</button>
                        <button id="btn-zoom-reset" class="wf-btn wf-btn-sm" title="Reset View">Reset</button>
                    </div>
                </main>

                <!-- RIGHT SIDEBAR: PROPERTIES PANEL -->
                <aside class="wf-sidebar-right">
                    <div class="wf-sidebar-header">
                        <span>Element Properties</span>
                    </div>
                    <div class="wf-props-body">
                        <h3 id="props-title" class="wf-node-title" style="margin-bottom: 8px; color: var(--text-orange);">No Element Selected</h3>

                        <div class="form-group">
                            <label>Element Type</label>
                            <input type="text" id="prop-type" class="form-control" disabled value="">
                        </div>

                        <div class="form-group">
                            <label>Name</label>
                            <input type="text" id="prop-name" class="form-control" disabled placeholder="Node Name">
                        </div>

                        <div class="form-group">
                            <label>Description</label>
                            <textarea id="prop-desc" class="form-control" disabled placeholder="Description or control action details..."></textarea>
                        </div>

                        <div class="form-group">
                            <label>Assigned Role / Owner</label>
                            <input type="text" id="prop-role" class="form-control" disabled placeholder="e.g. Risk Lead, CISO, Security Analyst">
                        </div>

                        <div id="prop-label-container" class="form-group" style="display: none;">
                            <label>Branch Decision Label</label>
                            <input type="text" id="prop-label" class="form-control" placeholder="e.g. YES, NO, APPROVE, REJECT">
                        </div>

                        <div class="form-actions">
                            <button id="btn-save-props" class="wf-btn wf-btn-primary" disabled>Save Changes</button>
                            <button id="btn-delete-selected" class="wf-btn wf-btn-danger" disabled>Delete Element</button>
                        </div>
                    </div>
                </aside>
            </div>

            <!-- ====================================================================
                 3. BOTTOM STATUS & VALIDATION TELEMETRY PANEL
                 ==================================================================== -->
            <footer id="wf-bottom-panel" class="wf-bottom-panel">
                <div class="wf-bottom-header">
                    <div class="wf-bottom-title">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                        <span>GRC Orchestration Telemetry & Validation Console</span>
                        <span id="wf-status-badge" class="wf-badge">Ready</span>
                    </div>
                    <div style="display: flex; gap: 8px;">
                        <button id="btn-toggle-log" class="wf-btn wf-btn-sm" title="Collapse / Expand console drawer">Collapse</button>
                        <button id="btn-clear-log" class="wf-btn wf-btn-sm" title="Clear console output">Clear Log</button>
                    </div>
                </div>
                <div id="wf-log-output" class="wf-log-output">
                    <!-- Log items rendered dynamically -->
                </div>
            </footer>

            <!-- ====================================================================
                 4. SIMULATION DECISION BRANCH MODAL
                 ==================================================================== -->
            <div id="wf-decision-modal" class="wf-modal-overlay" style="display: none;">
                <div class="wf-modal-box">
                    <h2 id="decision-prompt-text" class="wf-modal-title">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--brand-primary)" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                        Risk Gateway Decision Required
                    </h2>
                    <p style="font-size: 13px; color: var(--text-muted);">Select which branch the orchestration engine should follow:</p>
                    <div id="decision-options-container" class="modal-options-container">
                        <!-- Buttons generated dynamically -->
                    </div>
                </div>
            </div>

        </div>

    </main>
</div>

<!-- Workflow Builder JavaScript Modules -->
<script src="<?= BASE_URL ?>/modules/workflows/js/workflow.js"></script>
<script src="<?= BASE_URL ?>/modules/workflows/js/nodes.js"></script>
<script src="<?= BASE_URL ?>/modules/workflows/js/connections.js"></script>
<script src="<?= BASE_URL ?>/modules/workflows/js/canvas.js"></script>
<script src="<?= BASE_URL ?>/modules/workflows/js/storage.js"></script>
<script src="<?= BASE_URL ?>/modules/workflows/js/validation.js"></script>
<script src="<?= BASE_URL ?>/modules/workflows/js/simulation.js"></script>
<script src="<?= BASE_URL ?>/modules/workflows/js/app.js"></script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
