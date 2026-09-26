/**
 * Offline draft manager Alpine component.
 *
 * General-purpose wrapper around `draftService` for any form that wants periodic
 * local autosave. `accountPlanEditor` uses the service directly and is the
 * reference implementation; this component exists for forms that need the
 * behaviour without the plan-specific field mapping.
 *
 * Intended usage:
 *
 *   <div x-data="offlineDraftManager({ draftKey: 'plan-42', interval: 30000 })"
 *        x-init="init()">
 *       <template x-if="hasRestorableDraft"> ... restore / discard UI ... </template>
 *   </div>
 *
 * Drafts are transient. A draft is not a career run: it never appears in the
 * Local run list and is discarded once the corresponding save succeeds.
 */

import { draftService } from "../services/index.js";

export function offlineDraftManager(config = {}) {
    return {
        draftKey: config.draftKey ?? null,
        interval: Number(config.interval) > 0 ? Number(config.interval) : draftService.DEFAULT_DRAFT_INTERVAL,
        /** Provide a function returning the data to persist. */
        collect: typeof config.collect === "function" ? config.collect : null,

        handle: null,
        lastSavedAt: null,
        error: null,
        pendingRestore: null,
        enabled: true,
        bound: false,

        init() {
            if (this.bound) {
                return;
            }

            this.bound = true;
            this.inspect();
        },

        get hasRestorableDraft() {
            return this.pendingRestore !== null;
        },

        get versionCount() {
            return draftService.get(this.key)?.versions?.length ?? 0;
        },

        get key() {
            return this.draftKey ?? "offline-draft";
        },

        get versions() {
            return draftService.get(this.key)?.versions ?? [];
        },

        inspect() {
            const draft = draftService.latest(this.key);

            this.pendingRestore = draft?.data ? draft : null;
            this.lastSavedAt = draft?.saved_at ?? null;
        },

        start() {
            if (this.handle || !this.enabled || !this.collect) {
                return;
            }

            this.handle = draftService.startAutosave(
                this.key,
                () => this.collect(),
                this.interval,
                (error) => {
                    this.error = error?.message ?? null;
                }
            );
        },

        stop() {
            this.handle?.stop();
            this.handle = null;
        },

        saveNow() {
            if (!this.collect) {
                return null;
            }

            let data = null;

            try {
                data = this.collect();
            } catch (error) {
                this.error = error?.message ?? null;
                return null;
            }

            if (!data) {
                return null;
            }

            try {
                const saved = draftService.save(this.key, data);
                this.lastSavedAt = saved.saved_at;
                this.error = null;
                this.pendingRestore = null;
                return saved;
            } catch (error) {
                this.error = error?.message ?? null;
                return null;
            }
        },

        /** Called after the real save succeeded; drop the draft. */
        clear() {
            draftService.clear(this.key);
            this.pendingRestore = null;
            this.lastSavedAt = null;
        },

        setCollector(fn) {
            if (typeof fn === "function") {
                this.collect = fn;
                this.start();
            }
        },

        destroy() {
            this.stop();
        },
    };
}

export default offlineDraftManager;
