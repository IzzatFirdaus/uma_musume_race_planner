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
import "./stores/index.js";

// Import keyboard shortcuts manager (Task 30.2 - Accessibility)
import "./keyboard-shortcuts.js";

// Import services and attach to window for global access
import { localRunStorage, draftService } from "./services/index.js";
window.localRunStorage = localRunStorage;
window.draftService = draftService;

// Import and register Alpine components
import { localDataManager } from "./components/localDataManager.js";
window.localDataManager = localDataManager;

import { offlineDraftManager } from "./components/offlineDraftManager.js";
window.offlineDraftManager = offlineDraftManager;

import { accountPlanEditor } from "./components/accountPlanEditor.js";
window.accountPlanEditor = accountPlanEditor;

import { raceSnapshotQuota } from "./components/raceSnapshotQuota.js";
window.raceSnapshotQuota = raceSnapshotQuota;

// Start Livewire (which also starts Alpine)
Livewire.start();

// Initialize stores after Livewire/Alpine starts
// Alpine stores don't auto-call init() like components do
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

// Import SweetAlert2
import Swal from "sweetalert2";
window.Swal = Swal;

// Import and run your main application logic
import "./main.js";
// Import character-specific styles so they're bundled into app.css
import "../css/characters.css";
