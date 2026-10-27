/**
 * Storage Manager
 * Handles LocalStorage persistence, Exporting to JSON file, and Importing from JSON file.
 */
class StorageManager {
    constructor(storageKey = 'wf_builder_active_workflow') {
        this.storageKey = storageKey;
    }

    /**
     * Save workflow model to localStorage
     */
    saveToLocalStorage(workflowModel) {
        try {
            const data = workflowModel.toJSON();
            localStorage.setItem(this.storageKey, JSON.stringify(data));
            return { success: true, message: 'Workflow saved to browser localStorage.' };
        } catch (error) {
            return { success: false, message: 'Failed to save to localStorage: ' + error.message };
        }
    }

    /**
     * Load workflow model from localStorage
     */
    loadFromLocalStorage(workflowModel) {
        try {
            const raw = localStorage.getItem(this.storageKey);
            if (!raw) {
                return { success: false, message: 'No saved workflow found in localStorage.' };
            }
            const data = JSON.parse(raw);
            const validation = this.validateWorkflowJSON(data);
            if (!validation.valid) {
                return { success: false, message: 'Saved workflow data is corrupted: ' + validation.error };
            }

            workflowModel.fromJSON(data);
            return { success: true, message: 'Workflow loaded successfully from localStorage.' };
        } catch (error) {
            return { success: false, message: 'Failed to load from localStorage: ' + error.message };
        }
    }

    /**
     * Export workflow model as downloadable JSON file
     */
    exportJSON(workflowModel) {
        try {
            const data = workflowModel.toJSON();
            const jsonStr = JSON.stringify(data, null, 2);
            const blob = new Blob([jsonStr], { type: 'application/json' });
            const url = URL.createObjectURL(blob);

            const filename = (workflowModel.name || 'workflow')
                .toLowerCase()
                .replace(/[^a-z0-9]/g, '-')
                .replace(/-+/g, '-') + '.json';

            const link = document.createElement('a');
            link.href = url;
            link.download = filename;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            URL.revokeObjectURL(url);

            return { success: true, message: `Exported workflow to ${filename}` };
        } catch (error) {
            return { success: false, message: 'Export failed: ' + error.message };
        }
    }

    /**
     * Read JSON file via File API and load into model
     */
    importJSONFile(file, workflowModel, callback) {
        if (!file) return;

        const reader = new FileReader();
        reader.onload = (e) => {
            try {
                const data = JSON.parse(e.target.result);
                const validation = this.validateWorkflowJSON(data);
                if (!validation.valid) {
                    callback({ success: false, message: 'Invalid JSON file: ' + validation.error });
                    return;
                }

                workflowModel.fromJSON(data);
                callback({ success: true, message: `Successfully imported "${workflowModel.name}"` });
            } catch (err) {
                callback({ success: false, message: 'JSON Parse Error: ' + err.message });
            }
        };

        reader.onerror = () => {
            callback({ success: false, message: 'Failed to read file from disk.' });
        };

        reader.readAsText(file);
    }

    /**
     * Schema validation for import JSON data
     */
    validateWorkflowJSON(data) {
        if (!data || typeof data !== 'object') {
            return { valid: false, error: 'Root element must be a JSON object.' };
        }
        if (!Array.isArray(data.nodes)) {
            return { valid: false, error: 'Missing or invalid "nodes" array.' };
        }
        if (!Array.isArray(data.connections)) {
            return { valid: false, error: 'Missing or invalid "connections" array.' };
        }

        // Validate each node structure
        for (let i = 0; i < data.nodes.length; i++) {
            const n = data.nodes[i];
            if (!n.id || !n.type) {
                return { valid: false, error: `Node at index ${i} is missing "id" or "type".` };
            }
        }

        // Validate connections
        for (let i = 0; i < data.connections.length; i++) {
            const c = data.connections[i];
            if (!c.source || !c.target) {
                return { valid: false, error: `Connection at index ${i} is missing "source" or "target".` };
            }
        }

        return { valid: true };
    }
}
