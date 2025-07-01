<?php
// Shortcode to display current user's username
function show_current_username() {
	if (is_user_logged_in()) {
		$current_user = wp_get_current_user();
		return esc_html( get_user_meta($current_user->ID, 'first_name', true));
	}
	
	return '';
}
add_shortcode('username', 'show_current_username');