/**
 * Connection SVG Renderer
 * Handles rendering smooth Bezier curves, arrow markers, labels, and interaction.
 */
class ConnectionRenderer {
    constructor(svgContainer, workflowModel) {
        this.svg = svgContainer;
        this.model = workflowModel;
        this.selectedConnectionId = null;
        this.initDefs();
    }

    /**
     * Create SVG markers for line directional arrows
     */
    initDefs() {
        let defs = this.svg.querySelector('defs');
        if (!defs) {
            defs = document.createElementNS('http://www.w3.org/2000/svg', 'defs');
            this.svg.appendChild(defs);
        }

        defs.innerHTML = `
            <marker id="wf-arrow" viewBox="0 0 10 10" refX="8" refY="5" markerWidth="6" markerHeight="6" orient="auto-start-reverse">
                <path d="M 0 1 L 10 5 L 0 9 z" fill="#ff7849" />
            </marker>
            <marker id="wf-arrow-active" viewBox="0 0 10 10" refX="8" refY="5" markerWidth="6" markerHeight="6" orient="auto-start-reverse">
                <path d="M 0 1 L 10 5 L 0 9 z" fill="#EF6351" />
            </marker>
            <marker id="wf-arrow-selected" viewBox="0 0 10 10" refX="8" refY="5" markerWidth="6" markerHeight="6" orient="auto-start-reverse">
                <path d="M 0 1 L 10 5 L 0 9 z" fill="#ff441f" />
            </marker>
        `;
    }

    /**
     * Render all connections from model
     */
    render(zoom = 1, pan = { x: 0, y: 0 }) {
        // Keep defs
        const defs = this.svg.querySelector('defs');
        this.svg.innerHTML = '';
        if (defs) this.svg.appendChild(defs);

        const connectionsGroup = document.createElementNS('http://www.w3.org/2000/svg', 'g');
        connectionsGroup.id = 'wf-connections-group';

        this.model.connections.forEach(conn => {
            const sourceEl = document.getElementById(`node-el-${conn.source}`);
            const targetEl = document.getElementById(`node-el-${conn.target}`);

            if (!sourceEl || !targetEl) return;

            const sourcePort = sourceEl.querySelector('.wf-port-output') || sourceEl;
            const targetPort = targetEl.querySelector('.wf-port-input') || targetEl;

            const p1 = this.getPortCoordinates(sourcePort);
            const p2 = this.getPortCoordinates(targetPort);

            const isSelected = conn.id === this.selectedConnectionId;
            const pathData = this.calculateBezierPath(p1, p2);

            // Group container for path + label
            const group = document.createElementNS('http://www.w3.org/2000/svg', 'g');
            group.classList.add('wf-connection-item');
            if (isSelected) group.classList.add('selected');
            group.dataset.connectionId = conn.id;

            // Background wider hit area for easy clicking
            const hitPath = document.createElementNS('http://www.w3.org/2000/svg', 'path');
            hitPath.setAttribute('d', pathData);
            hitPath.setAttribute('class', 'wf-connection-hitbox');

            // Visible path
            const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
            path.setAttribute('d', pathData);
            path.setAttribute('class', `wf-connection-line ${isSelected ? 'selected' : ''}`);
            path.setAttribute('marker-end', isSelected ? 'url(#wf-arrow-selected)' : 'url(#wf-arrow)');

            group.appendChild(hitPath);
            group.appendChild(path);

            // Connection Label (e.g. YES, NO, APPROVE)
            if (conn.label) {
                const midPoint = this.getBezierMidpoint(p1, p2);

                const labelGroup = document.createElementNS('http://www.w3.org/2000/svg', 'g');
                labelGroup.setAttribute('transform', `translate(${midPoint.x}, ${midPoint.y})`);
                labelGroup.setAttribute('class', 'wf-connection-label-group');

                const rect = document.createElementNS('http://www.w3.org/2000/svg', 'rect');
                rect.setAttribute('rx', '4');
                rect.setAttribute('ry', '4');
                rect.setAttribute('class', 'wf-connection-label-bg');

                const text = document.createElementNS('http://www.w3.org/2000/svg', 'text');
                text.setAttribute('text-anchor', 'middle');
                text.setAttribute('dominant-baseline', 'central');
                text.setAttribute('class', 'wf-connection-label-text');
                text.textContent = conn.label;

                labelGroup.appendChild(rect);
                labelGroup.appendChild(text);
                group.appendChild(labelGroup);

                // Auto position rect padding after render frame
                setTimeout(() => {
                    try {
                        const bbox = text.getBBox();
                        rect.setAttribute('x', bbox.x - 6);
                        rect.setAttribute('y', bbox.y - 3);
                        rect.setAttribute('width', bbox.width + 12);
                        rect.setAttribute('height', bbox.height + 6);
                    } catch (e) {
                        // fallback if DOM unmounted
                    }
                }, 0);
            }

            connectionsGroup.appendChild(group);
        });

        this.svg.appendChild(connectionsGroup);
    }

    /**
     * Render drag preview connection line while connecting nodes
     */
    renderTempConnection(sourceNodeId, currentPos) {
        let tempPath = this.svg.querySelector('#wf-temp-connection');
        if (!tempPath) {
            tempPath = document.createElementNS('http://www.w3.org/2000/svg', 'path');
            tempPath.id = 'wf-temp-connection';
            tempPath.setAttribute('class', 'wf-connection-temp');
            tempPath.setAttribute('marker-end', 'url(#wf-arrow-active)');
            this.svg.appendChild(tempPath);
        }

        const sourceEl = document.getElementById(`node-el-${sourceNodeId}`);
        if (!sourceEl) return;

        const sourcePort = sourceEl.querySelector('.wf-port-output') || sourceEl;
        const p1 = this.getPortCoordinates(sourcePort);
        const p2 = currentPos;

        const pathData = this.calculateBezierPath(p1, p2);
        tempPath.setAttribute('d', pathData);
        tempPath.style.display = 'block';
    }

    /**
     * Remove temp connection line
     */
    removeTempConnection() {
        const tempPath = this.svg.querySelector('#wf-temp-connection');
        if (tempPath) {
            tempPath.style.display = 'none';
        }
    }

    /**
     * Get center coordinates of port element relative to canvas layer
     */
    getPortCoordinates(portEl) {
        const canvasContainer = document.getElementById('wf-canvas');
        const containerRect = canvasContainer.getBoundingClientRect();
        const portRect = portEl.getBoundingClientRect();

        // Calculate center of port relative to canvas viewport element
        const x = (portRect.left + portRect.width / 2 - containerRect.left);
        const y = (portRect.top + portRect.height / 2 - containerRect.top);

        return { x, y };
    }

    /**
     * Calculate smooth cubic Bezier curve path string
     */
    calculateBezierPath(p1, p2) {
        const dx = Math.abs(p2.x - p1.x);
        const dy = Math.abs(p2.y - p1.y);

        // Dynamic curvature based on distance
        const controlOffset = Math.max(40, dx * 0.5);

        // Output from right side of p1, into left side of p2
        const c1 = { x: p1.x + controlOffset, y: p1.y };
        const c2 = { x: p2.x - controlOffset, y: p2.y };

        return `M ${p1.x} ${p1.y} C ${c1.x} ${c1.y}, ${c2.x} ${c2.y}, ${p2.x} ${p2.y}`;
    }

    /**
     * Calculate mid point on cubic Bezier curve for label positioning
     */
    getBezierMidpoint(p1, p2) {
        const dx = Math.abs(p2.x - p1.x);
        const controlOffset = Math.max(40, dx * 0.5);
        
        const c1 = { x: p1.x + controlOffset, y: p1.y };
        const c2 = { x: p2.x - controlOffset, y: p2.y };

        // t = 0.5 for midpoint
        const t = 0.5;
        const mt = 1 - t;

        const x = mt * mt * mt * p1.x + 3 * mt * mt * t * c1.x + 3 * mt * t * t * c2.x + t * t * t * p2.x;
        const y = mt * mt * mt * p1.y + 3 * mt * mt * t * c1.y + 3 * mt * t * t * c2.y + t * t * t * p2.y;

        return { x, y };
    }

    setSelectedConnection(connectionId) {
        this.selectedConnectionId = connectionId;
        this.render();
    }
}
