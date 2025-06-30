<?php
// === CREATE FORM SHORTCODE ===
function vertegenwoordiger_form_shortcode() {
	// Build form
	ob_start(); ?>
	<div id="create-container" class="toggle-container" style="display:none;">
		<form class="vertegenwoordigers-form" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST">
			<input type="hidden" name="action" value="vertegenwoordiger_create">
			<?php wp_nonce_field('vertegenwoordiger_create_action', 'vertegenwoordiger_nonce'); ?>
			<div class="form-group-vertegenwoordigers">
				<input placeholder="Naam" type="text" name="vertegenwoordiger_name" required>
			</div>
			<div class="form-group-vertegenwoordigers">
				<input placeholder="E-mail" type="email" name="vertegenwoordiger_email" required>
			</div>
			<div class="form-group-vertegenwoordigers">
				<select name="vertegenwoordiger_region" required>
					<option value="">Selecteer een Regio</option>
					<option value="Noord">Noord</option>
					<option value="Zuid">Zuid</option>
					<option value="Oost">Oost</option>
					<option value="West">West</option>
				</select>
			</div>
			<div class="form-group-vertegenwoordigers-button">
				<button class="custom-button" type="submit">Toevoegen</button>
			</div>
		</form>
	</div>
	<?php return ob_get_clean();
}
add_shortcode('vertegenwoordiger_form', 'vertegenwoordiger_form_shortcode');

// === CREATE HANDLER ===
add_action('admin_post_vertegenwoordiger_create', 'handle_vertegenwoordiger_create');
add_action('admin_post_nopriv_vertegenwoordiger_create', 'handle_vertegenwoordiger_create');

function handle_vertegenwoordiger_create() {
	// Validate nonce: if invalid, stop
	if (!isset($_POST['vertegenwoordiger_nonce']) || !wp_verify_nonce($_POST['vertegenwoordiger_nonce'], 'vertegenwoordiger_create_action')) {
		wp_die('Beveiligingsfout');
	}
	
	$name = sanitize_text_field($_POST['vertegenwoordiger_name']);
	$email = sanitize_email($_POST['vertegenwoordiger_email']);
	$region = sanitize_text_field($_POST['vertegenwoordiger_region']);
	
	// Validate input fields: name, email, region. If invalid, stop
	if (!$name || !is_email($email) || !$region) {
		wp_die('Ongeldige invoer');
	}
	
	// Run duplicates check
	$check = check_vertegenwoordiger_duplicates($name, $email);
	$skipped = [];
	
	if ($check['duplicate']) {
		// Safely encode duplicates using "|" as separator
		$error_items = implode('|', $check['duplicates']);
		
		$redirect_url = home_url('/vertegenwoordigers/');
		$redirect_url = add_query_arg('error', 'duplicate', $redirect_url);
		$redirect_url = add_query_arg('skipped', urlencode($error_items), $redirect_url);
		
		wp_redirect($redirect_url);
		exit;
	}

	// Add post to custom post type 'vertegenwoordiger'
	$post_id = wp_insert_post([
		'post_type' => 'vertegenwoordiger',
		'post_title' => $name,
		'post_status' => 'publish',
	]);
	
	if ($post_id) {
		update_post_meta($post_id, 'vertegenwoordiger_name', $name);
		update_post_meta($post_id, 'vertegenwoordiger_email', $email);
		update_post_meta($post_id, 'vertegenwoordiger_region', $region);
	}
	
	wp_redirect(home_url('/vertegenwoordigers'));
	exit;
}