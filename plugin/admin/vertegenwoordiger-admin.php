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

// Vertegenwoordigers details
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

		// Update the custom ID based on region
		$custom_id = generate_unique_custom_id($region);
		update_post_meta($post_id, 'vertegenwoordiger_custom_id', $custom_id);
	}
}

// === CSV UPLOAD ===
// Submenu
add_action('admin_menu', function () {
	add_submenu_page(
		'edit.php?post_type=vertegenwoordiger',
		'CSV Upload',
		'CSV Upload',
		'manage_options',
		'vertegenwoordiger-csv-upload',
		'render_csv_upload_page'
	);
});


function render_csv_upload_page() {
	// Build CSV Form container
	?>
	<div class="wrap">
		<h1>CSV Upload voor Vertegenwoordigers</h1><br>
		<form class="csv-upload-form" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST" enctype="multipart/form-data">
			<input type="hidden" name="action" value="process_csv_upload">
			<?php wp_nonce_field('csv_upload_action', 'csv_upload_nonce'); ?>
			<input type="file" style="display: none;" name="csv_file" id="csv_file" required>
			<button class="toggle-button custom-button csv-button" type="button" id="upload-button">Upload</button>
		</form>
	</div>
	<script>
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
	</script>
	<?php
}
