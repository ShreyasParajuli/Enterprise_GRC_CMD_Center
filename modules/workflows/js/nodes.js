/**
 * Node Types Registry and Configs - Enterprise GRC Command Center Theme
 */
const NODE_TYPES = {
    START: {
        type: 'START',
        label: 'Trigger / Start',
        category: 'trigger',
        accentColor: '#10b981', // Emerald Green
        icon: `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polygon points="10 8 16 12 10 16 10 8"/></svg>`,
        defaultName: 'Incident Ingestion',
        defaultDesc: 'Workflow entry point or event trigger',
        hasInput: false,
        hasOutput: true,
        isDecision: false
    },
    TASK: {
        type: 'TASK',
        label: 'Action Task',
        category: 'action',
        accentColor: '#ff7849', // GRC Vibrant Safety Orange
        icon: `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>`,
        defaultName: 'Remediation Task',
        defaultDesc: 'Perform corrective action or patch',
        hasInput: true,
        hasOutput: true,
        isDecision: false
    },
    CONTROL: {
        type: 'CONTROL',
        label: 'Control Check',
        category: 'compliance',
        accentColor: '#C9AEF1', // GRC Lavender / Cyber Sky
        icon: `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg>`,
        defaultName: 'Security Control Verify',
        defaultDesc: 'Validate ISO/SOC2/NIST compliance control',
        hasInput: true,
        hasOutput: true,
        isDecision: false
    },
    APPROVAL: {
        type: 'APPROVAL',
        label: 'Review / Sign-off',
        category: 'governance',
        accentColor: '#EF6351', // GRC Primary Coral Orange
        icon: `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="m16 11 2 2 4-4"/></svg>`,
        defaultName: 'CISO / Lead Approval',
        defaultDesc: 'Review risk exposure and authorize branch',
        hasInput: true,
        hasOutput: true,
        isDecision: false
    },
    DECISION: {
        type: 'DECISION',
        label: 'Risk Gateway',
        category: 'logic',
        accentColor: '#f59e0b', // Amber / Warning
        icon: `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 22 12 12 22 2 12 12 2"/></svg>`,
        defaultName: 'Risk Threshold Gate',
        defaultDesc: 'Evaluate branch condition (Likelihood × Impact)',
        hasInput: true,
        hasOutput: true,
        isDecision: true
    },
    END: {
        type: 'END',
        label: 'Resolution / End',
        category: 'terminal',
        accentColor: '#ef4444', // Red / Rose
        icon: `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><rect x="9" y="9" width="6" height="6" rx="1"/></svg>`,
        defaultName: 'Archive & Log',
        defaultDesc: 'Workflow completion and audit trail committed',
        hasInput: true,
        hasOutput: false,
        isDecision: false
    }
};

/**
 * Node DOM Helper functions
 */
const NodeRenderer = {
    createNodeElement(node) {
        const config = NODE_TYPES[node.type] || NODE_TYPES.TASK;
        
        const el = document.createElement('div');
        el.className = `wf-node wf-node-${node.type.toLowerCase()}`;
        el.id = `node-el-${node.id}`;
        el.dataset.nodeId = node.id;
        el.style.left = `${node.x}px`;
        el.style.top = `${node.y}px`;

        let portsHtml = '';
        if (config.hasInput) {
            portsHtml += `<div class="wf-port wf-port-input" data-port-type="input" data-node-id="${node.id}" title="Input connection port"></div>`;
        }
        if (config.hasOutput) {
            portsHtml += `<div class="wf-port wf-port-output" data-port-type="output" data-node-id="${node.id}" title="Output connection port (drag to connect)"></div>`;
        }

        const roleBadge = node.role ? `<span class="wf-node-badge">${this.escapeHtml(node.role)}</span>` : '';

        el.innerHTML = `
            ${portsHtml}
            <div class="wf-node-header" style="border-left-color: ${config.accentColor}">
                <span class="wf-node-icon" style="color: ${config.accentColor}">${config.icon}</span>
                <span class="wf-node-type-label">${config.label}</span>
                <button class="wf-node-delete-btn" data-node-id="${node.id}" title="Delete element">&times;</button>
            </div>
            <div class="wf-node-body">
                <div class="wf-node-title">${this.escapeHtml(node.name)}</div>
                ${node.description ? `<div class="wf-node-desc">${this.escapeHtml(node.description)}</div>` : ''}
                ${roleBadge}
            </div>
        `;

        return el;
    },

    escapeHtml(str) {
        if (!str) return '';
        return str
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }
};
