
(function($){
	$('#profile-edit-form').on('submit', function(e){
		e.preventDefault();

		var formData = new FormData(this);
		formData.append('action', 'save_profile_edits');
		formData.append('security', BodiaxData.profileEditNonce);

		$.ajax({
			url: BodiaxData.ajaxUrl,
			type: 'POST',
			data: formData,
			processData: false,
			contentType: false,
			success: function(response) {
				if(response.success){
					$('#profile-status')
						.removeClass('saved-status-failed')
						.addClass('saved-status-success')
						.text('Profiel succesvol opgeslagen.');
					if(response.data.new_avatar){
						$('#avatar-preview').attr('src', response.data.new_avatar);
					}
				} else {
					$('#profile-status')
						.removeClass('saved-status-success')
						.addClass('saved-status-failed')
						.text('Fout: ' + (response.data?.message || "Onbekende fout"));
				}
			}
		});
	});

	$('#change-logo-button').on('click', function(e) {
		e.preventDefault();
		$('#profile-picture-input').trigger('click');
	});

	$('#profile-picture-input').on('change', function(){
		const file = this.files[0];
		if (file && file.type.startsWith('image/')) {
			const reader = new FileReader();
			reader.onload = function(e) {
				$('#avatar-preview').attr('src', e.target.result);
			}
			reader.readAsDataURL(file);
		}
	});
})(jQuery);