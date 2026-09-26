// Import the initial bootstrap configuration (for Axios, etc.)
import "./bootstrap";

// Import Livewire and Alpine from Livewire's bundle (Livewire 3 approach)
// This ensures we use the same Alpine instance as Livewire
import {
    Livewire,
    Alpine,
} from "../../vendor/livewire/livewire/dist/livewire.esm";
window.Alpine = Alpine;

// Import and register stores (must be before Livewire.start())
import { registerStores } from "./stores/index.js";
registerStores(Alpine);

// Import keyboard shortcuts manager (Task 30.2 - Accessibility)
import "./keyboard-shortcuts.js";

// Import services and attach to window for global access
import { localRunStorage, draftService } from "./services/index.js";
window.localRunStorage = localRunStorage;
window.draftService = draftService;

// Import and register Alpine components.
// Alpine.data() is what makes `x-data="componentName(...)"` resolve; assigning
// to window alone does not, which is why the manager and plan editor pages
// were inert.
import { localDataManager } from "./components/localDataManager.js";
window.localDataManager = localDataManager;
Alpine.data("localDataManager", localDataManager);

import { offlineDraftManager } from "./components/offlineDraftManager.js";
window.offlineDraftManager = offlineDraftManager;
Alpine.data("offlineDraftManager", offlineDraftManager);

import { accountPlanEditor } from "./components/accountPlanEditor.js";
window.accountPlanEditor = accountPlanEditor;
Alpine.data("accountPlanEditor", accountPlanEditor);

import { raceSnapshotQuota } from "./components/raceSnapshotQuota.js";
window.raceSnapshotQuota = raceSnapshotQuota;
Alpine.data("raceSnapshotQuota", raceSnapshotQuota);

// Start Livewire (which also starts Alpine)
Livewire.start();

// Initialize stores after Livewire/Alpine starts.
// Alpine stores don't auto-call init() like components do, so each store
// exposes an idempotent init() that app.js invokes explicitly.
if (Alpine.store("connection")?.init) {
    Alpine.store("connection").init();
}
if (Alpine.store("preferences")?.init) {
    Alpine.store("preferences").init();
}
if (Alpine.store("toast")?.init) {
    Alpine.store("toast").init();
}
if (Alpine.store("keyboard")?.init) {
    Alpine.store("keyboard").init();
}

// Surface failed Livewire requests to the connection store so the
// x-connection-banner and x-reconnection-prompt-modal can react.
document.addEventListener("livewire:init", () => {
    const connection = Alpine.store("connection");

    Livewire.hook("request", ({ fail, component }) => {
        if (!fail) {
            connection?.clearFailure();
            return;
        }

        // 419 is an expired CSRF session, not a connectivity problem.
        if (fail.status === 419) {
            return;
        }

        connection?.onRequestFailure(
            fail.status,
            `The server responded with ${fail.status}. Your last change may not have been saved.`
        );
    });
});

// Apply theme changes requested by the keyboard shortcut.
window.addEventListener("uma:toggle-theme", () => {
    Alpine.store("preferences")?.toggle();
});

// SweetAlert2
import Swal from "sweetalert2";
window.Swal = Swal;

// Import and run your main application logic
import "./main.js";
// Import character-specific styles so they're bundled into app.css
import "../css/characters.css";
