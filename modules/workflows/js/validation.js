/**
 * Workflow Graph Validation Engine
 * Validates topology, completeness, reachability, orphan nodes, and decision paths.
 */
class WorkflowValidator {
    static validate(workflowModel) {
        const results = [];
        const nodes = workflowModel.nodes;
        const connections = workflowModel.connections;

        if (nodes.length === 0) {
            results.push({ type: 'error', text: 'Workflow canvas is empty.' });
            return results;
        }

        // 1. Start Node check
        const startNodes = nodes.filter(n => n.type === 'START');
        if (startNodes.length === 0) {
            results.push({ type: 'error', text: 'Workflow is missing a START node.' });
        } else if (startNodes.length > 1) {
            results.push({ type: 'warning', text: 'Workflow has multiple START nodes.' });
        } else {
            results.push({ type: 'success', text: 'Start node exists.' });
        }

        // 2. End Node check
        const endNodes = nodes.filter(n => n.type === 'END');
        if (endNodes.length === 0) {
            results.push({ type: 'error', text: 'Workflow is missing an END node.' });
        } else {
            results.push({ type: 'success', text: `End node exists (${endNodes.length} found).` });
        }

        // 3. Unique ID check
        const nodeIds = new Set();
        let duplicateIds = false;
        nodes.forEach(n => {
            if (nodeIds.has(n.id)) duplicateIds = true;
            nodeIds.add(n.id);
        });
        if (duplicateIds) {
            results.push({ type: 'error', text: 'Duplicate node IDs detected.' });
        } else {
            results.push({ type: 'success', text: 'All nodes have unique IDs.' });
        }

        // 4. Connection reference validity
        let invalidConn = false;
        connections.forEach(conn => {
            const sourceExists = nodes.some(n => n.id === conn.source);
            const targetExists = nodes.some(n => n.id === conn.target);
            if (!sourceExists || !targetExists) {
                invalidConn = true;
            }
        });
        if (invalidConn) {
            results.push({ type: 'error', text: 'Connection references a non-existent node.' });
        } else {
            results.push({ type: 'success', text: 'All connections reference valid nodes.' });
        }

        // 5. Start node outgoing connection check
        if (startNodes.length === 1) {
            const startOut = connections.filter(c => c.source === startNodes[0].id);
            if (startOut.length === 0) {
                results.push({ type: 'error', text: 'START node has no outgoing connection.' });
            }
        }

        // 6. Decision Nodes outgoing paths check
        const decisionNodes = nodes.filter(n => n.type === 'DECISION');
        decisionNodes.forEach(d => {
            const outConns = connections.filter(c => c.source === d.id);
            if (outConns.length === 0) {
                results.push({ type: 'error', text: `Decision node "${d.name}" has no outgoing branches.` });
            } else if (outConns.length === 1) {
                results.push({ type: 'warning', text: `Decision node "${d.name}" has only 1 outgoing path (requires multiple decision paths like YES/NO).` });
            } else {
                results.push({ type: 'success', text: `Decision node "${d.name}" has ${outConns.length} branch paths.` });
            }
        });

        // 7. Orphan Nodes check (nodes with no connections at all)
        nodes.forEach(node => {
            const hasIncoming = connections.some(c => c.target === node.id);
            const hasOutgoing = connections.some(c => c.source === node.id);

            if (node.type !== 'START' && node.type !== 'END' && !hasIncoming && !hasOutgoing) {
                results.push({ type: 'warning', text: `Orphan node found: "${node.name}" (unconnected).` });
            }
        });

        // 8. Graph Reachability (from Start to End) using BFS
        if (startNodes.length === 1 && endNodes.length >= 1) {
            const reachableNodeIds = new Set();
            const queue = [startNodes[0].id];

            while (queue.length > 0) {
                const currentId = queue.shift();
                if (reachableNodeIds.has(currentId)) continue;
                reachableNodeIds.add(currentId);

                const outgoing = connections.filter(c => c.source === currentId);
                outgoing.forEach(c => {
                    if (!reachableNodeIds.has(c.target)) {
                        queue.push(c.target);
                    }
                });
            }

            const endReachable = endNodes.some(e => reachableNodeIds.has(e.id));
            if (endReachable) {
                results.push({ type: 'success', text: 'End node is reachable from Start node.' });
            } else {
                results.push({ type: 'error', text: 'End node is NOT reachable from Start node.' });
            }
        }

        return results;
    }
}
