// Button toggle script for create form
document.addEventListener('DOMContentLoaded', function () {
	// Toggle Variables
	const toggleCreateButton = document.getElementById('toggle-create');
	const createContainer = document.getElementById('create-container');
	
	// Toggle container visibility function
	function toggleContainer(container) {
		if (container.style.display === 'none' || container.style.display === '') {
			container.style.display = 'block';
		} else {
			container.style.display = 'none';
		}
	}
	
	// Toggle button event listener for the Create container
	if (toggleCreateButton && createContainer) {
		toggleCreateButton.addEventListener('click', function () {
			toggleContainer(createContainer);
		});
	}
});

// AJAX
// Search/AJAX Variables
const searchInput = document.getElementById('vertegenwoordigers-search');
const resultsContainer = document.getElementById('vertegenwoordiger-results');
const ajaxUrl = BodiaxData.ajaxUrl;
let debounceTimeout;

// Fetch search results
function fetchResults(search = '', page = 1) {
	
	const formData = new FormData();
	formData.append('action', 'search_vertegenwoordigers');
	formData.append('search', search);
	formData.append('page', page);
	
	fetch(ajaxUrl, {
		method: 'POST',
		body: formData
	})
		.then(res => res.text())
		.then(html => {
		resultsContainer.innerHTML = html;
		attachPaginationEvents();
	})
		.catch(err => {
		console.error('AJAX error:', err);
		resultsContainer.innerHTML = '<p>Er ging iets mis</p>';
	});
}

// Pagination event listeners
function attachPaginationEvents() {
	document.querySelectorAll('.vertegenwoordiger-page-btn').forEach(button => {
		button.addEventListener('click', function () {
			const page = this.getAttribute('data-page');
			const search = searchInput.value.trim();
			fetchResults(search, page);
		});
	});
}

// Input listener for search
if (searchInput) {
	searchInput.addEventListener('input', function () {
		const value = this.value.trim();
		clearTimeout(debounceTimeout);
		debounceTimeout = setTimeout(function () {
			fetchResults(value);
		}, 250);
	});
}

// Initialize the fetchResults function
fetchResults();