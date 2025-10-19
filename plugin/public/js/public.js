document.addEventListener('DOMContentLoaded', function () {
    // ---------------------- AJAX SEARCH ----------------------
    const searchInput = document.getElementById('ajax-search');
    const resultsContainer = document.getElementById('ajax-results');
    const spinner = document.getElementById('custom-spinner');
    const ajaxUrl = BodiaxData.ajaxUrl; // Must be localized via wp_localize_script
    let debounceTimeout;

    function showSpinner() {
        if (spinner) spinner.style.display = 'block';
    }

    function hideSpinner() {
        if (spinner) spinner.style.display = 'none';
    }

    function fetchResults(search = '', page = 1) {
        showSpinner();

        const formData = new FormData();
		
		const dynamicAction = resultsContainer.dataset.action || 'search_vertegenwoordigers';
		console.log('AJAX action being used:', dynamicAction);
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

    // Debounced input
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            const value = this.value.trim();
            clearTimeout(debounceTimeout);
            debounceTimeout = setTimeout(() => {
                fetchResults(value);
            }, 250);
        });
    }

    // ---------------------- PAGINATION ----------------------
    function attachPaginationEvents() {
        document.querySelectorAll('.pagination-page-btn').forEach(button => {
            button.addEventListener('click', function () {
                const page = this.getAttribute('data-page');
                const search = searchInput.value.trim();
                fetchResults(search, page);
            });
        });
    }

    // ---------------------- HAMBURGER MENU ----------------------
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

    // ---------------------- INITIAL FETCH ----------------------
    fetchResults();

    // ---------------------- TOGGLE CREATE FORM ----------------------
    const toggleCreateButton = document.getElementById('toggle-create');
    const createContainer = document.getElementById('create-container');

    function toggleContainer(container) {
        if (!container) return;
        container.style.display = (container.style.display === 'block') ? 'none' : 'block';
    }

    if (toggleCreateButton && createContainer) {
        toggleCreateButton.addEventListener('click', () => toggleContainer(createContainer));
    }

    // ---------------------- CSV UPLOAD ----------------------
    const uploadButton = document.getElementById('upload-button');
    const csvInput = document.getElementById('csv_file');

    if (uploadButton && csvInput) {
        uploadButton.addEventListener('click', () => csvInput.click());
        csvInput.addEventListener('change', function () {
            if (this.files.length > 0) this.form.submit();
        });
    }

    // ---------------------- DUPLICATE ERROR MODAL ----------------------
    const modal = document.getElementById('error-modal');
    const modalOk = document.getElementById('modal-ok');

    if (window.location.href.indexOf('error=duplicate') > -1 && modal) {
        modal.style.display = 'flex';
        const newUrl = window.location.href.split('?')[0];
        window.history.replaceState({}, document.title, newUrl);
    }

    if (modalOk && modal) {
        modalOk.addEventListener('click', () => modal.style.display = 'none');
        modal.addEventListener('click', e => {
            if (e.target === modal) modal.style.display = 'none';
        });
    }
});

// ---------------------- PROFILE DROPDOWN ----------------------
const avatar = document.getElementById('user-avatar');
const dropdown = document.getElementById('profile-dropdown');

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
