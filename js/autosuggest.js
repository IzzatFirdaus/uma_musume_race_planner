// js/autosuggest.js
// Autosuggest utility for Uma Musume Race Planner
// Provides live database-backed suggestions for input fields (name, race_name, skill_name, goal, etc.)

// Store active suggestion lists for cleanup
const activeSuggestLists = new Map();

/**
 * Helper: Escape and sanitize text for safe HTML display.
 * Typically, textContent is enough, but this can be used for more complex cases.
 */
function sanitizeText(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

/**
 * Attach autosuggest functionality to an input field.
 * @param {HTMLInputElement} input The input element to attach autosuggest to.
 * @param {string} field The database field name to query (e.g., 'name', 'race_name', 'skill_name', 'goal').
 * @param {function(Object): void} [onSelectCallback] Optional callback function when a suggestion is selected.
 *        For skill_name, this will receive the full skill object.
 */
function attachAutosuggest(input, field, onSelectCallback = null) {
    let currentFocus = -1;

    // --- Security: Only allow whitelisted fields ---
    const allowedFields = ['name', 'race_name', 'skill_name', 'goal'];
    if (!allowedFields.includes(field)) {
        console.error('Autosuggest: Invalid field parameter provided:', field);
        return;
    }

    // --- Accessibility: Set ARIA attributes on the input ---
    input.setAttribute('aria-autocomplete', 'list');
    input.setAttribute('aria-controls', input.id + 'autocomplete-list');

    // --- Remove any previously open suggestion lists for this input ---
    closeAllLists(input);

    input.addEventListener("input", function () {
        const val = this.value;
        closeAllLists(this);

        if (!val) return false;
        currentFocus = -1;

        // Create suggestion list container
        const a = document.createElement("DIV");
        a.setAttribute("id", this.id + "autocomplete-list");
        a.setAttribute("class", "autocomplete-items card");
        a.setAttribute('role', 'listbox');
        a.setAttribute('aria-label', `Suggestions for ${input.id}`);
        this.parentNode.appendChild(a);
        activeSuggestLists.set(this, a);

        // Show loading indicator
        const loadingDiv = document.createElement("DIV");
        loadingDiv.textContent = "Loading...";
        loadingDiv.style.padding = "10px";
        loadingDiv.style.textAlign = "center";
        a.appendChild(loadingDiv);

        // Fetch suggestions from backend
        fetch(`get_autosuggest.php?field=${field}&query=${encodeURIComponent(val)}`)
            .then(response => {
                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                return response.text();
            })
            .then(text => {
                a.innerHTML = ''; // Remove loading
                try {
                    const data = JSON.parse(text);
                    if (data.success && data.suggestions) {
                        if (data.suggestions.length === 0) {
                            const noResults = document.createElement("DIV");
                            noResults.textContent = "No results found.";
                            noResults.style.padding = "10px";
                            noResults.style.color = "var(--color-text-muted-light)";
                            a.appendChild(noResults);
                            return;
                        }

                        data.suggestions.forEach(item => {
                            const valToDisplay = field === 'skill_name' ? item.skill_name : item;
                            const b = document.createElement("DIV");
                            b.setAttribute('role', 'option');
                            b.setAttribute('aria-selected', 'false');

                            // Display strong for matched part, safe from XSS
                            const strong = document.createElement('strong');
                            strong.textContent = valToDisplay.substr(0, val.length);
                            b.appendChild(strong);
                            b.appendChild(document.createTextNode(valToDisplay.substr(val.length)));

                            // Hidden input for value
                            const hiddenInput = document.createElement('input');
                            hiddenInput.type = 'hidden';
                            hiddenInput.value = valToDisplay;
                            b.appendChild(hiddenInput);

                            // Click handler
                            b.addEventListener("click", function () {
                                input.value = this.getElementsByTagName("input")[0].value;
                                if (onSelectCallback) {
                                    const selectedItem = field === 'skill_name' ? item : valToDisplay;
                                    onSelectCallback(selectedItem);
                                }
                                closeAllLists();
                            });
                            a.appendChild(b);
                        });
                    } else {
                        // Handle errors from backend
                        console.error("Autosuggest server error:", data.error || "Unknown error from server.");
                        const errorDiv = document.createElement("DIV");
                        errorDiv.textContent = data.error || "Error fetching suggestions.";
                        errorDiv.style.padding = "10px";
                        errorDiv.style.color = "red";
                        a.appendChild(errorDiv);
                    }
                } catch (jsonParseError) {
                    console.error("Autosuggest JSON parse error:", jsonParseError, "Raw response:", text);
                    const errorDiv = document.createElement("DIV");
                    errorDiv.textContent = "Error processing data.";
                    errorDiv.style.padding = "10px";
                    errorDiv.style.color = "red";
                    a.appendChild(errorDiv);
                }
            })
            .catch(error => {
                a.innerHTML = '';
                console.error("Autosuggest fetch error:", error);
                const errorDiv = document.createElement("DIV");
                errorDiv.textContent = "Could not fetch suggestions.";
                errorDiv.style.padding = "10px";
                errorDiv.style.color = "red";
                a.appendChild(errorDiv);
            });
    });

    input.addEventListener("keydown", function (e) {
        let x = document.getElementById(this.id + "autocomplete-list");
        if (x) x = x.getElementsByTagName("div");
        // Accessibility: Modern JS key handling
        if (e.key === 'ArrowDown') {
            currentFocus++;
            addActive(x);
            e.preventDefault();
        } else if (e.key === 'ArrowUp') {
            currentFocus--;
            addActive(x);
            e.preventDefault();
        } else if (e.key === 'Enter') {
            e.preventDefault();
            if (currentFocus > -1 && x && x[currentFocus]) {
                x[currentFocus].click();
            }
        }
    });

    function addActive(x) {
        if (!x) return false;
        removeActive(x);
        if (currentFocus >= x.length) currentFocus = 0;
        if (currentFocus < 0) currentFocus = x.length - 1;
        x[currentFocus].classList.add("autocomplete-active");
        x[currentFocus].setAttribute('aria-selected', 'true');
        x[currentFocus].focus();
        x[currentFocus].parentNode.scrollTop = x[currentFocus].offsetTop - x[currentFocus].parentNode.offsetTop;
    }

    function removeActive(x) {
        for (let i = 0; i < x.length; i++) {
            x[i].classList.remove("autocomplete-active");
            x[i].setAttribute('aria-selected', 'false');
        }
    }

    function closeAllLists(elmnt = null) {
        activeSuggestLists.forEach((listEl, inputEl) => {
            if (elmnt !== inputEl) {
                if (listEl.parentNode) listEl.parentNode.removeChild(listEl);
                activeSuggestLists.delete(inputEl);
            }
        });
    }

    // When user clicks outside, close suggestion lists
    document.addEventListener("click", function (e) {
        activeSuggestLists.forEach((listEl, inputEl) => {
            if (e.target !== inputEl && e.target !== listEl && !listEl.contains(e.target)) {
                closeAllLists(inputEl);
            }
        });
    });
}

// This file can be included in your main app. Attach autosuggest like:
// attachAutosuggest(document.getElementById('modalName'), 'name');
// attachAutosuggest(document.getElementById('modalRaceName'), 'race_name');
// attachAutosuggest(document.getElementById('modalGoal'), 'goal');
// attachAutosuggest(document.getElementById('skillNameInput'), 'skill_name', function(skillObj){ ... });