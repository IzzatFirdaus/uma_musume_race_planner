/**
 * Local run storage and offline drafts.
 *
 * Browser-side counterpart to `App\Services\LocalRunStorageService`, which is
 * the server-side schema authority. The constants and field names below MUST
 * stay in lockstep with that service — it defines the canonical payload shape,
 * the schema version, the migration rules, and the 5 MB quota threshold.
 *
 * Local runs are the ONLY copy of the data. The server has no record of them
 * and cannot recover them, so every write here is treated as critical.
 */

const STORAGE_KEY = "uma_local_runs";
const SCHEMA_VERSION = "1.0.0";
const QUOTA_WARNING_THRESHOLD = 5 * 1024 * 1024;
const DRAFT_STORAGE_KEY = "uma_plan_drafts";
const DRAFT_MAX_VERSIONS = 3;
const DEFAULT_DRAFT_INTERVAL = 30000;

const COLLECTION_KEYS = [
    "stat_progress",
    "skills",
    "goals",
    "race_predictions",
    "snapshots",
    "activity_log",
];

function nowIso() {
    return new Date().toISOString();
}

/**
 * Generate a v4 UUID.
 *
 * Prefers the platform implementation, then `crypto.getRandomValues`, and only
 * degrades to `Math.random` as a last resort. Exported because the import flow
 * needs to mint fresh identifiers for the "import as copy" resolution.
 */
export function generateUuid() {
    if (typeof crypto !== "undefined" && typeof crypto.randomUUID === "function") {
        return crypto.randomUUID();
    }

    if (typeof crypto !== "undefined" && typeof crypto.getRandomValues === "function") {
        const bytes = crypto.getRandomValues(new Uint8Array(16));
        bytes[6] = (bytes[6] & 0x0f) | 0x40;
        bytes[8] = (bytes[8] & 0x3f) | 0x80;

        const hex = Array.from(bytes, (b) => b.toString(16).padStart(2, "0"));
        return [
            hex.slice(0, 4).join(""),
            hex.slice(4, 6).join(""),
            hex.slice(6, 8).join(""),
            hex.slice(8, 10).join(""),
            hex.slice(10, 16).join(""),
        ].join("-");
    }

    return "xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx".replace(/[xy]/g, (c) => {
        const r = (Math.random() * 16) | 0;
        const v = c === "x" ? r : (r & 0x0f) | 0x40;
        return v.toString(16);
    });
}

function emptyCollection() {
    return COLLECTION_KEYS.reduce((acc, key) => {
        acc[key] = [];
        return acc;
    }, {});
}

/**
 * Forward-migrate a run payload to the current schema version.
 *
 * Mirrors `LocalRunStorageService::migrateToV1()`.
 */
function migrateRun(data) {
    if (!data || typeof data !== "object") {
        return data;
    }

    const version = data.schema_version ?? "0.0.0";

    if (version !== SCHEMA_VERSION) {
        data.id = data.id ?? generateUuid();
        data.created_at = data.created_at ?? nowIso();
        data.updated_at = data.updated_at ?? nowIso();

        if (data.career_run && typeof data.career_run === "object") {
            const run = data.career_run;

            if (run.total_sp !== undefined && run.total_sp_available === undefined) {
                run.total_sp_available = run.total_sp;
                delete run.total_sp;
            }

            if (run.stamina_pct !== undefined && run.stamina_percentage === undefined) {
                run.stamina_percentage = run.stamina_pct;
                delete run.stamina_pct;
            }

            if (run.turn !== undefined && run.current_turn === undefined) {
                run.current_turn = run.turn;
                delete run.turn;
            }
        }

        COLLECTION_KEYS.forEach((key) => {
            if (!Array.isArray(data[key])) {
                data[key] = [];
            }
        });

        data.schema_version = SCHEMA_VERSION;
    }

    return data;
}

function emptyStore() {
    return {
        schema_version: SCHEMA_VERSION,
        runs: [],
        last_modified: nowIso(),
    };
}

class QuotaError extends Error {
    constructor(message) {
        super(message);
        this.name = "QuotaError";
    }
}

export const localRunStorage = {
    STORAGE_KEY,
    SCHEMA_VERSION,
    QUOTA_WARNING_THRESHOLD,

    /**
     * Read the whole store, migrating each run forward.
     *
     * Never throws: a corrupt or unparseable store yields an empty one so the
     * app stays usable. A write-back of the migrated store is attempted
     * best-effort and its failure is ignored.
     */
    read() {
        let raw = null;

        try {
            raw = localStorage.getItem(STORAGE_KEY);
        } catch (error) {
            console.error("localRunStorage: localStorage is unavailable", error);
            return emptyStore();
        }

        if (!raw) {
            return emptyStore();
        }

        let parsed;
        try {
            parsed = JSON.parse(raw);
        } catch (error) {
            console.error("localRunStorage: store is corrupt and has been reset", error);
            return emptyStore();
        }

        if (!parsed || typeof parsed !== "object") {
            return emptyStore();
        }

        if (!Array.isArray(parsed.runs)) {
            parsed.runs = [];
        }

        parsed.schema_version = parsed.schema_version ?? SCHEMA_VERSION;
        parsed.last_modified = parsed.last_modified ?? nowIso();

        let migrated = false;
        parsed.runs = parsed.runs.map((run) => {
            const before = run?.schema_version;
            const next = migrateRun(run);
            if (next !== run || before !== SCHEMA_VERSION) {
                migrated = true;
            }
            return next;
        });

        if (migrated) {
            this.write(parsed);
        }

        return parsed;
    },

    /**
     * Persist the whole store.
     *
     * @throws {QuotaError} when the browser quota is exhausted.
     */
    write(store) {
        const payload = {
            ...store,
            schema_version: SCHEMA_VERSION,
            runs: Array.isArray(store.runs) ? store.runs : [],
            last_modified: nowIso(),
        };

        try {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(payload));
        } catch (error) {
            const isQuota =
                error?.name === "QuotaExceededError" ||
                error?.name === "NS_ERROR_DOM_QUOTA_REACHED" ||
                error?.code === 22 ||
                error?.code === 1014;

            if (isQuota) {
                throw new QuotaError(
                    "Browser storage is full. Delete or export a Local career run, then try again."
                );
            }

            throw error;
        }

        return payload;
    },

    /** All local runs. */
    getAll() {
        return this.read().runs;
    },

    getByUuid(uuid) {
        return this.read().runs.find((run) => run.id === uuid) ?? null;
    },

    count() {
        return this.read().runs.length;
    },

    /**
     * Create a run. Accepts the payload built by
     * `LocalRunStorageService::buildQuickPlanPayload()` and guarantees the
     * shape is complete even if the server omitted optional keys.
     */
    create(planData) {
        if (!planData || typeof planData !== "object") {
            throw new Error("localRunStorage.create: a run payload is required");
        }

        const store = this.read();
        const run = migrateRun({
            ...emptyCollection(),
            ...planData,
            id: planData.id ?? generateUuid(),
            schema_version: SCHEMA_VERSION,
            storage_mode: "local",
            created_at: planData.created_at ?? nowIso(),
            updated_at: nowIso(),
        });

        if (store.runs.some((existing) => existing.id === run.id)) {
            throw new Error(`localRunStorage.create: run ${run.id} already exists`);
        }

        store.runs.push(run);
        this.write(store);

        return run;
    },

    /**
     * Merge a partial update into an existing run.
     *
     * Top-level keys are replaced; the `career_run` sub-object and the
     * collection arrays are merged so a partial form submission cannot drop
     * unrelated data.
     */
    update(uuid, changes) {
        const store = this.read();
        const index = store.runs.findIndex((run) => run.id === uuid);

        if (index === -1) {
            return null;
        }

        const current = store.runs[index];
        const next = { ...current, ...changes, updated_at: nowIso() };

        if (changes.career_run && typeof changes.career_run === "object") {
            next.career_run = { ...(current.career_run ?? {}), ...changes.career_run };
        }

        COLLECTION_KEYS.forEach((key) => {
            if (Array.isArray(changes[key])) {
                next[key] = changes[key];
            }
        });

        next.id = uuid;
        next.schema_version = SCHEMA_VERSION;

        store.runs[index] = next;
        this.write(store);

        return next;
    },

    delete(uuid) {
        const store = this.read();
        const before = store.runs.length;
        store.runs = store.runs.filter((run) => run.id !== uuid);

        if (store.runs.length === before) {
            return null;
        }

        this.write(store);

        return uuid;
    },

    deleteMany(uuids) {
        const targets = new Set(Array.isArray(uuids) ? uuids : [uuids]);
        const store = this.read();
        const before = store.runs.length;

        store.runs = store.runs.filter((run) => !targets.has(run.id));
        this.write(store);

        return before - store.runs.length;
    },

    purge() {
        const count = this.count();
        this.write(emptyStore());

        return count;
    },

    /**
     * Replace the entire store. Used by import.
     */
    replaceAll(runs) {
        if (!Array.isArray(runs)) {
            throw new Error("localRunStorage.replaceAll: expected an array of runs");
        }

        const store = emptyStore();
        store.runs = runs.map((run) =>
            migrateRun({
                ...emptyCollection(),
                ...run,
                id: run.id ?? generateUuid(),
                schema_version: SCHEMA_VERSION,
                storage_mode: "local",
            })
        );

        this.write(store);

        return store.runs.length;
    },

    /**
     * Byte size of a run, matching `calculateStorageSize()` on the server
     * (which measures `strlen(json_encode($data))`).
     */
    calculateSize(data) {
        return new Blob([JSON.stringify(data ?? {})]).size;
    },

    formatSize(bytes) {
        const units = ["B", "KB", "MB", "GB"];
        let value = Number(bytes) || 0;
        let unit = 0;

        while (value >= 1024 && unit < units.length - 1) {
            value /= 1024;
            unit += 1;
        }

        return `${value.toFixed(unit === 0 ? 0 : 1)} ${units[unit]}`;
    },

    /**
     * Storage statistics.
     *
     * @returns {{used: number, available: number, count: number, percent: number, nearQuota: boolean}}
     *          Shaped to match `LocalData\Manager::updateStorageStats()`.
     */
    getStats() {
        const store = this.read();
        const used = this.calculateSize(store);
        const available = QUOTA_WARNING_THRESHOLD;
        const percent = Math.min(100, Math.round((used / available) * 100));

        return {
            used,
            available,
            count: store.runs.length,
            percent,
            nearQuota: used >= available,
        };
    },

    /**
     * Build an export document.
     */
    export(uuids = null) {
        const store = this.read();
        const targets = Array.isArray(uuids) && uuids.length > 0 ? new Set(uuids) : null;
        const runs = targets ? store.runs.filter((run) => targets.has(run.id)) : store.runs;

        return {
            schema_version: SCHEMA_VERSION,
            exported_at: nowIso(),
            run_count: runs.length,
            runs,
        };
    },
};

/**
 * Offline draft autosave (FR-10).
 *
 * Keeps the last {@link DRAFT_MAX_VERSIONS} versions of an in-progress form so
 * a crash, a tab close, or a failed Livewire request does not lose typed data.
 * Drafts are stored separately from local runs: a draft is transient UI state,
 * never a substitute for a saved run.
 */
export const draftService = {
    DRAFT_STORAGE_KEY,
    DRAFT_MAX_VERSIONS,
    DEFAULT_DRAFT_INTERVAL,

    readAll() {
        try {
            const parsed = JSON.parse(localStorage.getItem(DRAFT_STORAGE_KEY) || "{}");
            return parsed && typeof parsed === "object" ? parsed : {};
        } catch (error) {
            console.error("draftService: draft store is corrupt and has been reset", error);
            return {};
        }
    },

    writeAll(drafts) {
        try {
            localStorage.setItem(DRAFT_STORAGE_KEY, JSON.stringify(drafts ?? {}));
        } catch (error) {
            const isQuota =
                error?.name === "QuotaExceededError" ||
                error?.name === "NS_ERROR_DOM_QUOTA_REACHED" ||
                error?.code === 22 ||
                error?.code === 1014;

            if (isQuota) {
                throw new QuotaError("Browser storage is full, so this draft could not be saved.");
            }

            throw error;
        }
    },

    /**
     * @returns {{versions: Array<object>, savedAt: string|null}|null}
     */
    get(key) {
        return this.readAll()[key] ?? null;
    },

    /** Newest version first. */
    latest(key) {
        const draft = this.get(key);
        return draft?.versions?.[0] ?? null;
    },

    save(key, data) {
        const drafts = this.readAll();
        const existing = drafts[key];
        const version = { data, saved_at: nowIso() };

        const versions = [version, ...(existing?.versions ?? [])].slice(
            0,
            DRAFT_MAX_VERSIONS
        );

        drafts[key] = { versions, saved_at: version.saved_at };
        this.writeAll(drafts);

        return drafts[key];
    },

    restore(key) {
        return this.latest(key);
    },

    /**
     * Drop the draft once its contents have been successfully persisted.
     */
    clear(key) {
        const drafts = this.readAll();

        if (!(key in drafts)) {
            return false;
        }

        delete drafts[key];
        this.writeAll(drafts);

        return true;
    },

    clearAll() {
        this.writeAll({});
    },

    /**
     * Start periodic autosave.
     *
     * @param {string}   key            Draft identifier, usually the plan UUID.
     * @param {Function} collect        Returns the current form data to save.
     * @param {number}   [interval]     Milliseconds between saves.
     * @param {Function} [onError]      Called when a save fails, e.g. on quota.
     * @returns {{stop: Function, saveNow: Function}} handle with a `stop()`.
     */
    startAutosave(key, collect, interval = DEFAULT_DRAFT_INTERVAL, onError = null) {
        const period = Number(interval) > 0 ? Number(interval) : DEFAULT_DRAFT_INTERVAL;

        const saveNow = () => {
            try {
                let data;
                try {
                    data = collect();
                } catch (error) {
                    return null;
                }

                if (!data || typeof data !== "object") {
                    return null;
                }

                return this.save(key, data);
            } catch (error) {
                if (typeof onError === "function") {
                    onError(error);
                }
                return null;
            }
        };

        const timer = setInterval(saveNow, period);

        const onVisibility = () => {
            if (document.visibilityState === "hidden") {
                saveNow();
            }
        };
        document.addEventListener("visibilitychange", onVisibility);

        return {
            saveNow,
            stop() {
                clearInterval(timer);
                document.removeEventListener("visibilitychange", onVisibility);
            },
        };
    },
};

export default { localRunStorage, draftService, QuotaError, STORAGE_KEY, SCHEMA_VERSION };
