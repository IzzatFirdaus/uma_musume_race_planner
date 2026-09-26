/**
 * Alpine stores.
 *
 * Four stores, all with an optional `init()`. `resources/js/app.js` calls each
 * `init()` defensively after `Livewire.start()`:
 *
 *   if (Alpine.store("connection")?.init) { Alpine.store("connection").init(); }
 *
 * so every store must remain safe to initialise twice.
 *
 * Registered names: connection, preferences, toast, keyboard.
 * `components/layout.blade.php` and the x-* Blade components depend on the
 * exact property names below.
 */

import { registerKeyboardShortcuts } from "../keyboard-shortcuts.js";

const PREFERENCES_KEY = "uma_preferences";
const LEGACY_DARK_MODE_KEY = "darkMode";
const DEFAULT_TOAST_DURATION = 5000;
const MAX_VISIBLE_TOASTS = 5;

function readStoredPreferences() {
    try {
        const raw = localStorage.getItem(PREFERENCES_KEY);

        if (raw) {
            const parsed = JSON.parse(raw);

            if (parsed && typeof parsed.darkMode === "boolean") {
                return parsed;
            }
        }

        const legacy = localStorage.getItem(LEGACY_DARK_MODE_KEY);

        if (legacy !== null) {
            return { darkMode: legacy === "enabled" };
        }
    } catch (error) {
        console.error("stores: stored preferences are corrupt", error);
    }

    return {
        darkMode:
            typeof window !== "undefined" &&
            typeof window.matchMedia === "function" &&
            window.matchMedia("(prefers-color-scheme: dark)").matches,
    };
}

export function registerStores(Alpine) {
    /**
     * Connection state.
     *
     * Surfaces browser offline status and failed Livewire requests. The
     * reconnection prompt is only raised for Account runs, because a Local run
     * is written to localStorage and is therefore unaffected by connectivity.
     */
    Alpine.store("connection", {
        offline: typeof navigator !== "undefined" ? !navigator.onLine : false,
        failed: false,
        failedMessage: null,
        reconnectPromptOpen: false,
        bound: false,
        lastActiveElement: null,
        accountRunActive: false,

        init() {
            if (this.bound) {
                return;
            }

            this.bound = true;

            window.addEventListener("online", () => {
                this.offline = false;
            });

            window.addEventListener("offline", () => {
                this.offline = true;
            });
        },

        /**
         * Called when a Livewire request fails. `isAccountRun` is supplied
         * by the caller (or by the `set-account-run` window event) so that
         * Local runs never prompt for reconnection.
         */
        onRequestFailure(status, message) {
                this.failed = true;
                this.failedMessage = message || null;

                if (this.accountRunActive && this.reconnectPromptOpen === false) {
                    this.lastActiveElement =
                        document.activeElement instanceof HTMLElement ? document.activeElement : null;
                    this.reconnectPromptOpen = true;
                }
            },

            clearFailure() {
                this.failed = false;
                this.failedMessage = null;
            },

            /** Re-check the server and clear the failure state. */
            retry() {
                this.clearFailure();
                this.offline = typeof navigator !== "undefined" ? !navigator.onLine : false;

                window.dispatchEvent(new CustomEvent("uma:connection-retry"));
            },

            dismissReconnectPrompt() {
                this.reconnectPromptOpen = false;

                const target = this.lastActiveElement;
                this.lastActiveElement = null;

                if (target) {
                    this.$nextTick?.(() => target.focus({ preventScroll: true }));
                }
            },

        setAccountRunActive(active) {
            this.accountRunActive = Boolean(active);
        },
    });

    /**
     * User preferences.
     *
     * `darkMode` must mirror the `dark` class on <html>, which the inline
     * anti-FOUC script in components/layout.blade.php sets before first paint.
     */
    Alpine.store("preferences", {
        darkMode: false,
        bound: false,

        init() {
            if (this.bound) {
                return;
            }

            this.bound = true;

            this.darkMode = readStoredPreferences().darkMode;
            this.applyToDocument();

            if (typeof window.matchMedia === "function") {
                window
                    .matchMedia("(prefers-color-scheme: dark)")
                    .addEventListener?.("change", (event) => {
                        // Only follow the OS when the user has not chosen explicitly.
                        if (!this.hasExplicitPreference()) {
                            this.set(event.matches, { persist: false });
                        }
                    });
            }
        },

        hasExplicitPreference() {
            try {
                const raw = localStorage.getItem(PREFERENCES_KEY);
                return raw !== null && typeof JSON.parse(raw)?.darkMode === "boolean";
            } catch (error) {
                return false;
            }
        },

        applyToDocument() {
            document.documentElement.classList.toggle("dark", this.darkMode);
        },

        set(value, { persist = true } = {}) {
            this.darkMode = Boolean(value);
            this.applyToDocument();

            if (persist) {
                try {
                    localStorage.setItem(PREFERENCES_KEY, JSON.stringify({ darkMode: this.darkMode }));
                    localStorage.removeItem(LEGACY_DARK_MODE_KEY);
                } catch (error) {
                    console.error("preferences: could not persist theme", error);
                }
            }

            window.dispatchEvent(
                new CustomEvent("dark-mode-changed", { detail: { mode: this.darkMode ? "dark" : "light" } })
            );

            return this.darkMode;
        },

        toggle() {
            return this.set(!this.darkMode);
        },
    });

    /**
     * Toast notifications.
     *
     * Funnels three sources into one list so the x-toast-container component
     * can render them uniformly:
     *   1. window `toast` CustomEvent
     *   2. Livewire `toast` event (positional or named parameters)
     *   3. direct `Alpine.store("toast").add(...)` calls
     */
    Alpine.store("toast", {
        items: [],
        bound: false,

        init() {
            if (this.bound) {
                return;
            }

            this.bound = true;

            window.addEventListener("toast", (event) => {
                const detail = event?.detail ?? {};
                this.add(detail.type ?? "info", detail.message ?? "", detail.duration);
            });

            this.$nextTick?.(() => {
                if (typeof window.Livewire !== "undefined" && window.Livewire.on) {
                    window.Livewire.on("toast", (params) => this.fromLivewire(params));
                }
            });
        },

        /**
         * Livewire dispatches this event in three shapes across the codebase;
         * normalise all of them.
         */
        fromLivewire(params) {
            let payload = params;

            if (Array.isArray(params)) {
                payload = params[0] ?? {};
            }

            if (typeof payload === "string") {
                this.add("info", payload);
                return;
            }

            if (!payload || typeof payload !== "object") {
                return;
            }

            this.add(payload.type ?? "info", payload.message ?? "", payload.duration);
        },

        add(type = "info", message = "", duration = 0) {
            const text = typeof message === "string" ? message.trim() : "";

            if (!text) {
                return null;
            }

            const id = `toast_${Date.now()}_${Math.random().toString(36).slice(2, 8)}`;
            const ttl = Number(duration) > 0 ? Number(duration) : DEFAULT_TOAST_DURATION;
            const item = { id, type: type || "info", message: text, duration: ttl };

            this.items.push(item);

            while (this.items.length > MAX_VISIBLE_TOASTS) {
                this.items.shift();
            }

            const announce = () => window.Livewire?.dispatch?.("toast-added", { id, duration: ttl });

            if (ttl > 0) {
                setTimeout(() => {
                    this.dismiss(id);
                    announce();
                }, ttl);
            }

            return item;
        },

        success(message, duration) {
            return this.add("success", message, duration);
        },

        error(message, duration) {
            return this.add("error", message, duration);
        },

        warning(message, duration) {
            return this.add("warning", message, duration);
        },

        info(message, duration) {
            return this.add("info", message, duration);
        },

        dismiss(id) {
            const before = this.items.length;
            this.items = this.items.filter((item) => item.id !== id);

            return before !== this.items.length;
        },

        clear() {
            this.items = [];
        },
    });

    /**
     * Keyboard shortcuts and the help dialog.
     */
    Alpine.store("keyboard", {
        helpOpen: false,
        shortcuts: [],
        bound: false,
        lastActiveElement: null,

        init() {
            if (this.bound) {
                return;
            }

            this.bound = true;

            const { shortcuts, dispose } = registerKeyboardShortcuts(this);

            this.shortcuts = shortcuts;

            this.dispose = dispose;
        },

        register(definition) {
            this.shortcuts = [...this.shortcuts, definition];
            return this.shortcuts;
        },

        openHelp() {
            this.lastActiveElement =
                document.activeElement instanceof HTMLElement ? document.activeElement : null;
            this.helpOpen = true;
        },

        closeHelp() {
            this.helpOpen = false;

            const target = this.lastActiveElement;
            this.lastActiveElement = null;

            if (target) {
                this.$nextTick?.(() => target.focus({ preventScroll: true }));
            }
        },

        toggleHelp() {
            if (this.helpOpen) {
                this.closeHelp();
            } else {
                this.openHelp();
            }
        },
    });

    return Alpine;
}

export default registerStores;
