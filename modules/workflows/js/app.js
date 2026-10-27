/**
 * Main Application Bootstrap & UI Controller
 * Enterprise GRC Command Center - Visual Workflow Studio
 */
document.addEventListener('DOMContentLoaded', () => {
    // 1. Core Model & Component Initialization
    const workflow = new WorkflowModel("Security Assessment Approval");

    const canvasContainer = document.getElementById('wf-canvas');
    const nodesContainer = document.getElementById('wf-nodes-layer');
    const svgContainer = document.getElementById('wf-svg-layer');

    const connRenderer = new ConnectionRenderer(svgContainer, workflow);
    const canvasController = new CanvasController(
        canvasContainer,
        nodesContainer,
        svgContainer,
        workflow,
        connRenderer
    );

    const storageManager = new StorageManager('grc_wf_builder_saved_workflow');
    const simulator = new WorkflowSimulator(workflow, canvasController);

    // 2. DOM UI Element References
    const elements = {
        // App Container
        appContainer: document.getElementById('app-container'),

        // Top Toolbar
        workflowNameInput: document.getElementById('wf-title-input'),
        templateSelect: document.getElementById('template-select'),
        btnNew: document.getElementById('btn-new'),
        btnSave: document.getElementById('btn-save'),
        btnLoad: document.getElementById('btn-load'),
        btnValidate: document.getElementById('btn-validate'),
        btnRun: document.getElementById('btn-run'),
        btnExport: document.getElementById('btn-export'),
        btnImport: document.getElementById('btn-import'),
        fileInput: document.getElementById('file-input-import'),
        btnFullscreen: document.getElementById('btn-fullscreen'),

        // Canvas Controls
        zoomInBtn: document.getElementById('btn-zoom-in'),
        zoomOutBtn: document.getElementById('btn-zoom-out'),
        zoomResetBtn: document.getElementById('btn-zoom-reset'),
        zoomLevelText: document.getElementById('zoom-level-indicator'),

        // Properties Panel
        propsPanel: document.querySelector('.wf-sidebar-right'),
        propsTitle: document.getElementById('props-title'),
        propType: document.getElementById('prop-type'),
        propName: document.getElementById('prop-name'),
        propDesc: document.getElementById('prop-desc'),
        propRole: document.getElementById('prop-role'),
        propLabel: document.getElementById('prop-label'),
        propLabelContainer: document.getElementById('prop-label-container'),
        btnSaveProps: document.getElementById('btn-save-props'),
        btnDeleteSelected: document.getElementById('btn-delete-selected'),

        // Palette
        paletteContainer: document.getElementById('wf-palette-list'),

        // Validation & Telemetry Log Panel
        bottomPanel: document.getElementById('wf-bottom-panel'),
        logOutput: document.getElementById('wf-log-output'),
        statusBadge: document.getElementById('wf-status-badge'),
        btnClearLog: document.getElementById('btn-clear-log'),
        btnToggleLog: document.getElementById('btn-toggle-log'),

        // Decision Modal
        decisionModal: document.getElementById('wf-decision-modal'),
        decisionPrompt: document.getElementById('decision-prompt-text'),
        decisionButtons: document.getElementById('decision-options-container')
    };

    // 3. Render Node Palette Elements
    function renderPalette() {
        if (!elements.paletteContainer) return;
        elements.paletteContainer.innerHTML = '';

        Object.keys(NODE_TYPES).forEach(typeKey => {
            const config = NODE_TYPES[typeKey];
            const item = document.createElement('div');
            item.className = 'wf-palette-item';
            item.draggable = true;
            item.dataset.nodeType = config.type;

            item.innerHTML = `
                <span class="wf-palette-icon" style="color: ${config.accentColor}">${config.icon}</span>
                <span class="wf-palette-label">${config.label}</span>
            `;

            // HTML5 Drag Event Setup
            item.addEventListener('dragstart', (e) => {
                e.dataTransfer.setData('text/plain', config.type);
                e.dataTransfer.setData('application/wf-node-type', config.type);
                e.dataTransfer.effectAllowed = 'copy';
            });

            // Click to spawn on canvas alternative
            item.addEventListener('click', () => {
                const node = workflow.addNode({
                    type: config.type,
                    name: config.defaultName,
                    description: config.defaultDesc,
                    x: 250 + Math.random() * 60,
                    y: 180 + Math.random() * 60
                });
                canvasController.renderNode(node);
                canvasController.selectNode(node.id);
                canvasController.renderConnections();
                logMessage(`Spawned GRC element: [${config.label}] "${config.defaultName}"`, 'info');
            });

            elements.paletteContainer.appendChild(item);
        });
    }

    // 4. Connect Canvas Selection to Properties Panel
    canvasController.onNodeSelect = (node) => {
        if (!node) {
            elements.propsTitle.textContent = 'No Element Selected';
            elements.propType.value = '';
            elements.propName.value = '';
            elements.propDesc.value = '';
            elements.propRole.value = '';
            elements.propLabelContainer.style.display = 'none';
            elements.propName.disabled = true;
            elements.propDesc.disabled = true;
            elements.propRole.disabled = true;
            elements.btnSaveProps.disabled = true;
            elements.btnDeleteSelected.disabled = true;
            return;
        }

        elements.propsTitle.textContent = `Node: ${node.name}`;
        elements.propType.value = node.type;
        elements.propName.value = node.name || '';
        elements.propDesc.value = node.description || '';
        elements.propRole.value = node.role || '';
        elements.propLabelContainer.style.display = 'none';

        elements.propName.disabled = false;
        elements.propDesc.disabled = false;
        elements.propRole.disabled = false;
        elements.btnSaveProps.disabled = false;
        elements.btnDeleteSelected.disabled = false;
    };

    canvasController.onConnectionSelect = (conn) => {
        if (!conn) return;

        const sourceNode = workflow.getNode(conn.source);
        const targetNode = workflow.getNode(conn.target);
        const sourceName = sourceNode ? sourceNode.name : 'Unknown';
        const targetName = targetNode ? targetNode.name : 'Unknown';

        elements.propsTitle.textContent = `Link: ${sourceName} → ${targetName}`;
        elements.propType.value = 'CONNECTION';
        elements.propName.value = `${sourceName} → ${targetName}`;
        elements.propDesc.value = `Transition flow from ${sourceName} to ${targetName}`;
        elements.propRole.value = '';

        elements.propLabelContainer.style.display = 'block';
        elements.propLabel.value = conn.label || '';

        elements.propName.disabled = true;
        elements.propDesc.disabled = true;
        elements.propRole.disabled = true;
        elements.propLabel.disabled = false;
        elements.btnSaveProps.disabled = false;
        elements.btnDeleteSelected.disabled = false;
    };

    // Save Properties Panel Changes
    elements.btnSaveProps.addEventListener('click', () => {
        if (canvasController.selectedNodeId) {
            const updated = workflow.updateNode(canvasController.selectedNodeId, {
                name: elements.propName.value.trim(),
                description: elements.propDesc.value.trim(),
                role: elements.propRole.value.trim()
            });
            if (updated) {
                canvasController.renderNode(updated);
                canvasController.selectNode(updated.id);
                logMessage(`Saved node changes: "${updated.name}"`, 'success');
                if (typeof showToast === 'function') showToast(`Node "${updated.name}" updated`, 'success');
            }
        } else if (canvasController.selectedConnectionId) {
            const updated = workflow.updateConnection(canvasController.selectedConnectionId, {
                label: elements.propLabel.value.trim()
            });
            if (updated) {
                canvasController.renderConnections();
                logMessage(`Updated route label: "${updated.label}"`, 'success');
                if (typeof showToast === 'function') showToast('Route label updated', 'info');
            }
        }
    });

    // Delete Selected Item
    elements.btnDeleteSelected.addEventListener('click', () => {
        if (canvasController.selectedNodeId) {
            canvasController.deleteNode(canvasController.selectedNodeId);
            logMessage('Element removed from canvas.', 'info');
        } else if (canvasController.selectedConnectionId) {
            canvasController.deleteSelectedConnection();
            logMessage('Route link removed from workflow.', 'info');
        }
    });

    // 5. Toolbar Actions setup
    elements.workflowNameInput.addEventListener('change', (e) => {
        workflow.name = e.target.value.trim() || 'Untitled GRC Workflow';
    });

    // New Workflow
    elements.btnNew.addEventListener('click', () => {
        if (confirm('Create new workflow canvas? Any unsaved edits will be cleared.')) {
            workflow.clear();
            workflow.name = 'New GRC Workflow';
            elements.workflowNameInput.value = workflow.name;
            canvasController.renderAll();
            canvasController.resetView();
            updateZoomIndicator();
            logMessage('Created new empty GRC workflow canvas.', 'info');
            if (typeof showToast === 'function') showToast('New canvas created', 'info');
        }
    });

    // Save to LocalStorage
    elements.btnSave.addEventListener('click', () => {
        const res = storageManager.saveToLocalStorage(workflow);
        logMessage(res.message, res.success ? 'success' : 'error');
        if (typeof showToast === 'function') {
            showToast(res.message, res.success ? 'success' : 'error');
        }
    });

    // Load from LocalStorage
    elements.btnLoad.addEventListener('click', () => {
        const res = storageManager.loadFromLocalStorage(workflow);
        if (res.success) {
            elements.workflowNameInput.value = workflow.name;
            canvasController.renderAll();
            canvasController.resetView();
            updateZoomIndicator();
        }
        logMessage(res.message, res.success ? 'success' : 'error');
        if (typeof showToast === 'function') {
            showToast(res.message, res.success ? 'success' : 'error');
        }
    });

    // Export JSON
    elements.btnExport.addEventListener('click', () => {
        const res = storageManager.exportJSON(workflow);
        logMessage(res.message, res.success ? 'success' : 'error');
        if (typeof showToast === 'function') {
            showToast('Workflow exported to JSON file', 'success');
        }
    });

    // Import JSON
    elements.btnImport.addEventListener('click', () => {
        elements.fileInput.click();
    });

    elements.fileInput.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (!file) return;

        storageManager.importJSONFile(file, workflow, (res) => {
            if (res.success) {
                elements.workflowNameInput.value = workflow.name;
                canvasController.renderAll();
                canvasController.resetView();
                updateZoomIndicator();
                if (typeof showToast === 'function') showToast(`Loaded "${workflow.name}"`, 'success');
            }
            logMessage(res.message, res.success ? 'success' : 'error');
            elements.fileInput.value = '';
        });
    });

    // Validate Workflow Topology
    elements.btnValidate.addEventListener('click', () => {
        const results = WorkflowValidator.validate(workflow);
        elements.logOutput.innerHTML = '';

        let hasError = false;
        results.forEach(item => {
            if (item.type === 'error') hasError = true;
            logMessage(item.text, item.type);
        });

        elements.statusBadge.textContent = hasError ? 'Validation Failed' : 'Topology Valid';
        elements.statusBadge.className = `wf-badge ${hasError ? 'badge-error' : 'badge-success'}`;
        if (typeof showToast === 'function') {
            showToast(hasError ? 'Validation failed! Check telemetry log' : 'Workflow topology is valid', hasError ? 'error' : 'success');
        }
    });

    // Run Simulation
    elements.btnRun.addEventListener('click', () => {
        if (simulator.isRunning) {
            simulator.stop();
            elements.btnRun.innerHTML = `<svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"/></svg> Run Workflow`;
            elements.btnRun.classList.remove('btn-running');
            elements.statusBadge.textContent = 'Ready';
            elements.statusBadge.className = 'wf-badge';
            return;
        }

        // Validate before running
        const validation = WorkflowValidator.validate(workflow);
        const hasErrors = validation.some(v => v.type === 'error');

        if (hasErrors) {
            logMessage('Cannot run simulation: Workflow has validation errors! Review logs below.', 'error');
            if (typeof showToast === 'function') showToast('Fix validation errors before simulation', 'warning');
            return;
        }

        elements.btnRun.innerHTML = `<svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><rect x="6" y="6" width="12" height="12" rx="2"/></svg> Halt Simulation`;
        elements.btnRun.classList.add('btn-running');
        elements.statusBadge.textContent = 'Simulating...';
        elements.statusBadge.className = 'wf-badge badge-simulating';

        simulator.start();
    });

    simulator.onLog = (msg, type) => logMessage(msg, type);
    simulator.onComplete = (success) => {
        elements.btnRun.innerHTML = `<svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"/></svg> Run Workflow`;
        elements.btnRun.classList.remove('btn-running');
        elements.statusBadge.textContent = success ? 'Complete' : 'Halted';
        elements.statusBadge.className = `wf-badge ${success ? 'badge-success' : 'badge-error'}`;
        if (typeof showToast === 'function' && success) {
            showToast('Workflow simulation executed successfully!', 'success');
        }
    };

    simulator.onDecisionRequired = (node, options) => {
        elements.decisionPrompt.textContent = `Risk Gateway: "${node.name}"`;
        elements.decisionButtons.innerHTML = '';

        options.forEach(opt => {
            const btn = document.createElement('button');
            btn.className = 'wf-btn modal-choice-btn';
            btn.innerHTML = `<span>${opt.label}</span> <span style="font-size: 11px; opacity: 0.85; font-weight: normal;">→ ${opt.targetName}</span>`;
            btn.addEventListener('click', () => {
                elements.decisionModal.style.display = 'none';
                simulator.chooseBranch(opt.targetId);
            });
            elements.decisionButtons.appendChild(btn);
        });

        elements.decisionModal.style.display = 'flex';
    };

    // Fullscreen / Focus Mode Toggle
    if (elements.btnFullscreen) {
        elements.btnFullscreen.addEventListener('click', () => {
            const isFocus = elements.appContainer.classList.toggle('focus-mode');
            elements.btnFullscreen.innerHTML = isFocus
                ? `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 3v3a2 2 0 0 1-2 2H3m18 0h-3a2 2 0 0 1-2-2V3m0 18v-3a2 2 0 0 1 2-2h3M3 16h3a2 2 0 0 1 2 2v3"/></svg> Exit Focus`
                : `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7"/></svg> Focus Mode`;
            canvasController.renderConnections();
        });
    }

    // Toggle Collapsible Log Drawer
    if (elements.btnToggleLog) {
        elements.btnToggleLog.addEventListener('click', () => {
            const isCollapsed = elements.bottomPanel.classList.toggle('collapsed');
            elements.btnToggleLog.textContent = isCollapsed ? 'Expand Log' : 'Collapse';
        });
    }

    // 6. Zoom controls
    elements.zoomInBtn.addEventListener('click', () => {
        canvasController.setZoom(canvasController.scale + 0.15);
        updateZoomIndicator();
    });
    elements.zoomOutBtn.addEventListener('click', () => {
        canvasController.setZoom(canvasController.scale - 0.15);
        updateZoomIndicator();
    });
    elements.zoomResetBtn.addEventListener('click', () => {
        canvasController.resetView();
        updateZoomIndicator();
    });

    // Update zoom indicator on wheel zoom
    canvasContainer.addEventListener('wheel', () => {
        setTimeout(updateZoomIndicator, 10);
    }, { passive: true });

    function updateZoomIndicator() {
        if (elements.zoomLevelText) {
            elements.zoomLevelText.textContent = `${Math.round(canvasController.scale * 100)}%`;
        }
    }

    // 7. Telemetry Logging Helper
    function logMessage(text, type = 'info') {
        if (!elements.logOutput) return;
        const entry = document.createElement('div');
        entry.className = `log-entry log-${type}`;

        const time = new Date().toLocaleTimeString();
        let icon = 'ℹ';
        if (type === 'success') icon = '✓';
        if (type === 'warning') icon = '⚠';
        if (type === 'error') icon = '✗';
        if (type === 'step') icon = '▶';

        entry.innerHTML = `<span class="log-time">[${time}]</span> <span class="log-icon">${icon}</span> ${text}`;
        elements.logOutput.appendChild(entry);
        elements.logOutput.scrollTop = elements.logOutput.scrollHeight;
    }

    elements.btnClearLog.addEventListener('click', () => {
        elements.logOutput.innerHTML = '';
        elements.statusBadge.textContent = 'Ready';
        elements.statusBadge.className = 'wf-badge';
    });

    // 8. Keyboard Shortcuts (Delete selected node/connection)
    window.addEventListener('keydown', (e) => {
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA' || e.target.tagName === 'SELECT') return;

        if (e.key === 'Delete' || e.key === 'Backspace') {
            if (canvasController.selectedNodeId) {
                canvasController.deleteNode(canvasController.selectedNodeId);
                logMessage('Deleted element via keyboard command.', 'info');
            } else if (canvasController.selectedConnectionId) {
                canvasController.deleteSelectedConnection();
                logMessage('Deleted route connection via keyboard command.', 'info');
            }
        }
    });

    // 9. Pre-built GRC Workflow Templates
    const GRC_TEMPLATES = {
        security_assessment: {
            name: "Security Assessment Approval",
            build: () => {
                const start = workflow.addNode({ type: 'START', name: 'Start Assessment', description: 'Risk assessment request initialized', x: 80, y: 220 });
                const task1 = workflow.addNode({ type: 'TASK', name: 'Submit Security Profile', description: 'Fill risk assessment profile and asset impact', role: 'Risk Analyst', x: 280, y: 220 });
                const ctrl1 = workflow.addNode({ type: 'CONTROL', name: 'Control Verification', description: 'Verify ISO 27001 / SOC 2 control adherence', role: 'Compliance Officer', x: 500, y: 220 });
                const dec1 = workflow.addNode({ type: 'DECISION', name: 'Risk Acceptable?', description: 'Calculated risk score <= 12', x: 740, y: 220 });
                const app1 = workflow.addNode({ type: 'APPROVAL', name: 'CISO Sign-off', description: 'Authorize deployment and operational sign-off', role: 'CISO / Director', x: 970, y: 130 });
                const task2 = workflow.addNode({ type: 'TASK', name: 'Mitigation Plan', description: 'Mandate compensating security controls', role: 'Security Architect', x: 970, y: 310 });
                const end = workflow.addNode({ type: 'END', name: 'Audit Logged', description: 'Workflow closed and registered in GRC audit log', x: 1220, y: 220 });

                workflow.addConnection(start.id, task1.id);
                workflow.addConnection(task1.id, ctrl1.id);
                workflow.addConnection(ctrl1.id, dec1.id);
                workflow.addConnection(dec1.id, app1.id, 'YES (Low Risk)');
                workflow.addConnection(dec1.id, task2.id, 'NO (High Risk)');
                workflow.addConnection(app1.id, end.id);
                workflow.addConnection(task2.id, end.id);
            }
        },
        incident_escalation: {
            name: "Critical Risk Incident Escalation",
            build: () => {
                const start = workflow.addNode({ type: 'START', name: 'RSK-001 Detected', description: 'Automated telemetry alert / threat ingest', x: 80, y: 220 });
                const task1 = workflow.addNode({ type: 'TASK', name: 'Triage & Containment', description: 'Isolate affected network subnet / host', role: 'SOC Lead', x: 280, y: 220 });
                const dec1 = workflow.addNode({ type: 'DECISION', name: 'Impact >= High?', description: 'Severity score assessment > 15', x: 510, y: 220 });
                const app1 = workflow.addNode({ type: 'APPROVAL', name: 'Emergency Executive Sign-off', description: 'Convene CIRT and approve countermeasures', role: 'Incident Commander', x: 750, y: 130 });
                const task2 = workflow.addNode({ type: 'TASK', name: 'Standard Patching', description: 'Execute scheduled patch and reboot', role: 'Sysadmin', x: 750, y: 310 });
                const ctrl1 = workflow.addNode({ type: 'CONTROL', name: 'Forensic Post-Mortem', description: 'Validate log integrity and file hash scan', role: 'Forensics Lead', x: 1000, y: 220 });
                const end = workflow.addNode({ type: 'END', name: 'Resolved & Closed', description: 'Incident closed and reported to regulators', x: 1240, y: 220 });

                workflow.addConnection(start.id, task1.id);
                workflow.addConnection(task1.id, dec1.id);
                workflow.addConnection(dec1.id, app1.id, 'CRITICAL');
                workflow.addConnection(dec1.id, task2.id, 'STANDARD');
                workflow.addConnection(app1.id, ctrl1.id);
                workflow.addConnection(task2.id, ctrl1.id);
                workflow.addConnection(ctrl1.id, end.id);
            }
        },
        vendor_review: {
            name: "Third-Party Vendor Risk Assessment",
            build: () => {
                const start = workflow.addNode({ type: 'START', name: 'Vendor Intake', description: 'Procurement requests new SaaS integration', x: 80, y: 220 });
                const task1 = workflow.addNode({ type: 'TASK', name: 'Questionnaire Review', description: 'Evaluate SIG/CAIQ security questionnaire', role: 'Vendor Risk Analyst', x: 280, y: 220 });
                const ctrl1 = workflow.addNode({ type: 'CONTROL', name: 'SOC 2 Type II Check', description: 'Verify auditor report & GDPR data processing', role: 'Data Protection Officer', x: 500, y: 220 });
                const dec1 = workflow.addNode({ type: 'DECISION', name: 'Compliant?', description: 'Clean audit report without exceptions?', x: 730, y: 220 });
                const app1 = workflow.addNode({ type: 'APPROVAL', name: 'Vendor Approval', description: 'Approve vendor onboarding in register', role: 'Procurement CISO', x: 960, y: 130 });
                const task2 = workflow.addNode({ type: 'TASK', name: 'Remediation Notice', description: 'Require vendor contract security amendments', role: 'Legal Counsel', x: 960, y: 310 });
                const end = workflow.addNode({ type: 'END', name: 'Contract Ingested', description: 'Vendor active in Asset & Third-Party register', x: 1200, y: 220 });

                workflow.addConnection(start.id, task1.id);
                workflow.addConnection(task1.id, ctrl1.id);
                workflow.addConnection(ctrl1.id, dec1.id);
                workflow.addConnection(dec1.id, app1.id, 'APPROVED');
                workflow.addConnection(dec1.id, task2.id, 'EXCEPTIONS');
                workflow.addConnection(app1.id, end.id);
                workflow.addConnection(task2.id, end.id);
            }
        }
    };

    function loadTemplate(templateKey) {
        const tmpl = GRC_TEMPLATES[templateKey] || GRC_TEMPLATES.security_assessment;
        workflow.clear();
        workflow.name = tmpl.name;
        elements.workflowNameInput.value = workflow.name;
        tmpl.build();
        canvasController.renderAll();
        canvasController.resetView();
        updateZoomIndicator();
        logMessage(`Loaded GRC orchestration template: "${tmpl.name}".`, 'info');
        if (typeof showToast === 'function') showToast(`Template loaded: ${tmpl.name}`, 'info');
    }

    if (elements.templateSelect) {
        elements.templateSelect.addEventListener('change', (e) => {
            loadTemplate(e.target.value);
        });
    }

    // Initialize Palette & Preload default workflow
    renderPalette();
    loadTemplate('security_assessment');
});
