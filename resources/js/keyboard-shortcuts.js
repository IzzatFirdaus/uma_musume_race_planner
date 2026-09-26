/**
 * Global keyboard shortcut manager.
 *
 * Registers document-level shortcuts and keeps the `keyboard` store's
 * `shortcuts` list in sync so x-keyboard-shortcuts-help can render itself
 * without a duplicated hard-coded table.
 *
 * Two rules govern every binding:
 *
 *   1. Shortcuts never fire while the user is typing. A target is considered a
 *      text field if it is an input, textarea, select, or contenteditable, or
 *      if it carries role=textbox / role=searchbox.
 *   2. Modifier combinations are matched exactly. Pressing Ctrl+K does not
 *      also trigger a bare `k` binding.
 */

const DEFAULT_SHORTCUTS = [
    {
        id: "help",
        description: "Show keyboard shortcuts",
        keys: ["?"],
        combo: { key: "?" },
    },
    {
        id: "save",
        description: "Save the current career run",
        keys: ["Ctrl", "S"],
        combo: { key: "s", ctrl: true },
        // Handled per-page by the plan editor; declared here only so it appears
        // in the help dialog.
        handled: true,
    },
    {
        id: "close",
        description: "Close the open dialog",
        keys: ["Esc"],
        combo: { key: "escape" },
        handled: true,
    },
    {
        id: "dashboard",
        description: "Go to the dashboard",
        keys: ["Alt", "D"],
        combo: { key: "d", alt: true },
    },
    {
        id: "local-data",
        description: "Manage Local career runs",
        keys: ["Alt", "L"],
        combo: { key: "l", alt: true },
    },
    {
        id: "toggle-theme",
        description: "Toggle dark mode",
        keys: ["Alt", "M"],
        combo: { key: "m", alt: true },
    },
];

const TEXT_INPUT_TYPES = new Set([
    "text",
    "search",
    "email",
    "url",
    "tel",
    "password",
    "number",
    "date",
    "datetime-local",
    "month",
    "time",
    "week",
]);

function isTextEntry(target) {
    if (!(target instanceof HTMLElement)) {
        return false;
    }

    const tag = target.tagName;

    if (tag === "TEXTAREA" || tag === "SELECT") {
        return true;
    }

    if (tag === "INPUT") {
        return TEXT_INPUT_TYPES.has(target.type);
    }

    if (target.isContentEditable) {
        return true;
    }

    const role = target.getAttribute?.("role");

    return role === "textbox" || role === "searchbox";
}

/**
 * @param {object} store The `keyboard` Alpine store, so the handler can open
 *                       and close the help dialog.
 * @returns {{shortcuts: Array<object>, dispose: Function}}
 */
export function registerKeyboardShortcuts(store) {
    const shortcuts = [...DEFAULT_SHORTCUTS];

    function matches(event, combo) {
        const key = event.key?.toLowerCase();

        if (combo.key !== key) {
            return false;
        }

        return Boolean(combo.ctrl) === event.ctrlKey && Boolean(combo.alt) === event.altKey;
    }

    function run(definition) {
        switch (definition.id) {
            case "help":
                store.toggleHelp();
                break;
            case "close":
                if (store.helpOpen) {
                    store.closeHelp();
                }
                break;
            case "dashboard":
                window.location.href = "/dashboard";
                break;
            case "local-data":
                window.location.href = "/local-data";
                break;
            case "toggle-theme":
                window.dispatchEvent(new CustomEvent("uma:toggle-theme"));
                break;
            default:
                break;
        }
    }

    function onKeydown(event) {
        if (event.defaultPrevented) {
            return;
        }

        // The help dialog must stay dismissible regardless of focus.
        if (event.key === "Escape" && store.helpOpen) {
            store.closeHelp();
            return;
        }

        if (isTextEntry(event.target)) {
            return;
        }

        const definition = shortcuts.find((item) => matches(event, item.combo));

        if (!definition || definition.handled) {
            return;
        }

        event.preventDefault();
        run(definition);
    }

    document.addEventListener("keydown", onKeydown);

    return {
        shortcuts,
        dispose() {
            document.removeEventListener("keydown", onKeydown);
        },
    };
}

export default registerKeyboardShortcuts;
