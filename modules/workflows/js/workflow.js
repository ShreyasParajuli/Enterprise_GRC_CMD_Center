/**
 * Workflow Data Model
 * Manages the pure state of the workflow graph (nodes, connections, metadata).
 */
class WorkflowModel {
    constructor(name = "Untitled Workflow") {
        this.id = 'wf_' + Date.now() + '_' + Math.random().toString(36).substr(2, 5);
        this.name = name;
        this.nodes = [];
        this.connections = [];
    }

    /**
     * Add a node to the workflow
     */
    addNode(nodeData) {
        const node = {
            id: nodeData.id || 'node_' + Date.now() + '_' + Math.random().toString(36).substr(2, 5),
            type: nodeData.type || 'TASK',
            name: nodeData.name || 'New Node',
            description: nodeData.description || '',
            x: Math.round(nodeData.x || 100),
            y: Math.round(nodeData.y || 100),
            role: nodeData.role || ''
        };
        this.nodes.push(node);
        return node;
    }

    /**
     * Update properties of an existing node
     */
    updateNode(id, props) {
        const node = this.getNode(id);
        if (!node) return null;
        
        Object.keys(props).forEach(key => {
            if (key !== 'id') {
                node[key] = props[key];
            }
        });
        return node;
    }

    /**
     * Remove a node and all associated connections
     */
    removeNode(id) {
        const index = this.nodes.findIndex(n => n.id === id);
        if (index === -1) return false;

        this.nodes.splice(index, 1);
        
        // Cascade delete connected edges
        this.connections = this.connections.filter(
            conn => conn.source !== id && conn.target !== id
        );
        return true;
    }

    /**
     * Retrieve a node by ID
     */
    getNode(id) {
        return this.nodes.find(n => n.id === id) || null;
    }

    /**
     * Add a connection between source and target nodes
     */
    addConnection(sourceId, targetId, label = '') {
        // Prevent duplicate connection or self connection
        if (sourceId === targetId) return null;
        
        const existing = this.connections.find(c => c.source === sourceId && c.target === targetId);
        if (existing) {
            existing.label = label || existing.label;
            return existing;
        }

        const connection = {
            id: 'conn_' + Date.now() + '_' + Math.random().toString(36).substr(2, 5),
            source: sourceId,
            target: targetId,
            label: label || ''
        };

        this.connections.push(connection);
        return connection;
    }

    /**
     * Update connection properties (e.g. label)
     */
    updateConnection(id, props) {
        const conn = this.getConnection(id);
        if (!conn) return null;
        
        if (props.label !== undefined) conn.label = props.label;
        return conn;
    }

    /**
     * Remove a connection by ID
     */
    removeConnection(id) {
        const index = this.connections.findIndex(c => c.id === id);
        if (index === -1) return false;
        this.connections.splice(index, 1);
        return true;
    }

    /**
     * Retrieve connection by ID
     */
    getConnection(id) {
        return this.connections.find(c => c.id === id) || null;
    }

    /**
     * Get outgoing connections for a given node
     */
    getOutgoingConnections(nodeId) {
        return this.connections.filter(c => c.source === nodeId);
    }

    /**
     * Get incoming connections for a given node
     */
    getIncomingConnections(nodeId) {
        return this.connections.filter(c => c.target === nodeId);
    }

    /**
     * Clear all nodes and connections
     */
    clear() {
        this.nodes = [];
        this.connections = [];
    }

    /**
     * Export to plain JS Object / JSON
     */
    toJSON() {
        return {
            id: this.id,
            name: this.name,
            nodes: this.nodes.map(n => ({ ...n })),
            connections: this.connections.map(c => ({ ...c }))
        };
    }

    /**
     * Import from plain JS Object
     */
    fromJSON(data) {
        this.id = data.id || 'wf_' + Date.now();
        this.name = data.name || 'Imported Workflow';
        this.nodes = Array.isArray(data.nodes) ? data.nodes.map(n => ({
            id: String(n.id),
            type: String(n.type || 'TASK').toUpperCase(),
            name: String(n.name || 'Node'),
            description: String(n.description || ''),
            x: Number(n.x) || 100,
            y: Number(n.y) || 100,
            role: String(n.role || '')
        })) : [];

        this.connections = Array.isArray(data.connections) ? data.connections.map(c => ({
            id: String(c.id || 'conn_' + Math.random()),
            source: String(c.source),
            target: String(c.target),
            label: String(c.label || '')
        })) : [];
    }
}
