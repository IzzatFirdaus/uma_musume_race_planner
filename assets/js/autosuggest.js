// Autosuggest utility for Uma Musume Race Planner
// Provides live database-backed suggestions for fields like name, race_name, skill_name, goal.
// General Best Practices applied:
// - Security: whitelisted fields only, sanitized requests, avoids leaking sensitive info
// - Accessibility: WAI-ARIA listbox pattern, keyboard navigation, live-region announcements
// - Performance: input debouncing, request cancellation (AbortController), in-memory result caching
// - Robustness: graceful error handling, cleanup of DOM lists, defensive checks

/* eslint-env browser */
(() => {
    'use strict';

  // Public API exposed on window for legacy integration
  // window.attachAutosuggest will be defined at the end of this IIFE.

  /** @typedef {{ skill_name: string, description?: string, stat_type?: string, tag?: string }} SkillRef */

    const ALLOWED_FIELDS = ['name', 'race_name', 'skill_name', 'goal'];
    const DEBOUNCE_MS = 200;

  // WeakMaps to keep per-input state without leaking memory
    const listForInput = new WeakMap();             // input -> listbox element
    const controllerForInput = new WeakMap();       // input -> AbortController
    const debounceTimerForInput = new WeakMap();    // input -> debounce timer
    const activeIndexForInput = new WeakMap();      // input -> current active option index

  // Simple in-memory cache to avoid repeated network calls. Keyed by `${field}|${query}`
    const cache = new Map();

  // Localized strings (simplified). In a larger app, wire this into your i18n layer.
    const STR = {
        loading: 'Loading…',
        noResults: 'No results found.',
        fetchError: 'Could not fetch suggestions.',
    };

  // Utility short-hands
    const on = (el, evt, handler, opts) => el && el.addEventListener(evt, handler, opts);

  /**
   * Build API URL from a field and query.
   * Uses window.APP_CONFIG.API_BASE or window.APP_API_BASE for backward compatibility.
   * @param {string} field
   * @param {string} query
   */
    function buildApiUrl(field, query)
    {
        const base = (window.APP_CONFIG && window.APP_CONFIG.API_BASE) || window.APP_API_BASE || '/api';
  const url = `${base.replace(/\/+$/, '')}/autosuggest.php`;
        const params = new URLSearchParams({
            action: 'get',
            field,
            query,
            limit: '10',
        });
  return `${url}?${params.toString()}`;
    }

  /**
   * Remove an existing suggestion list for a given input element.
   * @param {HTMLInputElement} input
   */
    function removeList(input)
    {
        const list = listForInput.get(input);
        if (list && list.parentNode) {
            list.parentNode.removeChild(list);
        }
        listForInput.delete(input);
        activeIndexForInput.delete(input);
        input.setAttribute('aria-expanded', 'false');
        input.removeAttribute('aria-activedescendant');

      // abort any pending requests
        const ctrl = controllerForInput.get(input);
        if (ctrl) {
            try {
                ctrl.abort(); } catch (_) {
                }
                controllerForInput.delete(input);
        }

      // clear debounce timer
        const t = debounceTimerForInput.get(input);
        if (t) {
            clearTimeout(t);
            debounceTimerForInput.delete(input);
        }
    }

  /**
   * Create a live region status element (polite) to announce changes to screen readers.
   * Caller should append it to the list container or an accessible container.
   */
    function createLiveRegion()
    {
        const sr = document.createElement('div');
        sr.className = 'visually-hidden'; // rely on existing helper CSS
        sr.setAttribute('role', 'status');
        sr.setAttribute('aria-live', 'polite');
        sr.setAttribute('aria-atomic', 'true');
        return sr;
    }

  /**
   * Highlight the matched prefix of a suggestion value.
   * @param {string} value
   * @param {string} query
   * @returns {DocumentFragment}
   */
    function renderHighlighted(value, query)
    {
        const frag = document.createDocumentFragment();
        const strVal = String(value);
        const strQuery = String(query);
        const match = strVal.toLowerCase().startsWith(strQuery.toLowerCase());
        if (match) {
            const strong = document.createElement('strong');
            strong.textContent = strVal.slice(0, strQuery.length);
            frag.appendChild(strong);
            frag.appendChild(document.createTextNode(strVal.slice(strQuery.length)));
        } else {
            frag.appendChild(document.createTextNode(strVal));
        }
        return frag;
    }

  /**
   * Render suggestions in a listbox.
   * @param {HTMLInputElement} input
   * @param {Array<string|SkillRef>} suggestions
   * @param {string} field
   * @param {string} query
   * @param {(val: any) => void|null} onSelectCallback
   */
    function renderList(input, suggestions, field, query, onSelectCallback)
    {
      // Remove any existing list
        removeList(input);

      // Create container
        const list = document.createElement('div');
        list.className = 'autocomplete-items card';
        list.id = `${input.id || `input - ${Math.random().toString(36).slice(2)}`} - autocomplete - list`;
        list.setAttribute('role', 'listbox');
        list.setAttribute('aria-label', `Suggestions for ${input.id || 'input'}`);

      // Live region for screen readers
        const live = createLiveRegion();
        list.appendChild(live);
      // Announce result count
        live.textContent = `${suggestions.length} result${suggestions.length === 1 ? '' : 's'} available.`;

      // Append to DOM
  input.parentNode?.appendChild(list);
        listForInput.set(input, list);

      // Update input ARIA
        input.setAttribute('aria-controls', list.id);
        input.setAttribute('aria-expanded', 'true');

      // Build options
        suggestions.forEach((item, idx) => {
            const valToDisplay = (field === 'skill_name' && item && typeof item === 'object') ? item.skill_name : item;
            const option = document.createElement('div');
            option.setAttribute('role', 'option');
            option.className = 'autocomplete-option';
            const optionId = `${list.id} - opt - ${idx}`;
            option.id = optionId;
            option.setAttribute('aria-selected', 'false');
            option.tabIndex = -1;

            option.appendChild(renderHighlighted(String(valToDisplay), query));

          // Selection handlers: use pointerdown to select before input blur fires
            const commitSelection = () => {
                const finalValue = String(valToDisplay);
                input.value = finalValue;
                if (onSelectCallback) {
                    const selected = (field === 'skill_name') ? item : finalValue;
                    try {
                        onSelectCallback(selected); } catch (_) {
                                      /* swallow */ }
                }
                removeList(input);
                input.focus();
            };

            option.addEventListener('pointerdown', commitSelection);
            option.addEventListener('click', commitSelection);

            list.appendChild(option);
        });

      // Reset active index
  activeIndexForInput.set(input, -1);
    }

  /**
   * Update the active option styling and aria-activedescendant mapping.
   * @param {HTMLInputElement} input
   * @param {number} nextIndex
   */
    function setActiveIndex(input, nextIndex)
    {
        const list = listForInput.get(input);
        if (!list) {
            return;
        }

        const options = Array.from(list.querySelectorAll('[role="option"]'));
        if (!options.length) {
            return;
        }

      // Normalize index
        let idx = nextIndex;
        if (idx >= options.length) {
            idx = 0;
        }
        if (idx < 0) {
            idx = options.length - 1;
        }

      // Clear previous
        options.forEach(opt => {
            opt.classList.remove('autocomplete-active');
            opt.setAttribute('aria-selected', 'false');
        });

        const active = options[idx];
        active.classList.add('autocomplete-active');
        active.setAttribute('aria-selected', 'true');
        input.setAttribute('aria-activedescendant', active.id);

      // Ensure visible
        const parent = active.parentNode;
        if (parent && parent instanceof HTMLElement) {
            parent.scrollTop = active.offsetTop - parent.offsetTop;
        }

        activeIndexForInput.set(input, idx);
    }

  /**
   * Fetch suggestions with debounce and cancellation.
   * @param {HTMLInputElement} input
   * @param {string} field
   * @param {string} query
   * @param {(val: any) => void|null} onSelectCallback
   */
    function doFetch(input, field, query, onSelectCallback)
    {
      // If cached, render immediately
        const cacheKey = `${field} | ${query}`;
        if (cache.has(cacheKey)) {
            renderList(input, cache.get(cacheKey), field, query, onSelectCallback);
            return;
        }

      // Show loading list
        removeList(input);
        const loadingList = document.createElement('div');
        loadingList.className = 'autocomplete-items card';
        loadingList.id = `${input.id || `input - ${Math.random().toString(36).slice(2)}`} - autocomplete - list`;
        loadingList.setAttribute('role', 'listbox');
        const live = createLiveRegion();
        live.textContent = STR.loading;
        loadingList.appendChild(live);
        const loadingRow = document.createElement('div');
        loadingRow.textContent = STR.loading;
        loadingRow.style.padding = '10px';
        loadingRow.style.textAlign = 'center';
        loadingList.appendChild(loadingRow);
  input.parentNode?.appendChild(loadingList);
        listForInput.set(input, loadingList);
        input.setAttribute('aria-controls', loadingList.id);
        input.setAttribute('aria-expanded', 'true');

      // Abort any previous fetch for this input
        const prev = controllerForInput.get(input);
        if (prev) {
            try {
                prev.abort(); } catch (_) {
                }
        }
        const ctrl = new AbortController();
        controllerForInput.set(input, ctrl);

        const url = buildApiUrl(field, query);

  fetch(url, { signal: ctrl.signal, headers: { 'Accept': 'application/json' } })
        .then((response) => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }
            return response.json();
        })
        .then((data) => {
      if (!data || data.success !== true) {
        throw new Error(data?.error || 'Unknown error');
            }
            const suggestions = Array.isArray(data.suggestions) ? data.suggestions : [];
            cache.set(cacheKey, suggestions);
            renderList(input, suggestions, field, query, onSelectCallback);
        })
        .catch((err) => {
            if (err.name === 'AbortError') {
                return; // cancelled
            }
          // Render error message accessibly
            const list = listForInput.get(input);
            if (list) {
                list.innerHTML = '';
                const liveErr = createLiveRegion();
                liveErr.textContent = STR.fetchError;
                list.appendChild(liveErr);
                const row = document.createElement('div');
                row.textContent = STR.fetchError;
                row.style.padding = '10px';
                row.style.color = 'red';
                list.appendChild(row);
            } else {
              // Fallback: console error
                console.error('Autosuggest fetch error:', err);
            }
        });
    }

  /**
   * Attach autosuggest behavior to an input.
   * @param {HTMLInputElement} input The input element to enhance.
   * @param {string} field Allowed values: 'name' | 'race_name' | 'skill_name' | 'goal'
   * @param {(val: any) => void} [onSelectCallback] Optional callback when a suggestion is selected.
   */
    function attachAutosuggest(input, field, onSelectCallback = null)
    {
        if (!(input instanceof HTMLInputElement)) {
            console.error('attachAutosuggest: input must be an HTMLInputElement');
            return;
        }
        if (!ALLOWED_FIELDS.includes(field)) {
            console.error('attachAutosuggest: Invalid field parameter:', field);
            return;
        }

        // ARIA setup
        input.setAttribute('autocomplete', 'off');
        input.setAttribute('aria-autocomplete', 'list');
        input.setAttribute('aria-haspopup', 'listbox');
        input.setAttribute('aria-expanded', 'false');

        // Cleanup any existing
        removeList(input);

        // Debounced input handler
        const onInput = () => {
            const val = input.value.trim();
            removeList(input); // close current list for fresh render
            if (!val) {
                return;
            }

            const timer = setTimeout(() => {
                doFetch(input, field, val, onSelectCallback);
            }, DEBOUNCE_MS);
          debounceTimerForInput.set(input, timer);
        };

        on(input, 'input', onInput);

        // Keyboard navigation
        on(input, 'keydown', (e) => {
            const list = listForInput.get(input);
            const options = list ? Array.from(list.querySelectorAll('[role="option"]')) : [];

            if (e.key === 'Escape') {
                e.preventDefault();
                removeList(input);
                return;
            }

            if (!options.length) {
                return;
            }

            let idx = activeIndexForInput.get(input) ?? -1;

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                setActiveIndex(input, idx + 1);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                setActiveIndex(input, idx - 1);
            } else if (e.key === 'Enter') {
                e.preventDefault();
                idx = activeIndexForInput.get(input) ?? -1;
                if (idx >= 0 && idx < options.length) {
                    options[idx].dispatchEvent(new Event('click', { bubbles: true }));
                }
            }
        });

      // Clicking outside closes all
        on(document, 'click', (e) => {
              const list = listForInput.get(input);
            if (!list) {
                return;
            }
            if (e.target !== input && e.target !== list && !list.contains(e.target)) {
                  removeList(input);
            }
        });

      // Blur closes the list after a short delay (allows click/pointerdown to run)
        on(input, 'blur', () => setTimeout(() => removeList(input), 150));
    }

  // Expose for global usage
    window.attachAutosuggest = attachAutosuggest;
})();