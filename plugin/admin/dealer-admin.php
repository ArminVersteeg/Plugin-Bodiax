<?php
// === DEALER CUSTOM POST TYPE ===
function register_dealer_post_type() {
	register_post_type('Dealer', [
		'labels' => [
			'name' => 'Dealers',
			'singular_name' => 'Dealer',
			'add_new' => 'Nieuwe dealer toevoegen',
			'add_new_item' => 'Nieuwe dealer toevoegen',
			'edit_item' => 'Bewerk dealer',
			'new_item' => 'Nieuwe dealer',
			'all_items' => 'Alle dealers',
			'view_item' => 'Bekijk dealer',
			'search_items' => 'Zoek dealers',
			'not_found' => 'Geen dealers gevonden',
			'not_found_in_trash' => 'Geen dealers in de prullenbak',
		],
		'public' => true,
		'has_archive' => true,
		'show_ui' => true,
		'supports' => [''],
		'menu_icon' => 'dashicons-businessperson',
		'show_in_rest' => true,
	]);
}
add_action('init', 'register_dealer_post_type');

// Hook to add meta box
add_action('add_meta_boxes', function () {
	add_meta_box(
		'dealer_details',
		'dealer details',
		'render_dealer_meta_box',
		'dealer',
		'normal',
		'default'
	);
});

function render_dealer_meta_box($post) {
	$name = get_post_meta($post->ID, 'dealer_name', true);
	$email = get_post_meta($post->ID, 'dealer_email', true);
	$address = get_post_meta($post->ID, 'dealer_address', true);

	wp_nonce_field('save_dealer_meta', 'dealer_nonce');
	?>
	<div id="dealer_meta_box">
		<div class="form-group-dealers">
			<label for="dealer_name">Naam:</label>
			<input type="text" name="dealer_name" id="dealer_name"  value="<?php echo esc_attr($name); ?>" required>
		</div>
		<div class="form-group-dealers">
			<label for="dealer_email">E-mail:</label>
			<input type="email" name="dealer_email" id="dealer_email"  value="<?php echo esc_attr($email); ?>" required>
		</div>
		<div class="form-group-dealers">
			<label for="dealer_address">Adres:</label>
			<input type="text" name="dealer_address" id="dealer_address"  value="<?php echo esc_attr($address); ?>" required>
		</div>
	</div>
	<?php
}

add_action('save_post_dealer', 'save_dealer_meta');
function save_dealer_meta($post_id) {
	// Security checks
	if (!isset($_POST['dealer_nonce']) || !wp_verify_nonce($_POST['dealer_nonce'], 'save_dealer_meta')) {
		return;
	}
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return;
	}
	if (!current_user_can('edit_post', $post_id)) {
		return;
	}

	// Sanitize and save name
	if (isset($_POST['dealer_name'])) {
		$name = sanitize_text_field($_POST['dealer_name']);
		update_post_meta($post_id, 'dealer_name', $name);
		
		// Also save as post title
		remove_action('save_post_dealer', 'save_dealer_meta'); // Avoid infinite loop
		wp_update_post([
			'ID' => $post_id,
			'post_title' => $name,
		]);
		add_action('save_post_dealer', 'save_dealer_meta');
	}
	
	// Sanitize and save email
	if (isset($_POST['dealer_email'])) {
		$email = sanitize_email($_POST['dealer_email']);
		update_post_meta($post_id, 'dealer_email', $email);
	}

	// Sanitize and save address
	if (isset($_POST['dealer_address'])) {
		$address = sanitize_email($_POST['dealer_address']);
		update_post_meta($post_id, 'dealer_address', $address);
	}
}