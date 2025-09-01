// js/autosuggest.js
const activeSuggestLists = new Map(); // Stores active suggestion lists for easy cleanup

/**
 * Attaches autosuggest functionality to an input field.
 * @param {HTMLInputElement} input The input element to attach autosuggest to.
 * @param {string} field The database field name to query (e.g., 'name', 'race_name', 'skill_name').
 * @param {function(Object): void} [onSelectCallback] Optional callback function when a suggestion is selected.
 * For skill_name, this will receive the full skill object.
 */
function attachAutosuggest(input, field, onSelectCallback = null)
{
    let currentFocus = -1;
    let currentInput = input; // Store reference for closure

    // Close any existing autosuggest list for this input
    closeAllLists(input);

    input.addEventListener("input", function (e) {
        const val = this.value;
        closeAllLists(this); // Close any open lists before creating a new one

        if (!val) {
            return false; }

        currentFocus = -1;
        const a = document.createElement("DIV");
        a.setAttribute("id", this.id + "autocomplete-list");
        a.setAttribute("class", "autocomplete-items card"); // Use card class for styling
        this.parentNode.appendChild(a);
        activeSuggestLists.set(this, a); // Store the list element

        // Add a loading indicator
        const loadingDiv = document.createElement("DIV");
        loadingDiv.textContent = "Loading...";
        loadingDiv.style.padding = "10px";
        loadingDiv.style.textAlign = "center";
        a.appendChild(loadingDiv);

        fetch(`get_autosuggest.php ? field = ${field} & query = ${encodeURIComponent(val)}`)
            .then(response => {
                // Check if response is OK (status 200-299)
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                // Read response as text first for better error debugging
                return response.text();
            })
            .then(text => {
                // Remove loading indicator
                a.innerHTML = '';
                try {
                    const data = JSON.parse(text); // Try parsing as JSON
                    if (data.success && data.suggestions) {
                        data.suggestions.forEach(item => {
                            const valToDisplay = field === 'skill_name' ? item.skill_name : item;
                            const b = document.createElement("DIV");
                            b.innerHTML = "<strong>" + valToDisplay.substr(0, val.length) + "</strong>";
                            b.innerHTML += valToDisplay.substr(val.length);
                            b.innerHTML += "<input type='hidden' value='" + valToDisplay + "'>";
                            b.addEventListener("click", function (e) {
                                input.value = this.getElementsByTagName("input")[0].value;
                                if (onSelectCallback) {
                                    // If skill_name, pass the full item object
                                    const selectedItem = field === 'skill_name' ? item : valToDisplay;
                                    onSelectCallback(selectedItem);
                                }
                                closeAllLists();
                            });
                            a.appendChild(b);
                        });
                        if (data.suggestions.length === 0) {
                            const noResults = document.createElement("DIV");
                            noResults.textContent = "No results found.";
                            noResults.style.padding = "10px";
                            noResults.style.color = "var(--color-text-muted-light)";
                            a.appendChild(noResults);
                        }
                    } else {
                        // Handle server-side errors reported in JSON
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
                // Remove loading indicator
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
        if (x) {
            x = x.getElementsByTagName("div");
        }
        if (e.keyCode == 40) { // DOWN arrow
            currentFocus++;
            addActive(x);
        } else if (e.keyCode == 38) { // UP arrow
            currentFocus--;
            addActive(x);
        } else if (e.keyCode == 13) { // ENTER key
            e.preventDefault();
            if (currentFocus > -1) {
                if (x) {
                    x[currentFocus].click();
                }
            }
        }
    });

    function addActive(x)
    {
        if (!x) {
            return false;
        }
        removeActive(x);
        if (currentFocus >= x.length) {
            currentFocus = 0;
        }
        if (currentFocus < 0) {
            currentFocus = (x.length - 1);
        }
        x[currentFocus].classList.add("autocomplete-active");
        // Scroll into view if out of bounds
        x[currentFocus].parentNode.scrollTop = x[currentFocus].offsetTop - x[currentFocus].parentNode.offsetTop;
    }

    function removeActive(x)
    {
        for (let i = 0; i < x.length; i++) {
            x[i].classList.remove("autocomplete-active");
        }
    }

    function closeAllLists(elmnt = null)
    {
        activeSuggestLists.forEach((listEl, inputEl) => {
            if (elmnt !== inputEl) { // Only close lists not associated with the current input
                listEl.parentNode.removeChild(listEl);
                activeSuggestLists.delete(inputEl);
            }
        });
    }

    // Close on click outside
    document.addEventListener("click", function (e) {
        activeSuggestLists.forEach((listEl, inputEl) => {
            if (e.target !== inputEl && e.target !== listEl && !listEl.contains(e.target)) {
                closeAllLists(inputEl); // Pass the input to close only its associated list
            }
        });
    });
}