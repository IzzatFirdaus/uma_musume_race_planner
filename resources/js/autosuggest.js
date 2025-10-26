/**
 * js/autosuggest.js
 *
 * Provides a reusable autosuggest/autocomplete feature for input fields.
 * Enhanced with debouncing, ARIA accessibility, and improved UX.
 * This file is now an ES6 module.
 */

import { escapeHtml } from "./utils.js";

const activeSuggestLists = new Map();
const activeControllers = new Map();
const requestTokens = new Map();
const queryCache = new Map();
const DEBOUNCE_DELAY = 250;
const MIN_QUERY_LENGTH = 2;
const CACHE_EXPIRY = 2 * 60 * 1000; // 2 minutes
const MAX_CACHE_SIZE = 50;

/**
 * Creates a debounced version of a function
 * @param {Function} func The function to debounce
 * @param {number} delay The delay in milliseconds
 * @returns {Function} The debounced function
 */
function debounce(func, delay) {
    let timeoutId;
    return function (...args) {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => func.apply(this, args), delay);
    };
}

/**
 * Checks if cache entry is still valid
 * @param {Object} entry Cache entry with timestamp
 * @returns {boolean} Whether the entry is still valid
 */
function isCacheValid(entry) {
    return Date.now() - entry.timestamp < CACHE_EXPIRY;
}

/**
 * Gets cached suggestions if available and valid
 * @param {string} field The field name
 * @param {string} query The query string
 * @returns {Array|null} Cached suggestions or null
 */
function getCachedSuggestions(field, query) {
    const cacheKey = `${field}:${query.toLowerCase()}`;
    const cached = queryCache.get(cacheKey);

    if (cached && isCacheValid(cached)) {
        return cached.suggestions;
    }

    if (cached && !isCacheValid(cached)) {
        queryCache.delete(cacheKey);
    }

    return null;
}

/**
 * Caches suggestions with timestamp
 * @param {string} field The field name
 * @param {string} query The query string
 * @param {Array} suggestions The suggestions to cache
 */
function cacheSuggestions(field, query, suggestions) {
    const cacheKey = `${field}:${query.toLowerCase()}`;

    // Remove oldest entries if cache is full
    if (queryCache.size >= MAX_CACHE_SIZE) {
        const oldestKey = queryCache.keys().next().value;
        queryCache.delete(oldestKey);
    }

    queryCache.set(cacheKey, {
        suggestions,
        timestamp: Date.now(),
    });
}

/**
 * Checks if click target is outside the autosuggest elements
 * @param {Event} event The click event
 * @param {HTMLElement} input The input element
 * @param {HTMLElement} list The suggestions list element
 * @returns {boolean} Whether the click is outside
 */
function isOutsideClick(event, input, list) {
    return !input.contains(event.target) && !list.contains(event.target);
}

/**
 * Generates a unique ID for suggestion items
 * @param {string} inputId The input element ID
 * @param {number} index The suggestion index
 * @returns {string} Unique ID for the suggestion
 */
function getSuggestionId(inputId, index) {
    return `${inputId}-suggestion-${index}`;
}

/**
 * Attaches autosuggest functionality to a given input element.
 * @param {HTMLInputElement} input The input field to attach the functionality to.
 * @param {string} field The database field to search against (e.g., 'skill_name').
 * @param {function|null} onSelectCallback A callback function to run when a suggestion is selected.
 */
export function attachAutosuggest(input, field, onSelectCallback = null) {
    if (!input) {
        return; // Element not on this page; no-op
    }

    const allowedFields = ["name", "race_name", "skill_name", "goal"];
    if (!allowedFields.includes(field)) {
        console.error(
            "Autosuggest Error: Invalid field parameter provided:",
            field,
        );
        return;
    }

    let currentFocus = -1;
    let lastQuery = "";

    // Initialize request token for this input
    if (!requestTokens.has(input)) {
        requestTokens.set(input, 0);
    }

    // Setup ARIA attributes
    setupAriaAttributes(input);

    // Create debounced search function
    const debouncedSearch = debounce((query) => {
        if (query !== lastQuery) return; // Query has changed, ignore
        const currentToken = requestTokens.get(input) + 1;
        requestTokens.set(input, currentToken);
        performSearch(input, field, query, currentToken, onSelectCallback);
    }, DEBOUNCE_DELAY);

    // Input event listener with debouncing and caching
    input.addEventListener("input", function (e) {
        const val = this.value.trim();
        lastQuery = val;
        currentFocus = -1;

        // Close existing list
        closeList(this);

        if (!val || val.length < MIN_QUERY_LENGTH) {
            updateAriaExpanded(this, false);
            return;
        }

        // Check cache first
        const cached = getCachedSuggestions(field, val);
        if (cached) {
            renderSuggestions(this, cached, field, onSelectCallback);
            return;
        }

        // Show loading state
        showLoadingState(this);

        // Debounced search
        debouncedSearch(val);
    });

    // Keyboard navigation
    input.addEventListener("keydown", function (e) {
        const list = getActiveList(this);
        if (!list) return;

        const items = list.querySelectorAll('[role="option"]');
        if (items.length === 0) return;

        switch (e.key) {
            case "ArrowDown":
                e.preventDefault();
                currentFocus = Math.min(currentFocus + 1, items.length - 1);
                updateActiveDescendant(this, items, currentFocus);
                break;
            case "ArrowUp":
                e.preventDefault();
                currentFocus = Math.max(currentFocus - 1, -1);
                updateActiveDescendant(this, items, currentFocus);
                break;
            case "Enter":
                e.preventDefault();
                if (currentFocus >= 0 && items[currentFocus]) {
                    items[currentFocus].click();
                }
                break;
            case "Escape":
                e.preventDefault();
                closeList(this);
                break;
            case "Tab":
                // Allow natural tab behavior but close list
                closeList(this);
                break;
        }
    });

    // Focus event - don't reopen on focus, only on input
    input.addEventListener("focus", function () {
        // Keep existing behavior - don't auto-open on focus
    });

    // Setup global click handler for this input (with proper outside-click detection)
    const clickHandler = (e) => {
        const list = getActiveList(input);
        if (list && isOutsideClick(e, input, list)) {
            closeList(input);
        }
    };

    document.addEventListener("click", clickHandler);

    // Store cleanup function for potential future use
    input._autosuggestCleanup = () => {
        document.removeEventListener("click", clickHandler);
        closeList(input);
        activeControllers.delete(input);
        requestTokens.delete(input);
    };
}

// Public API: allow other scripts to close all autosuggest lists (e.g., before submitting forms)
export function closeAllAutosuggest() {
    closeAllLists();
}

/**
 * Setup ARIA attributes for accessibility
 * @param {HTMLInputElement} input The input element
 */
function setupAriaAttributes(input) {
    input.setAttribute("role", "combobox");
    input.setAttribute("aria-autocomplete", "list");
    input.setAttribute("aria-expanded", "false");
    input.setAttribute("autocomplete", "off");
}

/**
 * Update aria-expanded attribute
 * @param {HTMLInputElement} input The input element
 * @param {boolean} expanded Whether the list is expanded
 */
function updateAriaExpanded(input, expanded) {
    input.setAttribute("aria-expanded", expanded.toString());
}

/**
 * Update aria-activedescendant for keyboard navigation
 * @param {HTMLInputElement} input The input element
 * @param {NodeList} items The suggestion items
 * @param {number} activeIndex The active item index
 */
function updateActiveDescendant(input, items, activeIndex) {
    // Remove active class from all items
    items.forEach((item) => item.classList.remove("active"));

    if (activeIndex >= 0 && items[activeIndex]) {
        items[activeIndex].classList.add("active");
        input.setAttribute("aria-activedescendant", items[activeIndex].id);
    } else {
        input.removeAttribute("aria-activedescendant");
    }
}

/**
 * Get the active suggestions list for an input
 * @param {HTMLInputElement} input The input element
 * @returns {HTMLElement|null} The active list element
 */
function getActiveList(input) {
    return activeSuggestLists.get(input);
}

/**
 * Show loading state while fetching suggestions
 * @param {HTMLInputElement} input The input element
 */
function showLoadingState(input) {
    const suggestionsContainer = createSuggestionsContainer(input);
    suggestionsContainer.innerHTML = `
        <div class="list-group-item d-flex align-items-center">
            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
            Loading suggestions...
        </div>
    `;
    updateAriaExpanded(input, true);
}

/**
 * Create suggestions container element
 * @param {HTMLInputElement} input The input element
 * @returns {HTMLElement} The container element
 */
function createSuggestionsContainer(input) {
    let container = activeSuggestLists.get(input);

    if (!container) {
        container = document.createElement("div");
        container.setAttribute("id", input.id + "-autocomplete-list");
        container.setAttribute(
            "class",
            "autocomplete-items list-group position-absolute",
        );
        container.setAttribute("role", "listbox");
        container.style.zIndex = "1050";
        container.style.maxHeight = "300px";
        container.style.overflowY = "auto";
        container.style.width = "100%";

        input.parentNode.appendChild(container);
        activeSuggestLists.set(input, container);

        // Link input to listbox
        input.setAttribute("aria-controls", container.id);
    }

    return container;
}

/**
 * Perform the actual search request
 * @param {HTMLInputElement} input The input element
 * @param {string} field The field to search
 * @param {string} query The search query
 * @param {number} token Request token to prevent stale responses
 * @param {Function|null} onSelectCallback The selection callback
 */
function performSearch(input, field, query, token, onSelectCallback) {
    // Abort previous request
    const existingController = activeControllers.get(input);
    if (existingController) {
        existingController.abort();
    }

    // Create new AbortController
    const controller = new AbortController();
    activeControllers.set(input, controller);

    fetch(
        `/api/v1/autosuggest?field=${field}&query=${encodeURIComponent(query)}`,
        {
            signal: controller.signal,
        },
    )
        .then((response) => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then((data) => {
            // Check if this is still the latest request
            const currentRequestToken = requestTokens.get(input) || 0;
            if (token !== currentRequestToken || input.value.trim() !== query) {
                return; // Ignore stale response
            }

            if (data.success && data.suggestions) {
                // Cache the results
                cacheSuggestions(field, query, data.suggestions);

                // Render suggestions
                renderSuggestions(
                    input,
                    data.suggestions,
                    field,
                    onSelectCallback,
                );
            } else {
                throw new Error(data.error || "Unknown server error.");
            }
        })
        .catch((error) => {
            if (error.name === "AbortError") {
                return; // Request was aborted, ignore
            }

            console.error("Autosuggest fetch error:", error);
            renderErrorState(input);
        })
        .finally(() => {
            // Clean up controller
            if (activeControllers.get(input) === controller) {
                activeControllers.delete(input);
            }
        });
}

/**
 * Render suggestions in the dropdown
 * @param {HTMLInputElement} input The input element
 * @param {Array} suggestions The suggestions array
 * @param {string} field The field type
 * @param {Function|null} onSelectCallback The selection callback
 */
function renderSuggestions(input, suggestions, field, onSelectCallback) {
    const container = createSuggestionsContainer(input);

    if (suggestions.length === 0) {
        container.innerHTML = `
            <div class="list-group-item text-muted">
                <i class="bi bi-search me-2"></i>No results found
            </div>
        `;
        updateAriaExpanded(input, true);
        return;
    }

    // Build suggestions fragment
    const fragment = document.createDocumentFragment();

    suggestions.forEach((item, index) => {
        const suggestionElement = createSuggestionElement(
            item,
            field,
            input.id,
            index,
        );

        suggestionElement.addEventListener("click", function (event) {
            event.preventDefault();
            event.stopPropagation();

            const valueToSet = field === "skill_name" ? item.skill_name : item;
            input.value = valueToSet;

            if (onSelectCallback) {
                onSelectCallback(item);
            }

            closeList(input);
        });

        fragment.appendChild(suggestionElement);
    });

    // Clear container and append all suggestions at once
    container.innerHTML = "";
    container.appendChild(fragment);
    updateAriaExpanded(input, true);
}

/**
 * Create a suggestion element
 * @param {string|Object} item The suggestion item
 * @param {string} field The field type
 * @param {string} inputId The input element ID
 * @param {number} index The suggestion index
 * @returns {HTMLElement} The suggestion element
 */
function createSuggestionElement(item, field, inputId, index) {
    const element = document.createElement("a");
    element.className = "list-group-item list-group-item-action";
    element.href = "#";
    element.setAttribute("role", "option");
    element.id = getSuggestionId(inputId, index);

    if (field === "skill_name" && typeof item === "object") {
        // Rich skill display
        element.innerHTML = `
            <div class="d-flex justify-content-between align-items-start">
                <div class="flex-grow-1">
                    <div class="fw-bold">${escapeHtml(item.skill_name || "")}</div>
                    ${item.description ? `<small class="text-muted">${escapeHtml(item.description)}</small>` : ""}
                </div>
                ${item.tag ? `<span class="badge bg-secondary ms-2">${escapeHtml(item.tag)}</span>` : ""}
            </div>
        `;
    } else {
        // Simple text display
        const displayValue = field === "skill_name" ? item.skill_name : item;
        element.textContent = displayValue || "";
    }

    return element;
}

/**
 * Render error state
 * @param {HTMLInputElement} input The input element
 */
function renderErrorState(input) {
    const container = createSuggestionsContainer(input);
    container.innerHTML = `
        <div class="list-group-item list-group-item-danger">
            <i class="bi bi-exclamation-triangle me-2"></i>Could not fetch suggestions
        </div>
    `;
    updateAriaExpanded(input, true);
}

/**
 * Close the suggestions list for an input
 * @param {HTMLInputElement} input The input element
 */
function closeList(input) {
    const list = activeSuggestLists.get(input);
    if (list && list.parentNode) {
        list.parentNode.removeChild(list);
    }
    activeSuggestLists.delete(input);
    updateAriaExpanded(input, false);
    input.removeAttribute("aria-activedescendant");
}

/**
 * Close all suggestion lists
 */
function closeAllLists() {
    activeSuggestLists.forEach((listEl, input) => {
        closeList(input);
    });
}
