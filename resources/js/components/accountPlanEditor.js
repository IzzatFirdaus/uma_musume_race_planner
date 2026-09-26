/**
 * Account plan editor Alpine component.
 *
 * Referenced from `resources/views/livewire/dashboard/plan-details-page.blade.php`:
 *
 *   <div x-data="accountPlanEditor({ planId, isEditMode, isDirty })"
 *        x-init="init()" @keydown.ctrl.s.window.prevent="save()">
 *
 * The same view reaches the component imperatively through
 * `Alpine.$data(el).onSaveSuccess()` / `.onSaveError()` in response to the
 * `plan-saved` and `show-error` Livewire events, so those two method names are
 * part of the contract and must not be renamed.
 *
 * Scope: unsaved-state tracking, Ctrl/Cmd+S, and the dirty-state warning. The
 * authoritative plan data lives on the server in `PlanDetailsPage`; this
 * component never owns a copy of it. `isDirty` is entangled, so assigning to it
 * round-trips to the Livewire component.
 */

import { draftService } from "../services/index.js";

export function accountPlanEditor(config = {}) {
    return {
        planId: config.planId ?? null,
        isEditMode: Boolean(config.isEditMode),
        isDirty: Boolean(config.isDirty ?? false),
        isSaving: false,
        lastSavedAt: null,
        restoredDraft: null,
        autosaveHandle: null,
        bound: false,

        /**
         * Collect current form data for autosave.
         *
         * Deliberately scope-limited: the fields below are the ones the plan
         * form owns and that a user would be upset to lose. Anything added
         * here must also exist in `PlanDetailsPage`'s validation rules.
         */
        collect() {
            const root =
                this.$root instanceof HTMLElement
                    ? this.$root
                    : document.querySelector("[data-testid='plan-details-page']");

            if (!root) {
                return null;
            }

            const read = (name) => {
                const field = root.querySelector(`[name="${name}"]`);

                if (!field) {
                    return null;
                }

                return "value" in field ? field.value : field.getAttribute("value");
            };

            return {
                plan_title: read("plan_title"),
                name: read("name"),
                career_stage: read("career_stage"),
                class: read("class"),
                turn_before: read("turn_before"),
                total_available_skill_points: read("total_available_skill_points"),
                stamina_percentage: read("stamina_percentage"),
                energy: read("energy"),
                notes: read("notes"),
            };
        },

        init() {
            if (this.bound) {
                return;
            }

            this.bound = true;
            this.offerDraftRestore();
            this.startAutosave();
            this.watchSaveResults();
        },

        draftKey() {
            return this.planId ? `plan-${this.planId}` : "plan-unknown";
        },

        /* -----------------------------------------------------------------
         * Saving
         * -------------------------------------------------------------- */

        /**
         * Ask the server to persist. The server owns validation and
         * authorization; this only triggers the round trip and reflects the
         * in-flight state.
         */
        save() {
            if (!this.isEditMode || this.isSaving) {
                return;
            }

            this.isSaving = true;

            this.$wire?.call?.("save")
                ?.then?.(() => {
                    this.isSaving = false;
                })
                .catch?.(() => {
                    this.isSaving = false;
                });

            // The authoritative outcome arrives via plan-saved / show-error.
            window.setTimeout(() => {
                this.isSaving = false;
            }, 5000);
        },

        /** Called by the view's `plan-saved` Livewire handler. */
        onSaveSuccess() {
            this.isSaving = false;
            this.isDirty = false;
            this.lastSavedAt = new Date().toISOString();

            // The draft has served its purpose once the run is persisted.
            draftService.clear(this.draftKey());
        },

        /** Called by the view's `show-error` Livewire handler. */
        onSaveError() {
            this.isSaving = false;
            this.isDirty = true;
        },

        watchSaveResults() {
            if (typeof window.Livewire === "undefined" || !window.Livewire.on) {
                return;
            }

            window.Livewire.on("plan-saved", () => this.onSaveSuccess());
            window.Livewire.on("show-error", () => this.onSaveError());
            window.Livewire.on("plan-updated", () => this.onSaveSuccess());
        },

        /* -----------------------------------------------------------------
         * Drafts (FR-10)
         * -------------------------------------------------------------- */

        startAutosave() {
            if (!this.isEditMode) {
                return;
            }

            this.autosaveHandle = draftService.startAutosave(
                this.draftKey(),
                () => this.collect(),
                draftService.DEFAULT_DRAFT_INTERVAL,
                (error) => {
                    this.autosaveError = error?.message ?? null;
                }
            );
        },

        /**
         * If a newer draft exists than the last server save, offer to restore.
         * Never restores silently — the user decides.
         */
        offerDraftRestore() {
            if (!this.isEditMode) {
                return;
            }

            const draft = draftService.latest(this.draftKey());

            if (draft?.data) {
                this.restoredDraft = draft;
            }
        },

        applyRestoredDraft() {
            if (!this.restoredDraft?.data) {
                return;
            }

            const root =
                this.$root instanceof HTMLElement
                    ? this.$root
                    : document.querySelector("[data-testid='plan-details-page']");

            if (!root) {
                return;
            }

            Object.entries(this.restoredDraft.data).forEach(([name, value]) => {
                if (value === null || value === undefined) {
                    return;
                }

                const field = root.querySelector(`[name="${name}"]`);

                if (field && "value" in field) {
                    field.value = value;
                    field.dispatchEvent(new Event("input", { bubbles: true }));
                }
            });

            this.isDirty = true;
            this.restoredDraft = null;
        },

        discardRestoredDraft() {
            draftService.clear(this.draftKey());
            this.restoredDraft = null;
        },

        stop() {
            this.autosaveHandle?.stop();
            this.autosaveHandle = null;
        },
    };
}

export default accountPlanEditor;
