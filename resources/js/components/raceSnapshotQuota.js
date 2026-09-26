/**
 * Race snapshot quota guard.
 *
 * `career_snapshots` is the only child table of a run that grows
 * monotonically and without an upper bound. Unlike `stat_progress`, which is
 * capped by the 78-turn career length, a user can snapshot a race entry
 * repeatedly. On a Local run those snapshots live in the browser store, where a
 * quota exhaustion is unrecoverable.
 *
 * This component warns before that happens rather than after. It does not
 * enforce a limit — the server does not either — it just surfaces the pressure
 * and offers a non-destructive way out.
 *
 * Intended usage:
 *
 *   <div x-data="raceSnapshotQuota({ planId, warnAt: 0.8 })" x-init="init()">
 *       <template x-if="nearLimit"> ... warn the user ... </template>
 *   </div>
 */

import { localRunStorage } from "../services/index.js";

const DEFAULT_WARN_RATIO = 0.8;

export function raceSnapshotQuota(config = {}) {
    return {
        planId: config.planId ?? null,
        /** Warn once localStorage usage passes this fraction of the quota. */
        warnAt:
            typeof config.warnAt === "number" && config.warnAt > 0
                ? config.warnAt
                : DEFAULT_WARN_RATIO,

        snapshotCount: 0,
        usedBytes: 0,
        quotaBytes: localRunStorage.QUOTA_WARNING_THRESHOLD,
        ratio: 0,
        nearLimit: false,
        bound: false,
        timer: null,

        init() {
            if (this.bound) {
                return;
            }

            this.bound = true;
            this.measure();

            // Only a Local run can exhaust the browser store; an Account run is
            // bounded by the database.
            if (this.isLocalRun()) {
                this.timer = window.setInterval(() => this.measure(), 5000);
            }
        },

        destroy() {
            if (this.timer !== null) {
                window.clearInterval(this.timer);
                this.timer = null;
            }
        },

        isLocalRun() {
            if (!this.planId) {
                return false;
            }

            return localRunStorage.getByUuid(this.planId) !== null;
        },

        measure() {
            const stats = localRunStorage.getStats();
            const run = this.planId ? localRunStorage.getByUuid(this.planId) : null;

            this.usedBytes = stats.used;
            this.quotaBytes = stats.available;
            this.ratio = stats.available > 0 ? stats.used / stats.available : 0;
            this.nearLimit = stats.nearQuota || this.ratio >= this.warnAt;
            this.snapshotCount = Array.isArray(run?.snapshots) ? run.snapshots.length : 0;

            if (this.nearLimit) {
                this.raiseWarning();
            }

            return this;
        },

        raiseWarning() {
            window.dispatchEvent(
                new CustomEvent("uma:storage-pressure", {
                    detail: {
                        usedBytes: this.usedBytes,
                        quotaBytes: this.quotaBytes,
                        ratio: this.ratio,
                        usedLabel: localRunStorage.formatSize(this.usedBytes),
                        quotaLabel: localRunStorage.formatSize(this.quotaBytes),
                    },
                })
            );
        },

        formatBytes(bytes) {
            return localRunStorage.formatSize(bytes);
        },

        /**
         * Drop the oldest snapshots for a Local run. The newest are kept,
         * because recent race-day state is the data users actually rely on.
         *
         * @param {number} keep How many to retain.
         * @returns {{removed: number, retained: number}|null}
         */
        trimSnapshots(keep = 10) {
            if (!this.planId) {
                return null;
            }

            const run = localRunStorage.getByUuid(this.planId);

            if (!run || !Array.isArray(run.snapshots) || run.snapshots.length === 0) {
                return null;
            }

            const retained = run.snapshots.slice(-keep);
            const removed = run.snapshots.length - retained.length;

            if (removed === 0) {
                return { removed: 0, retained: retained.length };
            }

            try {
                localRunStorage.update(this.planId, { snapshots: retained });
            } catch (error) {
                console.error("raceSnapshotQuota: could not trim snapshots", error);
                return null;
            }

            this.measure();

            return { removed, retained: retained.length };
        },
    };
}

export default raceSnapshotQuota;
