<?php
// === "NEW" BUTTON SHORTCODE ===
function toggle_buttons_and_containers() {
	// Build button container
	ob_start(); ?>
	<div class="toggle-button-container">
		<button id="toggle-create" class="toggle-button custom-button">Nieuw</button>
	</div>
	<?php return ob_get_clean();
}
add_shortcode('toggle_buttons_and_containers', 'toggle_buttons_and_containers');

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

// === VERTEGENWOORDIGER TABLE ===
function vertegenwoordiger_table_html($entries) {
	if (!$entries) return '<table class="vertegenwoordigers-table" style="border-collapse: collapse;">
								<thead>
									<tr class="vertegenwoordigers-table-row">
										<th style="width: 30%;">Naam</th>
										<th style="width: 30%;">E-mail</th>
										<th style="width: 25%;">Regio</th>
										<th style="width: 10%;">ID</th>
										<th style="width: 5%;"></th>
									</tr>
								</thead>
								<tbody>
								</tbody>
							</table>
							<div class="error-container">
								<p class="error-message">Geen vertegenwoordigers gevonden.</p>
							</div>';
	
	$html = '<table class="vertegenwoordigers-table" style="border-collapse: collapse;">
				<thead>
					<tr class="vertegenwoordigers-table-row">
						<th style="width: 30%;">Naam</th>
						<th style="width: 30%;">E-mail</th>
						<th style="width: 25%;">Regio</th>
						<th style="width: 10%;">ID</th>
						<th style="width: 5%;"></th>
					</tr>
				</thead>
				<tbody>';
	
	// List all entries in table
	foreach ($entries as $entry) {
		$name = get_post_meta($entry->ID, 'vertegenwoordiger_name', true);
		$email = get_post_meta($entry->ID, 'vertegenwoordiger_email', true);
		$region = get_post_meta($entry->ID, 'vertegenwoordiger_region', true);
		$custom_id = get_post_meta($entry->ID, 'vertegenwoordiger_custom_id', true);
		$edit_url = add_query_arg(['id' => $entry->ID], site_url('/bewerken'));
		$delete_url = wp_nonce_url(admin_url('admin-post.php?action=vertegenwoordiger_delete&id=' . $entry->ID), 'delete_vertegenwoordiger_' . $entry->ID);
		
		$html .= '<tr>';
		$html .= '<td class="vertegenwoordigers-name">' . esc_html($name) . '</td>';
		$html .= '<td class="vertegenwoordigers-email">' . '<a class="table-mail-link" href="mailto:' . esc_attr($email) . '">' . esc_html($email) . '</a>' . '</td>';
		$html .= '<td class="vertegenwoordigers-region">' . esc_html($region) . '</td>';
		$html .= '<td class="vertegenwoordigers-id">' . esc_html($custom_id) . '</td>';
		$html .= '<td class="vertegenwoordiger-menu">
				<div class="hamburger-menu">
					<button class="hamburger-toggle">☰</button>
					<div class="hamburger-dropdown">
						<a href="' . esc_url($edit_url) . '">Bewerken</a>
						<a href="' . esc_url($delete_url) . '" onclick="return confirm(\'Weet je het zeker?\')">Verwijderen</a>
					</div>
				</div>
			</td>';
		$html .= '</tr>';
	}
	
	$html .= '</tbody></table>';
	return $html;
}

// === LISTING SHORTCODE ===
function list_vertegenwoordigers_shortcode() {
	$entries = get_posts([
		'post_type' => 'vertegenwoordiger',
		'numberposts' => 10,
		'paged' => 1
	]);
	
	$output = '<div class="vertegenwoordiger-spinner-container">
				<div id="custom-spinner" style="display: none;"></div>
			</div>
			<div class="vertegenwoordigers-search-container">
				<input type="text" id="vertegenwoordigers-search" placeholder="Zoeken..." />
			</div>
			<div id="vertegenwoordiger-results">' 
				. vertegenwoordiger_table_html($entries) .
			'</div>';
	
	return $output;
}
add_shortcode('list_vertegenwoordigers', 'list_vertegenwoordigers_shortcode');

// === EDIT SHORTCODE ===
function edit_vertegenwoordiger_shortcode($atts) {
	$atts = shortcode_atts(['id' => 0], $atts);
	$id = isset($_GET['id']) ? intval($_GET['id']) : intval($atts['id']);
	
	// Check if post ID is invalid
	if (!$id || get_post_type($id) !== 'vertegenwoordiger') {
		return 'Ongeldig ID';
	}
	
	$post = get_post($id);
	$name = get_post_meta($id, 'vertegenwoordiger_name', true);
	$email = get_post_meta($id, 'vertegenwoordiger_email', true);
	$region = get_post_meta($id, 'vertegenwoordiger_region', true);
	
	// Build form
	ob_start(); ?>
	<form class="vertegenwoordigers-form" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST">
		<input type="hidden" name="action" value="vertegenwoordiger_update">
		<input type="hidden" name="id" value="<?php echo esc_attr($id); ?>">
		<?php wp_nonce_field('vertegenwoordiger_update_action_' . $id, 'vertegenwoordiger_nonce'); ?>
		<div class="form-group-vertegenwoordigers">
			<label>Update de gegevens:</label>
			<input placeholder="Naam" type="text" name="vertegenwoordiger_name" value="<?php echo esc_attr($name); ?>" required>
		</div>
		<div class="form-group-vertegenwoordigers">
			<input placeholder="E-mail" type="email" name="vertegenwoordiger_email" value="<?php echo esc_attr($email); ?>" required>
		</div>
		<div class="form-group-vertegenwoordigers">
			<select name="vertegenwoordiger_region" required>
				<option value="">Selecteer een Regio</option>
				<option value="Noord" <?php selected($region, 'Noord'); ?>>Noord</option>
				<option value="Zuid" <?php selected($region, 'Zuid'); ?>>Zuid</option>
				<option value="Oost" <?php selected($region, 'Oost'); ?>>Oost</option>
				<option value="West" <?php selected($region, 'West'); ?>>West</option>
			</select>
		</div>
		<div class="form-group-vertegenwoordigers-button">
			<button class="custom-button" style="border-radius: 10px;" type="submit">Bijwerken</button>
		</div>
	</form>
	<?php return ob_get_clean();
}
add_shortcode('edit_vertegenwoordiger', 'edit_vertegenwoordiger_shortcode');

// === CUSTOM ID GENERATOR ===
function generate_unique_custom_id($region) {
	$prefixes = ['Noord' => 'NO-', 'Oost' => 'OO-', 'Zuid' => 'ZU-', 'West' => 'WE-'];
	$prefix = $prefixes[$region] ?? 'XX';
	$used_ids = [];
	
	$existing_ids = get_posts([
		'post_type' => 'vertegenwoordiger',
		'posts_per_page' => -1,
		'fields' => 'ids',
	]);
	
	foreach ($existing_ids as $post_id) {
		$used_ids[] = get_post_meta($post_id, 'vertegenwoordiger_custom_id', true);
	}
	
	do {
		$random_number = rand(1000, 9999);
		$new_id = $prefix . $random_number;
	} while (in_array($new_id, $used_ids));
	
	return $new_id;
}

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
		$custom_id = generate_unique_custom_id($region);
		update_post_meta($post_id, 'vertegenwoordiger_custom_id', $custom_id);
	}
	
	wp_redirect(home_url('/vertegenwoordigers'));
	exit;
}

// === UPDATE HANDLER ===
add_action('admin_post_vertegenwoordiger_update', 'handle_vertegenwoordiger_update');

function handle_vertegenwoordiger_update() {
	$id = intval($_POST['id'] ?? 0);
	
	// Validate ID: if invalid, stop
	if (!$id || get_post_type($id) !== 'vertegenwoordiger') {
		wp_die('Ongeldig ID');
	}
	
	// Validate nonce: if invalid, stop
	if (!isset($_POST['vertegenwoordiger_nonce']) || !wp_verify_nonce($_POST['vertegenwoordiger_nonce'], 'vertegenwoordiger_update_action_' . $id)) {
		wp_die('Beveiligingsfout');
	}
	
	$name = sanitize_text_field($_POST['vertegenwoordiger_name']);
	$email = sanitize_email($_POST['vertegenwoordiger_email']);
	$region = sanitize_text_field($_POST['vertegenwoordiger_region']);
	$old_region = get_post_meta($id, 'vertegenwoordiger_region', true);
	$old_custom_id = get_post_meta($id, 'vertegenwoordiger_custom_id', true);
	
	update_post_meta($id, 'vertegenwoordiger_name', $name);
	update_post_meta($id, 'vertegenwoordiger_email', $email);
	update_post_meta($id, 'vertegenwoordiger_region', $region);
	
	// Check if region is changed, if so, create new ID based on new region
	if ($old_region !== $region) {
		$new_custom_id = generate_unique_custom_id($region);
		update_post_meta($id, 'vertegenwoordiger_custom_id', $new_custom_id);
	}
	
	wp_redirect(home_url('/vertegenwoordigers'));
	exit;
}

// === DELETE HANDLER ===
add_action('admin_post_vertegenwoordiger_delete', 'handle_vertegenwoordiger_delete');

function handle_vertegenwoordiger_delete() {
	// Validate ID: if invalid, stop
	if (!isset($_GET['id'])) {
		wp_die('Geen ID opgegeven');
	}
	
	$id = intval($_GET['id']);
	$nonce = $_GET['_wpnonce'];
	
	// Validate nonce: if invalid, stop
	if (!wp_verify_nonce($nonce, 'delete_vertegenwoordiger_' . $id)) {
		wp_die('Ongeldige nonce');
	}
	
	// Delete post and send user back to 'vertegenwoordigers' page
	wp_delete_post($id, true);
	wp_redirect(home_url('/vertegenwoordigers'));
	exit;
}