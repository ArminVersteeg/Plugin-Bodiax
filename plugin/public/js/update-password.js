(function($){
	// Listen for the submit event on the password update form
	$('#password-update-form').on('submit', function(e){
		e.preventDefault();
		const data = {
			action: 'custom_update_password',
			security: BodiaxData.passwordUpdateNonce,
			current_password: $('input[name="current_password"]').val(),
			new_password: $('input[name="new_password"]').val(),
			confirm_new_password: $('input[name="confirm_new_password"]').val()
		};
		
		// Send an AJAX POST request to admin-ajax.php
		$.post(BodiaxData.ajaxUrl, data, function(response){
			if (response.success) {
				$('#password-update-status')
					.removeClass("saved-status-failed")
					.addClass("saved-status-success")
					.text(response.data.message);
				$('#password-update-form')[0].reset();
			} else {
				$('#password-update-status')
					.removeClass("saved-status-success")
					.addClass("saved-status-failed")
					.text(response.data.message);
			}
		});
	});
})(jQuery);