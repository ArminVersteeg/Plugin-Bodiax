<?php
// === VERTEGENWOORDIGER CUSTOM POST TYPE ===
function register_vertegenwoordiger_post_type() {
	register_post_type('vertegenwoordiger', [
		'labels' => [
			'name' => 'Vertegenwoordigers',
			'singular_name' => 'Vertegenwoordiger',
			'add_new' => 'Nieuwe vertegenwoordiger toevoegen',
			'add_new_item' => 'Nieuwe vertegenwoordiger toevoegen',
			'edit_item' => 'Bewerk vertegenwoordiger',
			'new_item' => 'Nieuwe vertegenwoordiger',
			'all_items' => 'Alle Vertegenwoordigers',
			'view_item' => 'Bekijk Vertegenwoordiger',
			'search_items' => 'Zoek vertegenwoordigers',
			'not_found' => 'Geen vertegenwoordigers gevonden',
			'not_found_in_trash' => 'Geen vertegenwoordigers in de prullenbak',
		],
		'public' => true,
		'has_archive' => true,
		'show_ui' => true,
		'supports' => [''],
		'menu_icon' => 'dashicons-businessperson',
		'show_in_rest' => true,
	]);
}
add_action('init', 'register_vertegenwoordiger_post_type');

// Hook to add meta box
add_action('add_meta_boxes', function () {
	add_meta_box(
		'vertegenwoordiger_details',
		'Vertegenwoordiger details',
		'render_vertegenwoordiger_meta_box',
		'vertegenwoordiger',
		'normal',
		'default'
	);
});

function render_vertegenwoordiger_meta_box($post) {
	$name = get_post_meta($post->ID, 'vertegenwoordiger_name', true);
	$email = get_post_meta($post->ID, 'vertegenwoordiger_email', true);
	$region = get_post_meta($post->ID, 'vertegenwoordiger_region', true);

	wp_nonce_field('save_vertegenwoordiger_meta', 'vertegenwoordiger_nonce');
	?>
	<div id="vertegenwoordiger_meta_box">
		<div class="form-group-vertegenwoordigers">
			<label for="vertegenwoordiger_name">Naam:</label>
			<input type="text" name="vertegenwoordiger_name" id="vertegenwoordiger_name" value="<?php echo esc_attr($name); ?>">
		</div>
		<div class="form-group-vertegenwoordigers">
			<label for="vertegenwoordiger_email">E-mail:</label>
			<input type="email" name="vertegenwoordiger_email" id="vertegenwoordiger_email" value="<?php echo esc_attr($email); ?>">
		</div>
		<div class="form-group-vertegenwoordigers">
			<label for="vertegenwoordiger_region">Regio:</label>
			<select name="vertegenwoordiger_region" id="vertegenwoordiger_region">
				<option value="">Selecteer een Regio</option>
				<option value="Noord" <?php selected($region, 'Noord'); ?>>Noord</option>
				<option value="Zuid" <?php selected($region, 'Zuid'); ?>>Zuid</option>
				<option value="Oost" <?php selected($region, 'Oost'); ?>>Oost</option>
				<option value="West" <?php selected($region, 'West'); ?>>West</option>
			</select>
		</div>
	</div>
	<?php
}


add_action('save_post_vertegenwoordiger', 'save_vertegenwoordiger_meta');
function save_vertegenwoordiger_meta($post_id) {
	// Security checks
	if (!isset($_POST['vertegenwoordiger_nonce']) || !wp_verify_nonce($_POST['vertegenwoordiger_nonce'], 'save_vertegenwoordiger_meta')) {
		return;
	}
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return;
	}
	if (!current_user_can('edit_post', $post_id)) {
		return;
	}

	// Sanitize and save name
	if (isset($_POST['vertegenwoordiger_name'])) {
		$name = sanitize_text_field($_POST['vertegenwoordiger_name']);
		update_post_meta($post_id, 'vertegenwoordiger_name', $name);
		
		// Also save as post title
		remove_action('save_post_vertegenwoordiger', 'save_vertegenwoordiger_meta'); // Avoid infinite loop
		wp_update_post([
			'ID' => $post_id,
			'post_title' => $name,
		]);
		add_action('save_post_vertegenwoordiger', 'save_vertegenwoordiger_meta');
	}
	
	// Sanitize and save email
	if (isset($_POST['vertegenwoordiger_email'])) {
		update_post_meta($post_id, 'vertegenwoordiger_email', sanitize_email($_POST['vertegenwoordiger_email']));
	}

	// Sanitize and save region
	if (isset($_POST['vertegenwoordiger_region'])) {
		$region = sanitize_text_field($_POST['vertegenwoordiger_region']);
		update_post_meta($post_id, 'vertegenwoordiger_region', $region);

		// Now update the custom ID based on region:
		// Assume generate_unique_custom_id is your function for creating IDs based on region
		$custom_id = generate_unique_custom_id($region);
		update_post_meta($post_id, 'vertegenwoordiger_custom_id', $custom_id);
	}
}
