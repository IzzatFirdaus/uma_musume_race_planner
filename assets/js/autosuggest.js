// Autosuggest utility for Uma Musume Race Planner
// Provides live database-backed suggestions for input fields (name, race_name, skill_name, goal, etc.)

// Store active suggestion lists for cleanup
const activeSuggestLists = new Map();

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
	if (input) {
		input.setAttribute('aria-autocomplete', 'list');
		input.setAttribute('aria-controls', input.id + 'autocomplete-list');
	}

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

		// Fetch suggestions from centralized API base
		const autosuggestUrl = `${window.APP_API_BASE}/autosuggest.php?action=get&field=${encodeURIComponent(field)}&query=${encodeURIComponent(val)}`;
		fetch(autosuggestUrl)
			.then(response => {
				if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
				return response.json();
			})
			.then(data => {
				a.innerHTML = ''; // Remove loading
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

						// Display strong for matched part
						const strong = document.createElement('strong');
						strong.textContent = String(valToDisplay).substr(0, String(val).length);
						b.appendChild(strong);
						b.appendChild(document.createTextNode(String(valToDisplay).substr(String(val).length)));

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
					const errorDiv = document.createElement("DIV");
					errorDiv.textContent = data.error || "Error fetching suggestions.";
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

	document.addEventListener("click", function (e) {
		activeSuggestLists.forEach((listEl, inputEl) => {
			if (e.target !== inputEl && e.target !== listEl && !listEl.contains(e.target)) {
				closeAllLists(inputEl);
			}
		});
	});
}