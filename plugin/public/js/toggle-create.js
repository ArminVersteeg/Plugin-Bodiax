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