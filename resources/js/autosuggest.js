/**
 * js/autosuggest.js
 *
 * Provides a reusable autosuggest/autocomplete feature for input fields.
 * This file is now an ES6 module.
 */

const activeSuggestLists = new Map();

/**
 * Attaches autosuggest functionality to a given input element.
 * @param {HTMLInputElement} input The input field to attach the functionality to.
 * @param {string} field The database field to search against (e.g., 'skill_name').
 * @param {function|null} onSelectCallback A callback function to run when a suggestion is selected.
 */
export function attachAutosuggest(input, field, onSelectCallback = null) {
    let currentFocus = -1;
    const allowedFields = ['name', 'race_name', 'skill_name', 'goal'];
    if (!allowedFields.includes(field)) {
        console.error('Autosuggest Error: Invalid field parameter provided:', field);
        return;
    }

    input.addEventListener("input", function(e) {
        const val = this.value;
        closeAllLists();

        if (!val) {
            return false;
        }

        currentFocus = -1;
        const suggestionsContainer = document.createElement("DIV");
        suggestionsContainer.setAttribute("id", this.id + "autocomplete-list");
        suggestionsContainer.setAttribute("class", "autocomplete-items list-group");
        this.parentNode.appendChild(suggestionsContainer);
        activeSuggestLists.set(this, suggestionsContainer);

        // --- UPDATED: Fetch from the new Laravel API endpoint ---
        fetch(`/api/v1/autosuggest?field=${field}&query=${encodeURIComponent(val)}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                suggestionsContainer.innerHTML = '';
                if (data.success && data.suggestions) {
                    if (data.suggestions.length === 0) {
                        const noResults = document.createElement("div");
                        noResults.className = "list-group-item disabled";
                        noResults.textContent = "No results found.";
                        suggestionsContainer.appendChild(noResults);
                        return;
                    }

                    data.suggestions.forEach(item => {
                        const valToDisplay = field === 'skill_name' ? item.skill_name : item;
                        const suggestionDiv = document.createElement("a");
                        suggestionDiv.className = "list-group-item list-group-item-action";
                        suggestionDiv.href = '#';
                        suggestionDiv.textContent = valToDisplay;

                        suggestionDiv.addEventListener("click", function(event) {
                            event.preventDefault();
                            input.value = valToDisplay;
                            if (onSelectCallback) {
                                onSelectCallback(item);
                            }
                            closeAllLists();
                        });
                        suggestionsContainer.appendChild(suggestionDiv);
                    });
                } else {
                    throw new Error(data.error || "Unknown server error.");
                }
            })
            .catch(error => {
                console.error("Autosuggest fetch error:", error);
                suggestionsContainer.innerHTML = '';
                const errorDiv = document.createElement("div");
                errorDiv.className = "list-group-item list-group-item-danger";
                errorDiv.textContent = "Could not fetch suggestions.";
                suggestionsContainer.appendChild(errorDiv);
            });
    });

    input.addEventListener("keydown", function(e) {
        let list = document.getElementById(this.id + "autocomplete-list");
        if (!list) return;
        let items = list.getElementsByTagName("a");
        if (items.length === 0) return;

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            currentFocus++;
            addActive(items);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            currentFocus--;
            addActive(items);
        } else if (e.key === 'Enter') {
            e.preventDefault();
            if (currentFocus > -1) {
                items[currentFocus]?.click();
            }
        }
    });

    function addActive(items) {
        if (!items) return false;
        removeActive(items);
        if (currentFocus >= items.length) currentFocus = 0;
        if (currentFocus < 0) currentFocus = (items.length - 1);
        items[currentFocus].classList.add("active");
    }

    function removeActive(items) {
        for (let i = 0; i < items.length; i++) {
            items[i].classList.remove("active");
        }
    }

    function closeAllLists() {
        activeSuggestLists.forEach((listEl) => {
            if (listEl.parentNode) {
                listEl.parentNode.removeChild(listEl);
            }
        });
        activeSuggestLists.clear();
    }

    document.addEventListener("click", function(e) {
        activeSuggestLists.forEach((listEl, inputEl) => {
            if (e.target !== inputEl && e.target !== listEl) {
                closeAllLists();
            }
        });
    });
}
