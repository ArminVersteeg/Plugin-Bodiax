document.addEventListener('DOMContentLoaded', function () {
	// AJAX
	// Search/AJAX Variables
	const searchInput = document.getElementById('ajax-search');
	const resultsContainer = document.getElementById('ajax-results');
	const spinner = document.getElementById('custom-spinner');
	const ajaxUrl = BodiaxData.ajaxUrl;
	let debounceTimeout;

	// Show the spinner during AJAX requests
	function showSpinner() {
		if (spinner) spinner.style.display = 'block';
	}

	// Hide the spinner after AJAX requests complete
	function hideSpinner() {
		if (spinner) spinner.style.display = 'none';
	}

	// Fetch search results
	function fetchResults(search = '', page = 1) {
		showSpinner();

		const formData = new FormData();
		const dynamicAction = resultsContainer.dataset.action || 'search_vertegenwoordigers';

		formData.append('action', dynamicAction);
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
			attachHamburgerListeners();
			hideSpinner();
		})
			.catch(err => {
			console.error('AJAX error:', err);
			resultsContainer.innerHTML = '<p>Er ging iets mis</p>';
			hideSpinner();
		});
	}

	// Pagination event listeners
	function attachPaginationEvents() {
		document.querySelectorAll('.pagination-page-btn').forEach(button => {
			button.addEventListener('click', function () {
				const page = this.getAttribute('data-page');
				const search = searchInput.value.trim();
				fetchResults(search, page);
			});
		});
	}

	// Hamburger menu event listeners
	function attachHamburgerListeners() {
		document.querySelectorAll('.hamburger-toggle').forEach(button => {
			button.addEventListener('click', function (e) {
				e.stopPropagation();
				closeAllDropdowns();
				const dropdown = this.nextElementSibling;
				dropdown.classList.toggle('show');
			});
		});

		document.addEventListener('click', closeAllDropdowns);

		function closeAllDropdowns() {
			document.querySelectorAll('.hamburger-dropdown').forEach(d => d.classList.remove('show'));
		}
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
	
	// Button toggle script for create form
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
	
	// CSV Upload hidden form
	const uploadButton = document.getElementById('upload-button');
	const csvInput = document.getElementById('csv_file');

	if (uploadButton && csvInput) {
		// When the "Upload" button is clicked, trigger the hidden file input
		uploadButton.addEventListener('click', function () {
			csvInput.click();
		});

		// When the file is selected, automatically submit the form
		csvInput.addEventListener('change', function () {
			if (this.files.length > 0) {
				this.form.submit(); // Automatically submit the form
			}
		});
	}

	// Duplicate error
	const modal = document.getElementById('error-modal');
	const modalOk = document.getElementById('modal-ok');
	
	// Show modal if URL contains ?error=duplicate
	if (window.location.href.indexOf('error=duplicate') > -1) {
		modal.style.display = 'flex';
		
		// Remove error param from URL after showing modal
		const newUrl = window.location.href.split('?')[0];
		window.history.replaceState({}, document.title, newUrl);
	}
	
	// Close modal on OK
	modalOk.addEventListener('click', function () {
		modal.style.display = 'none';
	});
	
	// Close modal if clicking outside the modal box
	modal.addEventListener('click', function (e) {
		if (e.target === modal) {
			modal.style.display = 'none';
		}
	});
});

// Profile dropdown variables
const avatar = document.getElementById('user-avatar');
const dropdown = document.getElementById('profile-dropdown');

// User profile dropdown
if (avatar && dropdown) {
	avatar.addEventListener('click', function () {
		dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
	});
	
	document.addEventListener('click', function (e) {
		if (!avatar.contains(e.target) && !dropdown.contains(e.target)) {
			dropdown.style.display = 'none';
		}
	});
}

// Ajax listener for profile page
const formData = new FormData(document.getElementById('profile-edit-form'));
formData.append('action', 'save_profile_edits');
formData.append('security', profileEditData.nonce);

fetch(profileEditData.ajaxUrl, {
	method: 'POST',
	body: formData,
})
	.then(response => response.json())
	.then(data => {
	if (data.success) {
		console.log('Success!');
	} else {
		console.error('Error:', data.data.message);
	}
});
