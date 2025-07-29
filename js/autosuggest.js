// js/autosuggest.js

// Global variable to store current active autocomplete input to prevent multiple lists
let currentAutocompleteInput = null;

// Function to close any open autocomplete lists
function closeAutocomplete()
{
    const existingList = document.querySelector('.autocomplete-items');
    if (existingList) {
        existingList.remove();
    }
    currentAutocompleteInput = null;
}

/**
 * Attaches autosuggest functionality to a given input element.
 * @param {HTMLElement} inputElement The input field to attach autosuggest to.
 * @param {string} fieldName The name of the field to query in get_autosuggest.php (e.g., 'skill_name', 'name').
 * @param {function(object|string): void} [onSelectCallback] Optional callback function when an item is selected.
 * For 'skill_name', this will receive the full skill object. For others, it will be the string value.
 */
function attachAutosuggest(inputElement, fieldName, onSelectCallback = null)
{
    let timeout = null;

    inputElement.addEventListener('input', function () {
        closeAutocomplete(); // Close any existing list before processing new input
        const query = this.value.trim();

        if (query.length < 2) { // Only fetch if at least 2 characters are typed
            return;
        }

        clearTimeout(timeout);
        timeout = setTimeout(async() => {
            currentAutocompleteInput = inputElement; // Mark this as the active input
            try {
                const response = await fetch(`get_autosuggest.php ? field = ${fieldName} & query = ${encodeURIComponent(query)}`);
                const result = await response.json();

                if (result.success && result.suggestions.length > 0) {
                    // Only display if this is still the active input
                    if (currentAutocompleteInput !== inputElement) {
                        return;
                    }

                    const list = document.createElement('div');
                    list.className = 'autocomplete-items list-group';
                    list.style.position = 'absolute'; // Position relative to parent container
                    list.style.zIndex = '1000'; // Ensure it's above other elements
                    list.style.width = inputElement.offsetWidth + 'px'; // Match input width
                    list.style.backgroundColor = 'var(--light-bg)'; // Use CSS variable
                    list.style.boxShadow = 'var(--card-shadow)'; // Use CSS variable
                    list.style.maxHeight = '200px';
                    list.style.overflowY = 'auto';
                    list.style.top = (inputElement.offsetHeight + 2) + 'px'; // Position right below input

                    result.suggestions.forEach(suggestion => {
                        const item = document.createElement('button');
                        item.type = 'button';
                        item.className = 'list-group-item list-group-item-action';

                        let displayValue;
                        let selectValue;

                        if (fieldName === 'skill_name') {
                            displayValue = suggestion.skill_name;
                            selectValue = suggestion; // Pass full object for skills
                        } else {
                            displayValue = suggestion;
                            selectValue = suggestion; // Pass string for other fields
                        }

                        item.textContent = displayValue;

                        item.addEventListener('click', function () {
                            inputElement.value = displayValue; // Set input value
                            if (onSelectCallback) {
                                onSelectCallback(selectValue); // Call callback with selected data
                            }
                            closeAutocomplete(); // Close the list after selection
                        });
                        list.appendChild(item);
                    });
                    // Append the autocomplete list to the same parent as the input for correct positioning
                    inputElement.parentNode.appendChild(list);
                } else {
                    closeAutocomplete(); // No suggestions or error, close list
                }
            } catch (error) {
                console.error('Autosuggest fetch error:', error);
                closeAutocomplete();
            }
        }, 300); // Debounce time: 300ms delay after last keypress
    });

    // Close autocomplete when the input loses focus (with a slight delay to allow click on suggestions)
    inputElement.addEventListener('blur', function () {
        setTimeout(closeAutocomplete, 150);
    });

    // Add event listener for general clicks to close autocomplete if clicked outside
    document.addEventListener('click', function (event) {
        if (currentAutocompleteInput && !inputElement.parentNode.contains(event.target) && event.target !== inputElement) {
            closeAutocomplete();
        }
    });
}