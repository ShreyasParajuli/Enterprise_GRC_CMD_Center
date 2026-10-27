/**
 * Canvas Interactive Controller
 * Manages dragging nodes, connecting ports, palette drop, zoom, and pan.
 */
class CanvasController {
    constructor(canvasContainer, nodesContainer, svgContainer, workflowModel, connectionRenderer) {
        this.canvas = canvasContainer;
        this.nodesContainer = nodesContainer;
        this.svg = svgContainer;
        this.model = workflowModel;
        this.connectionRenderer = connectionRenderer;

        // Viewport transform state
        this.scale = 1;
        this.panX = 0;
        this.panY = 0;

        // Interaction state
        this.isPanning = false;
        this.panStart = { x: 0, y: 0 };

        this.draggedNodeId = null;
        this.dragOffset = { x: 0, y: 0 };

        this.isConnecting = false;
        this.connectingSourceNodeId = null;

        this.selectedNodeId = null;
        this.selectedConnectionId = null;

        // Event callbacks
        this.onNodeSelect = null;
        this.onConnectionSelect = null;
        this.onCanvasChange = null;

        this.initEventListeners();
    }

    initEventListeners() {
        // Drag & Drop from Palette onto Canvas
        this.canvas.addEventListener('dragover', (e) => e.preventDefault());
        this.canvas.addEventListener('drop', (e) => this.handlePaletteDrop(e));

        // Canvas Click & Mouse Events
        this.canvas.addEventListener('mousedown', (e) => this.handleMouseDown(e));
        window.addEventListener('mousemove', (e) => this.handleMouseMove(e));
        window.addEventListener('mouseup', (e) => this.handleMouseUp(e));

        // Wheel Zooming
        this.canvas.addEventListener('wheel', (e) => this.handleWheelZoom(e), { passive: false });

        // Double click connection to edit label
        this.canvas.addEventListener('dblclick', (e) => this.handleDoubleClick(e));
    }

    /**
     * Transform viewport coordinates (accounting for zoom & pan)
     */
    screenToCanvasCoords(screenX, screenY) {
        const rect = this.canvas.getBoundingClientRect();
        const x = (screenX - rect.left - this.panX) / this.scale;
        const y = (screenY - rect.top - this.panY) / this.scale;
        return { x, y };
    }

    /**
     * Handle drag and drop from left palette onto canvas
     */
    handlePaletteDrop(e) {
        e.preventDefault();
        const nodeType = e.dataTransfer.getData('text/plain') || e.dataTransfer.getData('application/wf-node-type');
        if (!nodeType || !NODE_TYPES[nodeType]) return;

        const pos = this.screenToCanvasCoords(e.clientX, e.clientY);
        const config = NODE_TYPES[nodeType];

        const node = this.model.addNode({
            type: nodeType,
            name: config.defaultName,
            description: config.defaultDesc,
            x: Math.round(pos.x - 90),
            y: Math.round(pos.y - 40)
        });

        this.renderNode(node);
        this.selectNode(node.id);
        this.renderConnections();

        if (this.onCanvasChange) this.onCanvasChange();
    }

    /**
     * Handle Mouse Down (Node selection, port connection, node drag, pan)
     */
    handleMouseDown(e) {
        // If clicking delete button on node
        if (e.target.classList.contains('wf-node-delete-btn')) {
            const nodeId = e.target.dataset.nodeId;
            this.deleteNode(nodeId);
            return;
        }

        // If clicking output port to start connecting
        const portEl = e.target.closest('.wf-port-output');
        if (portEl) {
            e.stopPropagation();
            this.isConnecting = true;
            this.connectingSourceNodeId = portEl.dataset.nodeId;
            return;
        }

        // If clicking a node element
        const nodeEl = e.target.closest('.wf-node');
        if (nodeEl) {
            e.stopPropagation();
            const nodeId = nodeEl.dataset.nodeId;
            const node = this.model.getNode(nodeId);

            if (node) {
                this.selectNode(nodeId);
                this.draggedNodeId = nodeId;
                const canvasCoords = this.screenToCanvasCoords(e.clientX, e.clientY);
                this.dragOffset = {
                    x: canvasCoords.x - node.x,
                    y: canvasCoords.y - node.y
                };
            }
            return;
        }

        // If clicking a connection item
        const connectionItem = e.target.closest('.wf-connection-item');
        if (connectionItem) {
            e.stopPropagation();
            const connId = connectionItem.dataset.connectionId;
            this.selectConnection(connId);
            return;
        }

        // Clicked canvas background -> clear selection or start pan
        this.clearSelection();

        // Start canvas panning if middle click, or clicking backdrop
        if (e.button === 1 || e.target === this.canvas || e.target === this.svg || e.target === this.nodesContainer) {
            this.isPanning = true;
            this.panStart = { x: e.clientX - this.panX, y: e.clientY - this.panY };
            this.canvas.style.cursor = 'grabbing';
        }
    }

    /**
     * Handle Mouse Move (Dragging node, drawing connection wire, panning canvas)
     */
    handleMouseMove(e) {
        if (this.draggedNodeId) {
            const pos = this.screenToCanvasCoords(e.clientX, e.clientY);
            const newX = Math.round(pos.x - this.dragOffset.x);
            const newY = Math.round(pos.y - this.dragOffset.y);

            const node = this.model.updateNode(this.draggedNodeId, { x: newX, y: newY });
            if (node) {
                const nodeEl = document.getElementById(`node-el-${node.id}`);
                if (nodeEl) {
                    nodeEl.style.left = `${node.x}px`;
                    nodeEl.style.top = `${node.y}px`;
                }
                this.renderConnections();
            }
            return;
        }

        if (this.isConnecting) {
            const canvasCoords = this.screenToCanvasCoords(e.clientX, e.clientY);
            this.connectionRenderer.renderTempConnection(this.connectingSourceNodeId, canvasCoords);
            return;
        }

        if (this.isPanning) {
            this.panX = e.clientX - this.panStart.x;
            this.panY = e.clientY - this.panStart.y;
            this.applyViewportTransform();
        }
    }

    /**
     * Handle Mouse Up (Finish dragging, complete port connection, stop pan)
     */
    handleMouseUp(e) {
        if (this.draggedNodeId) {
            this.draggedNodeId = null;
            if (this.onCanvasChange) this.onCanvasChange();
        }

        if (this.isConnecting) {
            this.isConnecting = false;
            this.connectionRenderer.removeTempConnection();

            const targetPortEl = e.target.closest('.wf-port-input') || e.target.closest('.wf-node');
            if (targetPortEl) {
                const targetNodeId = targetPortEl.dataset.nodeId;
                if (targetNodeId && targetNodeId !== this.connectingSourceNodeId) {
                    // Ask default label if decision node
                    const sourceNode = this.model.getNode(this.connectingSourceNodeId);
                    let label = '';
                    if (sourceNode && sourceNode.type === 'DECISION') {
                        const existingConns = this.model.getOutgoingConnections(sourceNode.id);
                        label = existingConns.length === 0 ? 'YES' : 'NO';
                    }

                    const conn = this.model.addConnection(this.connectingSourceNodeId, targetNodeId, label);
                    if (conn) {
                        this.renderConnections();
                        if (this.onCanvasChange) this.onCanvasChange();
                    }
                }
            }
            this.connectingSourceNodeId = null;
        }

        if (this.isPanning) {
            this.isPanning = false;
            this.canvas.style.cursor = 'default';
        }
    }

    /**
     * Handle Mouse Wheel Zooming
     */
    handleWheelZoom(e) {
        if (e.ctrlKey || e.metaKey || true) { // Always allow scroll zoom on canvas container
            e.preventDefault();
            const delta = e.deltaY < 0 ? 1.1 : 0.9;
            const newScale = Math.min(Math.max(0.3, this.scale * delta), 2.5);

            // Zoom towards cursor location
            const rect = this.canvas.getBoundingClientRect();
            const mouseX = e.clientX - rect.left;
            const mouseY = e.clientY - rect.top;

            this.panX = mouseX - (mouseX - this.panX) * (newScale / this.scale);
            this.panY = mouseY - (mouseY - this.panY) * (newScale / this.scale);
            this.scale = newScale;

            this.applyViewportTransform();
        }
    }

    /**
     * Double Click Connection to edit branch label
     */
    handleDoubleClick(e) {
        const connectionItem = e.target.closest('.wf-connection-item');
        if (connectionItem) {
            const connId = connectionItem.dataset.connectionId;
            const conn = this.model.getConnection(connId);
            if (conn) {
                const newLabel = prompt('Enter branch label (e.g. YES, NO, APPROVE, REJECT):', conn.label);
                if (newLabel !== null) {
                    this.model.updateConnection(connId, { label: newLabel.trim() });
                    this.renderConnections();
                    if (this.onCanvasChange) this.onCanvasChange();
                }
            }
        }
    }

    applyViewportTransform() {
        this.nodesContainer.style.transform = `translate(${this.panX}px, ${this.panY}px) scale(${this.scale})`;
        this.svg.style.transform = `translate(${this.panX}px, ${this.panY}px) scale(${this.scale})`;
        this.renderConnections();
    }

    setZoom(newScale) {
        this.scale = Math.min(Math.max(0.3, newScale), 2.5);
        this.applyViewportTransform();
    }

    resetView() {
        this.scale = 1;
        this.panX = 0;
        this.panY = 0;
        this.applyViewportTransform();
    }

    /**
     * Render node to DOM canvas
     */
    renderNode(node) {
        let el = document.getElementById(`node-el-${node.id}`);
        if (el) {
            el.remove();
        }
        el = NodeRenderer.createNodeElement(node);
        if (node.id === this.selectedNodeId) {
            el.classList.add('selected');
        }
        this.nodesContainer.appendChild(el);
    }

    /**
     * Re-render entire workflow graph from model
     */
    renderAll() {
        this.nodesContainer.innerHTML = '';
        this.model.nodes.forEach(node => this.renderNode(node));
        this.renderConnections();
    }

    renderConnections() {
        this.connectionRenderer.render(this.scale, { x: this.panX, y: this.panY });
    }

    selectNode(nodeId) {
        this.clearSelection();
        this.selectedNodeId = nodeId;
        const nodeEl = document.getElementById(`node-el-${nodeId}`);
        if (nodeEl) nodeEl.classList.add('selected');

        const node = this.model.getNode(nodeId);
        if (this.onNodeSelect && node) this.onNodeSelect(node);
    }

    selectConnection(connectionId) {
        this.clearSelection();
        this.selectedConnectionId = connectionId;
        this.connectionRenderer.setSelectedConnection(connectionId);

        const conn = this.model.getConnection(connectionId);
        if (this.onConnectionSelect && conn) this.onConnectionSelect(conn);
    }

    clearSelection() {
        if (this.selectedNodeId) {
            const oldEl = document.getElementById(`node-el-${this.selectedNodeId}`);
            if (oldEl) oldEl.classList.remove('selected');
            this.selectedNodeId = null;
        }

        if (this.selectedConnectionId) {
            this.selectedConnectionId = null;
            this.connectionRenderer.setSelectedConnection(null);
        }

        if (this.onNodeSelect) this.onNodeSelect(null);
        if (this.onConnectionSelect) this.onConnectionSelect(null);
    }

    deleteNode(nodeId) {
        if (this.model.removeNode(nodeId)) {
            const el = document.getElementById(`node-el-${nodeId}`);
            if (el) el.remove();
            if (this.selectedNodeId === nodeId) this.clearSelection();
            this.renderConnections();
            if (this.onCanvasChange) this.onCanvasChange();
        }
    }

    deleteSelectedConnection() {
        if (this.selectedConnectionId) {
            this.model.removeConnection(this.selectedConnectionId);
            this.clearSelection();
            this.renderConnections();
            if (this.onCanvasChange) this.onCanvasChange();
        }
    }
}
