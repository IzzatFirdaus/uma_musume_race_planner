/**
 * Local Data Manager Alpine component.
 *
 * Backs `resources/views/livewire/local-data/manager.blade.php`, whose ONLY
 * x-data declaration is `x-data="localDataManager()"` with `x-init="init()"`.
 * Every piece of client state and every client handler on that page — stats,
 * search, selection, import preview, export, delete — therefore has to be
 * provided by this component. If a handler is missing here, the corresponding
 * button in that view silently does nothing.
 *
 * This component is the bridge in one direction only: the Livewire component
 * (`App\Livewire\LocalData\Manager`) owns workflow state and dialogs, and emits
 * an event when it wants the browser to do something. It never touches
 * localStorage itself.
 *
 * SHAPE DRIFT (pre-existing, see ARCHITECTURE.md §10):
 * `LocalRunStorageService` produces the canonical payload keyed on `id` with the
 * title at `career_run.plan_title`, but manager.blade.php and the Manager's
 * PHP handlers both expect `uuid` and `title`. `normalizeRun()` bridges the two
 * so this component honours the view contract while remaining compatible with
 * the server-defined schema. Prefer fixing the view; do not widen this shim.
 */

import { generateUuid, localRunStorage } from "../services/index.js";

function toIsoOrNull(value) {
    if (!value) {
        return null;
    }

    const date = new Date(value);

    return Number.isNaN(date.getTime()) ? null : date.toISOString();
}

export function localDataManager(config = {}) {
    return {
        /** View-shaped run list. */
        runs: [],
        /** Selected run UUIDs. */
        selectedRuns: [],
        searchQuery: "",
        /** @type {{count: number, conflicts: number, plans: Array<object>}} */
        importPreview: { count: 0, conflicts: 0, plans: [] },
        conflictResolution: "skip",
        /** Raw parsed JSON awaiting an import decision. */
        pendingImport: null,
        importError: null,
        busy: false,
        bound: false,

        /* -----------------------------------------------------------------
         * Lifecycle
         * -------------------------------------------------------------- */

        init() {
            if (this.bound) {
                return;
            }

            this.bound = true;
            this.bindLivewireEvents();
            this.refresh();
        },

        /**
         * Subscribe to every event `LocalData\Manager` emits.
         *
         * Livewire dispatches with either a named-parameter object or a
         * positional array; `payload()` normalises both.
         */
        bindLivewireEvents() {
            if (typeof window.Livewire === "undefined" || !window.Livewire.on) {
                return;
            }

            const on = (event, handler) => {
                window.Livewire.on(event, (params) => handler(this.payload(params)));
            };

            on("execute-purge-local-storage", () => this.purgeAll());

            on("select-all-runs", () => {
                this.selectAll();
                this.emitSelectedRuns();
            });

            on("export-all-runs", () => this.download(this.localRunStorage.export()));
            on("export-selected-runs", (data) =>
                this.download(this.localRunStorage.export(data.uuids ?? []))
            );

            on("delete-local-run", (data) => this.deleteRun(data.uuid));

            on("get-local-runs-for-convert", () => {
                this.emit("convert-local-runs", this.runs.map((run) => run.data));
            });

            on("get-single-run-for-convert", (data) => {
                const run = this.runs.find((item) => item.uuid === data.uuid);
                this.emit("convert-single-run", run ? { uuid: run.uuid, data: run.data } : {});
            });

            on("remove-converted-runs", (data) => this.removeConverted(data.uuids ?? []));
            on("local-runs-converted", (data) => this.removeConverted(data.uuids ?? []));

            on("execute-import", (data) => {
                this.conflictResolution = data.conflictResolution ?? "skip";
                this.executeImport();
            });
        },

        /** Flatten Livewire's dispatch shape into a plain object. */
        payload(params) {
            if (Array.isArray(params)) {
                const first = params[0];

                if (first && typeof first === "object" && !Array.isArray(first)) {
                    // A single associative array — merge any trailing named params.
                    return params.length === 1 ? { ...first } : { ...first, ...params[1] };
                }

                // Positional arguments: map onto the known handler signatures.
                return { positional: params, uuids: params[0], count: params[0] };
            }

            return params && typeof params === "object" ? params : {};
        },

        /* -----------------------------------------------------------------
         * State
         * -------------------------------------------------------------- */

        /** Exposed on window so the Livewire `$wire` bridge can reach it. */
        get localRunStorage() {
            return localRunStorage;
        },

        get runCount() {
            return this.runs.length;
        },

        get storageUsed() {
            return localRunStorage.getStats().used;
        },

        get percentUsed() {
            return localRunStorage.getStats().percent;
        },

        get isNearQuota() {
            return localRunStorage.getStats().nearQuota;
        },

        get filteredRuns() {
            const query = this.searchQuery.trim().toLowerCase();

            if (!query) {
                return this.runs;
            }

            return this.runs.filter((run) => {
                const haystack = [run.title, run.character_name, run.status, run.uuid]
                    .filter(Boolean)
                    .join(" ")
                    .toLowerCase();

                return haystack.includes(query);
            });
        },

        /** Reload from localStorage and push fresh stats to the server. */
        refresh() {
            this.runs = localRunStorage.getAll().map((run) => this.normalizeRun(run));
            this.selectedRuns = this.selectedRuns.filter((uuid) =>
                this.runs.some((run) => run.uuid === uuid)
            );
            this.pushStats();
        },

        /**
         * Map the canonical payload onto the shape manager.blade.php expects.
         * Accepts either key so a partially-migrated store still renders.
         */
        normalizeRun(run) {
            const career = run?.career_run ?? {};

            return {
                uuid: run?.id ?? run?.uuid ?? null,
                title: run?.title ?? career.plan_title ?? career.name ?? "Untitled Plan",
                character_name: run?.character_name ?? career.name ?? null,
                status: run?.status ?? career.status ?? "unknown",
                career_stage: career.career_stage ?? null,
                current_turn: career.current_turn ?? null,
                total_sp_available: career.total_sp_available ?? 0,
                stamina_percentage: career.stamina_percentage ?? null,
                created_at: toIsoOrNull(run?.created_at),
                updated_at: toIsoOrNull(run?.updated_at),
                data: run,
            };
        },

        /* -----------------------------------------------------------------
         * Formatting
         * -------------------------------------------------------------- */

        formatBytes(bytes) {
            return localRunStorage.formatSize(bytes);
        },

        getRunSize(run) {
            return localRunStorage.calculateSize(run?.data ?? run);
        },

        formatDate(value) {
            if (!value) {
                return "-";
            }

            const date = new Date(value);

            if (Number.isNaN(date.getTime())) {
                return "-";
            }

            return new Intl.DateTimeFormat(undefined, {
                year: "numeric",
                month: "short",
                day: "numeric",
                hour: "2-digit",
                minute: "2-digit",
            }).format(date);
        },

        /* -----------------------------------------------------------------
         * Selection
         * -------------------------------------------------------------- */

        toggleSelection(uuid) {
            const index = this.selectedRuns.indexOf(uuid);
            const at = index === -1;

            if (at) {
                this.selectedRuns.push(uuid);
            } else {
                this.selectedRuns.splice(index, 1);
            }

            this.emitSelectedRuns();
        },

        selectAll() {
            this.selectedRuns = this.filteredRuns.map((run) => run.uuid);
        },

        deselectAll() {
            this.selectedRuns = [];
            this.emitSelectedRuns();
        },

        emitSelectedRuns() {
            this.emit("update-selected-runs", { uuids: this.selectedRuns });
        },

        /* -----------------------------------------------------------------
         * Mutations
         * -------------------------------------------------------------- */

        deleteRun(uuid) {
            const run = this.runs.find((item) => item.uuid === uuid);
            const title = run?.title ?? "Untitled Plan";

            try {
                localRunStorage.delete(uuid);
            } catch (error) {
                this.fail(error, "Failed to delete the plan.");
                return;
            }

            this.refresh();
            this.emit("run-deleted", { title });
        },

        purgeAll() {
            let count = 0;

            try {
                count = localRunStorage.purge();
            } catch (error) {
                this.fail(error, "Failed to delete Local data.");
                return;
            }

            this.selectedRuns = [];
            this.refresh();
            this.emit("purge-completed", { count });
        },

        removeConverted(uuids) {
            if (!Array.isArray(uuids) || uuids.length === 0) {
                return;
            }

            try {
                localRunStorage.deleteMany(uuids);
            } catch (error) {
                console.error("localDataManager: could not remove converted runs", error);
            }

            this.refresh();
        },

        /* -----------------------------------------------------------------
         * Import
         * -------------------------------------------------------------- */

        /**
         * Parse the chosen file and build a preview. Conflict detection
         * compares against runs already in the store.
         */
        handleFileSelect(event) {
            const input = event?.target;
            const file = input?.files?.[0];

            this.importError = null;

            if (!file) {
                this.pendingImport = null;
                this.importPreview = { count: 0, conflicts: 0, plans: [] };
                return;
            }

            const reader = new FileReader();

            reader.onload = () => {
                try {
                    this.pendingImport = this.parseImportDocument(String(reader.result));
                } catch (error) {
                    this.pendingImport = null;
                    this.importPreview = { count: 0, conflicts: 0, plans: [] };
                    this.importError = "That file is not valid JSON exported from this planner.";
                    return;
                }

                this.importPreview = this.buildPreview(this.pendingImport);
            };

            reader.onerror = () => {
                this.importError = "The file could not be read.";
            };

            reader.readAsText(file);
        },

        /**
         * Accept the shapes this application has emitted over time: a single
         * run, a bulk export from `localRunStorage.export()`, or a raw array.
         */
        parseImportDocument(text) {
            const parsed = JSON.parse(text);

            if (Array.isArray(parsed)) {
                return parsed;
            }

            if (Array.isArray(parsed?.runs)) {
                return parsed.runs;
            }

            if (parsed && typeof parsed === "object") {
                return [parsed];
            }

            throw new Error("unrecognised import document");
        },

        buildPreview(runs) {
            const existing = new Set(this.runs.map((run) => run.uuid));
            const plans = runs.map((run) => {
                const view = this.normalizeRun(run);
                const isConflict = existing.has(view.uuid);

                return { uuid: view.uuid, title: view.title, isConflict };
            });

            return {
                count: plans.length,
                conflicts: plans.filter((plan) => plan.isConflict).length,
                plans,
            };
        },

        /**
         * Apply the chosen conflict resolution and commit to localStorage.
         *
         *   skip     — discard conflicting runs
         *   overwrite— replace the stored run with the imported one
         *   copy     — import under a fresh UUID with a "(Copy)" suffix
         */
        executeImport() {
            if (!Array.isArray(this.pendingImport) || this.pendingImport.length === 0) {
                return;
            }

            const incoming = this.pendingImport.map((run) => this.normalizeRun(run));
            const existing = new Set(this.runs.map((run) => run.uuid));
            let imported = 0;
            let skipped = 0;

            const accepted = [];

            incoming.forEach((run) => {
                if (!existing.has(run.uuid)) {
                    accepted.push(run);
                    imported += 1;
                    return;
                }

                if (this.conflictResolution === "overwrite") {
                    accepted.push(run);
                    imported += 1;
                    return;
                }

                if (this.conflictResolution === "copy") {
                    accepted.push({
                        ...run,
                        uuid: generateUuid(),
                        title: `${run.title} (Copy)`,
                    });
                    imported += 1;
                    return;
                }

                skipped += 1;
            });

            if (accepted.length > 0) {
                try {
                    const merged = this.mergeIntoStore(accepted);
                    localRunStorage.replaceAll(merged);
                } catch (error) {
                    this.fail(error, "Import failed. Nothing was changed.");
                    return;
                }
            }

            this.refresh();
            this.emit("import-completed", { imported, skipped, errors: [] });
            this.pendingImport = null;
            this.importPreview = { count: 0, conflicts: 0, plans: [] };
        },

        /** Return the post-import run list without writing it. */
        mergeIntoStore(incoming) {
            const byUuid = new Map(localRunStorage.getAll().map((run) => [run.id, run]));

            incoming.forEach((view) => {
                const data = view.data ?? {};
                const next = {
                    ...data,
                    id: view.uuid,
                    storage_mode: "local",
                    updated_at: view.updated_at ?? new Date().toISOString(),
                };

                if (next.career_run && typeof next.career_run === "object") {
                    next.career_run = { ...next.career_run, plan_title: view.title };
                } else {
                    next.career_run = { plan_title: view.title };
                }

                byUuid.set(view.uuid, next);
            });

            return Array.from(byUuid.values());
        },

        /* -----------------------------------------------------------------
         * Export
         * -------------------------------------------------------------- */

        exportAllRuns() {
            this.download(localRunStorage.export());
        },

        exportSelectedRuns() {
            if (this.selectedRuns.length === 0) {
                this.emit("toast", {
                    type: "warning",
                    message: "Please select at least one plan to export",
                });
                return;
            }

            this.download(localRunStorage.export(this.selectedRuns));
        },

        download(document_) {
            const stamp = new Date().toISOString().slice(0, 10);
            const blob = new Blob([JSON.stringify(document_, null, 2)], {
                type: "application/json",
            });
            const url = URL.createObjectURL(blob);
            const anchor = window.document.createElement("a");

            anchor.href = url;
            anchor.download = `uma-local-runs-${stamp}.json`;
            window.document.body.appendChild(anchor);
            anchor.click();
            anchor.remove();
            URL.revokeObjectURL(url);

            this.emit("export-completed", { count: document_.run_count ?? 0 });
        },

        /* -----------------------------------------------------------------
         * Plumbing
         * -------------------------------------------------------------- */

        /** Tell the server component the browser store changed. */
        pushStats() {
            this.emit("storage-stats-updated", localRunStorage.getStats());
        },

        emit(event, payload = {}) {
            window.Livewire?.dispatch?.(event, payload);
        },

        fail(error, fallbackMessage) {
            console.error("localDataManager:", error);
            this.emit("toast", {
                type: "error",
                message: error?.message || fallbackMessage,
            });
        },

        /** Required by manager.blade.php's confirm-delete button. */
        confirmDeleteRun(run) {
            const confirmed =
                typeof window.confirm === "function"
                    ? window.confirm(
                          `Delete "${run?.title ?? "Untitled Plan"}"? This cannot be undone.`
                      )
                    : true;

            if (confirmed) {
                this.deleteRun(run?.uuid);
            }
        },
    };
}

export default localDataManager;
