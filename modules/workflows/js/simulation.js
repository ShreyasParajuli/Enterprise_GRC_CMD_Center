/**
 * Visual Workflow Simulator Engine
 * Traverses graph step-by-step, highlighting active nodes & asking decision prompts.
 */
class WorkflowSimulator {
    constructor(workflowModel, canvasController) {
        this.model = workflowModel;
        this.canvas = canvasController;

        this.isRunning = false;
        this.currentNodeId = null;
        this.history = [];

        // UI callbacks
        this.onStepChange = null;
        this.onDecisionRequired = null;
        this.onComplete = null;
        this.onLog = null;
    }

    /**
     * Start workflow simulation
     */
    start() {
        const startNode = this.model.nodes.find(n => n.type === 'START');
        if (!startNode) {
            if (this.onLog) this.onLog('Simulation failed: No START node found in workflow.', 'error');
            return false;
        }

        this.isRunning = true;
        this.history = [];
        if (this.onLog) this.onLog(`Starting workflow simulation: "${this.model.name}"`, 'info');

        this.setCurrentNode(startNode.id);
        return true;
    }

    /**
     * Stop/Reset simulation
     */
    stop() {
        this.clearActiveHighlight();
        this.isRunning = false;
        this.currentNodeId = null;
        if (this.onLog) this.onLog('Simulation stopped by user.', 'warning');
        if (this.onComplete) this.onComplete(false);
    }

    /**
     * Set active node and evaluate next step
     */
    setCurrentNode(nodeId) {
        if (!this.isRunning) return;

        this.clearActiveHighlight();
        this.currentNodeId = nodeId;
        const node = this.model.getNode(nodeId);

        if (!node) {
            if (this.onLog) this.onLog(`Simulation error: Node ${nodeId} not found.`, 'error');
            this.stop();
            return;
        }

        this.history.push(nodeId);

        // Highlight node on canvas
        const nodeEl = document.getElementById(`node-el-${node.id}`);
        if (nodeEl) {
            nodeEl.classList.add('sim-active');
            nodeEl.scrollIntoView({ behavior: 'smooth', block: 'center', inline: 'center' });
        }

        if (this.onStepChange) this.onStepChange(node);
        if (this.onLog) this.onLog(`Active Step: [${node.type}] ${node.name}`, 'step');

        // Check if END node
        if (node.type === 'END') {
            setTimeout(() => {
                if (this.onLog) this.onLog('Workflow simulation completed successfully! 🎉', 'success');
                this.isRunning = false;
                if (this.onComplete) this.onComplete(true);
            }, 600);
            return;
        }

        // Evaluate Outgoing Connections
        const outgoing = this.model.getOutgoingConnections(node.id);

        if (outgoing.length === 0) {
            setTimeout(() => {
                if (this.onLog) this.onLog(`Dead end: Node "${node.name}" has no outgoing connection!`, 'warning');
                this.isRunning = false;
                if (this.onComplete) this.onComplete(false);
            }, 600);
            return;
        }

        // If Decision Node with multiple choices -> prompt user choice
        if (node.type === 'DECISION' || outgoing.length > 1) {
            if (this.onDecisionRequired) {
                const options = outgoing.map(conn => {
                    const targetNode = this.model.getNode(conn.target);
                    return {
                        connectionId: conn.id,
                        targetId: conn.target,
                        label: conn.label || (targetNode ? targetNode.name : 'Branch'),
                        targetName: targetNode ? targetNode.name : 'Unknown'
                    };
                });
                this.onDecisionRequired(node, options);
            }
        } else {
            // Auto advance for standard linear sequence (Start, Task, Approval)
            setTimeout(() => {
                if (this.isRunning) {
                    this.setCurrentNode(outgoing[0].target);
                }
            }, 1000);
        }
    }

    /**
     * Step into chosen decision branch
     */
    chooseBranch(targetId) {
        if (!this.isRunning) return;
        this.setCurrentNode(targetId);
    }

    clearActiveHighlight() {
        document.querySelectorAll('.wf-node.sim-active').forEach(el => {
            el.classList.remove('sim-active');
        });
    }
}
