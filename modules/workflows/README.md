# Visual Workflow Builder

A standalone, browser-based visual workflow builder and execution engine built with standard web technologies (HTML5, CSS3, Vanilla JavaScript).

## Features

- 🎨 **Visual Canvas**: Infinite grid canvas supporting Node dragging, SVG smooth Bezier curve connections, zooming, and panning.
- 🧩 **Node Types**: Supports `START`, `TASK`, `APPROVAL`, `DECISION`, and `END` nodes with customizable properties (Name, Description, Assigned Role).
- 🔀 **Decision Branching**: Create labeled decision paths (`YES`, `NO`, `APPROVE`, `REJECT`) connected to logic nodes.
- ⚙️ **Data-Driven Architecture**: Clean state separation between `WorkflowModel`, SVG SVG SVG connection renderer, interactive DOM canvas, and validation engine.
- 💾 **Save / Load / Export / Import**:
  - Save/Load to browser `localStorage`.
  - Export full workflow model as `.json` file.
  - Import previously exported `.json` files with strict schema validation.
- 🔍 **Graph Topology Validation**: Validates presence of Start/End nodes, unique IDs, connection integrity, orphan nodes, and reachability.
- ▶️ **Interactive Workflow Simulator**: Step-by-step visual execution flow highlighting active nodes and prompting users at decision branch nodes.

## Technology Stack

- **HTML5**: Semantic UI layout and native drag-and-drop.
- **CSS3**: Modern dark developer tool styling, SVG line animations, and responsive flexbox/grid layout.
- **Vanilla JavaScript (ES6+)**: Zero external libraries, zero frameworks, zero backend dependencies. Runs 100% in browser.

## File Structure

```
workflow-builder/
├── index.html        # Main interface container & markup
├── css/
│   └── style.css     # Developer tool aesthetics & node styling
├── js/
│   ├── workflow.js   # Pure Data Model (Nodes, Connections)
│   ├── nodes.js      # Node types registry & DOM rendering
│   ├── connections.js# SVG Bezier curve line rendering & arrow markers
│   ├── canvas.js     # Viewport zoom/pan, dragging, & port wiring
│   ├── storage.js    # LocalStorage & JSON Export/Import
│   ├── validation.js # Graph reachability & topology validator
│   ├── simulation.js # Step-by-step interactive workflow engine
│   └── app.js        # Main initialization & toolbar controller
└── README.md
```

## Quick Start

Simply open `index.html` in any modern web browser (Google Chrome, Microsoft Edge, Mozilla Firefox, or Apple Safari).
