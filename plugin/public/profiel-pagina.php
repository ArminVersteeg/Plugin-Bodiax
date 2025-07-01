<?php 
// === PROFILE EDIT SECTION SHORTCODE ===
add_shortcode('user_profile_editor', 'custom_user_profile_editor_shortcode');
function custom_user_profile_editor_shortcode() {
	if (!is_user_logged_in()) return '<p>Please log in to edit your profile.</p>';
	
	$user = wp_get_current_user();
	$profile_picture_url = get_user_meta($user->ID, 'custom_profile_picture', true) ?: get_avatar_url($user->ID);
	$nickname = get_user_meta($user->ID, 'nickname', true) ?: $user->display_name;
	
	ob_start(); ?>
	<form id="profile-edit-form" enctype="multipart/form-data">
		<div class="profile-edit-logo-container">
			<img id="avatar-preview" src="<?php echo esc_url($profile_picture_url); ?>" />
			<input type="file" id="profile-picture-input" name="profile_picture" accept="image/*" style="display: none;">
			<button type="button" id="change-logo-button" class="custom-button">Kies ander Logo</button>
		</div>
		<div class="profile-edit-name-container">
			<label>Naam:<br>
				<input type="text" name="nickname" value="<?php echo esc_attr($nickname); ?>">
			</label>
		</div>
		<div class="profile-edit-email-container">
			<label>E-mailadres:<br>
				<input type="email" name="email" value="<?php echo esc_attr($user->user_email); ?>" required>
			</label>
		</div>
		<div class="profile-edit-submit-container">
			<button class="custom-button" type="submit">Profiel bijwerken</button>
			<p id="profile-status" style="margin-top:10px;"></p>
		</div>
	</form>
	<?php return ob_get_clean();
}

// === HANDLE EDIT PROFILE AJAX ===
add_action('wp_ajax_save_profile_edits', 'custom_save_profile_edits');
function custom_save_profile_edits() {
	check_ajax_referer('save_profile_edits', 'security');
	
	$user_id = get_current_user_id();
	if (!$user_id) {
		wp_send_json_error(['message' => 'Not logged in']);
	}
	
	// Nickname update
	if (!empty($_POST['nickname'])) {
		wp_update_user([
			'ID' => $user_id,
			'nickname' => sanitize_text_field($_POST['nickname'])
		]);
	}
									
	// Email update
	if (!empty($_POST['email']) && is_email($_POST['email'])) {
		wp_update_user([
			'ID' => $user_id,
			'user_email' => sanitize_email($_POST['email'])
		]);
	} else {
		wp_send_json_error(['message' => 'Ongeldige email']);
	}
	
	$new_avatar_url = null;
	
	// Handle profile picture
	if (!empty($_FILES['profile_picture']['name'])) {
		require_once(ABSPATH . 'wp-admin/includes/image.php');
		require_once(ABSPATH . 'wp-admin/includes/file.php');
		require_once(ABSPATH . 'wp-admin/includes/media.php');
		
		$old_url = get_user_meta($user_id, 'custom_profile_picture', true);
		if ($old_url) {
			$old_attachment_id = attachment_url_to_postid($old_url);
			if ($old_attachment_id) {
				wp_delete_attachment($old_attachment_id, true);
			}
		}
		
		$attachment_id = media_handle_upload('profile_picture', 0);
		if (is_wp_error($attachment_id)) {
			wp_send_json_error(['message' => $attachment_id->get_error_message()]);
		}
		
		$image_url = wp_get_attachment_url($attachment_id);
		update_user_meta($user_id, 'custom_profile_picture', esc_url_raw($image_url));
		$new_avatar_url = $image_url;
	}
	
	wp_send_json_success([
		'new_avatar' => $new_avatar_url,
		'message' => 'Profiel succesvol opgeslagen.'
	]);
}

// === OVERRIDE PROFILE PICTURE ===
add_filter('get_avatar', 'custom_override_avatar', 10, 5);
function custom_override_avatar($avatar, $id_or_email, $size, $default, $alt) {
	$user = false;
	
	if (is_numeric($id_or_email)) {
		$user = get_user_by('id', $id_or_email);
	} elseif (is_object($id_or_email) && isset($id_or_email->user_id)) {
		$user = get_user_by('id', $id_or_email->user_id);
	} elseif (is_string($id_or_email)) {
		$user = get_user_by('email', $id_or_email);
	}
	
	if ($user) {
		$custom_url = get_user_meta($user->ID, 'custom_profile_picture', true);
		if ($custom_url && filter_var($custom_url, FILTER_VALIDATE_URL)) {
			$avatar = sprintf(
				'<img alt="%s" src="%s" class="avatar avatar-%d photo" height="%d" width="%d" />',
				esc_attr($alt),
				esc_url($custom_url),
				(int) $size,
				(int) $size,
				(int) $size
			);
		}
	}
	
	return $avatar;
}

// === UPDATE PASSWORD SECTION SHORTCODE ===
add_shortcode('update_password_form', 'custom_update_password_form_shortcode');
function custom_update_password_form_shortcode() {
	if (!is_user_logged_in()) return '<p>Please log in to change your password.</p>'; // Show error when no user is logged in
	
	ob_start(); ?>
	<form id="password-update-form">
		<div class="password-update-field">
			<label>Huidig wachtwoord:<br>
				<input type="password" name="current_password" required>
			</label>
		</div>
		<div class="password-update-field">
			<label>Nieuw wachtwoord:<br>
				<input type="password" name="new_password" required>
			</label>
		</div>
		<div class="password-update-field">
			<label>Bevestig nieuw wachtwoord:<br>
				<input type="password" name="confirm_new_password" required>
			</label>
		</div>
		<div class="password-update-submit">
			<button class="custom-button" type="submit">Wachtwoord bijwerken</button>
			<p id="password-update-status" style="margin-top: 10px;"></p>
		</div>
	</form>
	<?php return ob_get_clean();
}
