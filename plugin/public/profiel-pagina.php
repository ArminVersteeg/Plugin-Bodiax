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
