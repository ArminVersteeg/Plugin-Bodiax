<?php
// ================ CRUD =================
// === "NEW" + UPLOAD BUTTON SHORTCODE ===
function toggle_buttons_and_containers() {
	// Build button container
	ob_start(); ?>
	<div class="toggle-buttons-container">
		<button id="toggle-create" class="toggle-button custom-button" type="button">Nieuw</button>
		<form class="csv-upload-form" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST" enctype="multipart/form-data">
			<input type="hidden" name="action" value="process_csv_upload">
			<?php wp_nonce_field('csv_upload_action', 'csv_upload_nonce'); ?>
			<input type="file" style="display: none;" name="csv_file" id="csv_file" required>
			<button class="toggle-button custom-button" id="upload-button" type="button">
				<svg aria-hidden="true" id="csv-upload-icon" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg">
					<path d="M296 384h-80c-13.3 0-24-10.7-24-24V192h-87.7c-17.8 0-26.7-21.5-14.1-34.1L242.3 5.7c7.5-7.5 19.8-7.5 27.3 0l152.2 152.2c12.6 12.6 3.7 34.1-14.1 34.1H320v168c0 13.3-10.7 24-24 24zm216-8v112c0 13.3-10.7 24-24 24H24c-13.3 0-24-10.7-24-24V376c0-13.3 10.7-24 24-24h136v8c0 30.9 25.1 56 56 56h80c30.9 0 56-25.1 56-56v-8h136c13.3 0 24 10.7 24 24zm-124 88c0-11-9-20-20-20s-20 9-20 20 9 20 20 20 20-9 20-20zm64 0c0-11-9-20-20-20s-20 9-20 20 9 20 20 20 20-9 20-20z">
					</path>
				</svg>
			</button>
		</form>
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
				<input type="text" id="ajax-search" placeholder="Zoeken..." />
			</div>
			<div id="ajax-results" data-action="search_vertegenwoordigers">' 
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
	
	// Update post with new information
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
// ============================================

// ================== AJAX ====================
add_action('wp_ajax_search_vertegenwoordigers', 'search_vertegenwoordigers_ajax');
add_action('wp_ajax_nopriv_search_vertegenwoordigers', 'search_vertegenwoordigers_ajax');

function search_vertegenwoordigers_ajax() {
	$search = sanitize_text_field($_POST['search'] ?? '');
	$page = max(1, intval($_POST['page'] ?? 1));
	$per_page = 10;
	
	// Build the query arguments
	$query_args = [
		'post_type' => 'vertegenwoordiger',
		'posts_per_page' => $per_page,
		'paged' => $page,
		'meta_query' => [
			'relation' => 'OR',
			['key' => 'vertegenwoordiger_name', 'value' => $search, 'compare' => 'LIKE'],
			['key' => 'vertegenwoordiger_email', 'value' => $search, 'compare' => 'LIKE'],
			['key' => 'vertegenwoordiger_region', 'value' => $search, 'compare' => 'LIKE'],
			['key' => 'vertegenwoordiger_custom_id', 'value' => $search, 'compare' => 'LIKE'],
		],
	];
	
	// Run the query
	$query = new WP_Query($query_args);
	
	// Build empty table if no results
	if (!$query->have_posts()) {
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
					<tbody>
					</tbody>
					</table>
					<div class="error-container">
						<p class="error-message">Geen vertegenwoordigers gevonden.</p>
					</div>';
		
		echo $html;
		wp_die();
	}

	// Output the results in table format
	echo vertegenwoordiger_table_html($query->posts);
	
	// Pagination
	$total = $query->found_posts;
	$total_pages = ceil($total / $per_page);
	
	if ($total_pages > 1) {
		echo '<div class="vertegenwoordiger-pagination">';
		
		if ($page > 1) {
			echo '<button class="pagination-page-btn" data-page="' . ($page - 1) . '" type="button">‹</button>';
		}
		
		if ($page > 2) {
			echo '<button class="pagination-page-btn" data-page="1" type="button">1</button>';
			if ($page > 3) {
				echo '<span class="dots">...</span>';
			}
		}
		
		for ($i = max(1, $page - 1); $i <= min($total_pages, $page + 1); $i++) {
			if ($i == $page) {
				echo '<button class="current" disabled>' . $i . '</button>';
			} else {
				echo '<button class="pagination-page-btn" data-page="' . $i . '" type="button">' . $i . '</button>';
			}
		}
		
		if ($page < $total_pages - 1) {
			if ($page < $total_pages - 2) {
				echo '<span class="dots">...</span>';
			}
			echo '<button class="pagination-page-btn" data-page="' . $total_pages . '" type="button">' . $total_pages . '</button>';
		}
		
		if ($page < $total_pages) {
			echo '<button class="pagination-page-btn" data-page="' . ($page + 1) . '" type="button">›</button>';
		}
		
		echo '</div>';
	}
	
	wp_die();
}

// === APPLY SEARCH FILTER ===
function filter_vertegenwoordiger_post_title($where, $wp_query) {
	if (!is_admin() && $wp_query->get('post_type') === 'vertegenwoordiger') {
		global $wpdb;
		
		// Get the search term
		$search = $wp_query->get('s');
		
		if (!empty($search)) {
			$where .= " AND {$wpdb->posts}.post_title LIKE '%" . esc_sql($search) . "%'";
		}
	}
	return $where;
}
add_filter('posts_where', 'filter_vertegenwoordiger_post_title', 10, 2);
// ============================================


// =============== CSV UPLOAD =================
// ============ PROCESS CSV FILE ==============
function handle_csv_upload() {
	// Check nonce for security
	if (!isset($_POST['csv_upload_nonce']) || !wp_verify_nonce($_POST['csv_upload_nonce'], 'csv_upload_action')) {
		wp_die('Security check failed.');
	}
	
	// Check if a file has been uploaded
	if (empty($_FILES['csv_file']['tmp_name'])) {
		wp_die('No file uploaded.');
	}
	
	// Get the uploaded file
	$file = $_FILES['csv_file'];
	
	// Check if it's a valid CSV file
	if ($file['type'] !== 'text/csv' && $file['type'] !== 'application/vnd.ms-excel') {
		wp_die('Ongeldig bestandstype. Upload a.u.b. een CSV bestand.');
	}
	
	$handle = fopen($file['tmp_name'], 'r'); // Open the file for reading
	$row = 0;
	$duplicates = []; // Array to collect all duplicates
	
	if ($handle !== false) {
		while (($data = fgetcsv($handle, 1000, ';')) !== false) {
			// Skip row 0
			if ($row === 0) {
				$row++;
				continue;
			}
			
			// Parse the CSV data
			$name = sanitize_text_field($data[0]); // Sanitize name field
			$email = sanitize_email($data[1]); // Sanitize email field
			$region = ucfirst(strtolower(sanitize_text_field($data[2]))); // Decapitalize value > Capitalise first letter > Sanitize field
			
			// Check for duplicate name
			$existing_name = get_posts([
				'post_type' => 'vertegenwoordiger',
				'posts_per_page' => 1,
				'meta_query' => [
					['key' => 'vertegenwoordiger_name', 'value' => $name, 'compare' => '=']
				],
			]);
			
			// Check for duplicate email
			$existing_email = get_posts([
				'post_type' => 'vertegenwoordiger',
				'posts_per_page' => 1,
				'meta_query' => [
					['key' => 'vertegenwoordiger_email', 'value' => $email, 'compare' => '=']
				],
			]);
			
			// If either or both are duplicates, skip and set reason
			if (!empty($existing_name) || !empty($existing_email)) {
				if (!empty($existing_name) && !empty($existing_email)) {
					$reason = 'naam en e-mail al bestaan';
				} elseif (!empty($existing_name)) {
					$reason = 'naam al bestaat';
				} else {
					$reason = 'e-mail bestaat al';
				}
				
				$duplicates[] = [
					'name' => $name,
					'email' => $email,
					'reason' => $reason
				];
				
				$row++;
				continue; // Skip insertion
			}
			
			// Generate the unique timestamp for each post
			$unique_timestamp = time() + $row;
			
			// Insert the data into the custom post type 'vertegenwoordiger'
			$post_id = wp_insert_post([
				'post_type' => 'vertegenwoordiger',
				'post_status' => 'publish',
				'post_title' => $name,
				'post_date' => date('Y-m-d H:i:s', $unique_timestamp), // Set the unique post date and time
			]);
			
			if ($post_id) {
				update_post_meta($post_id, 'vertegenwoordiger_name', $name);
				update_post_meta($post_id, 'vertegenwoordiger_email', $email);
				update_post_meta($post_id, 'vertegenwoordiger_region', $region);
				$custom_id = generate_unique_custom_id($region);
				update_post_meta($post_id, 'vertegenwoordiger_custom_id', $custom_id);
			}
			
			$row++;
		}
		
		fclose($handle);
	}
	
	// Redirect with duplicate info (if any)
	if (!empty($duplicates)) {
		$names = array_map(fn($d) => $d['name'], $duplicates);
		$emails = array_map(fn($d) => $d['email'], $duplicates);
		$reasons = array_map(fn($d) => $d['reason'], $duplicates);
		
		$url = add_query_arg([
			'error' => 'duplicate',
			'names' => urlencode(implode('|', $names)),
			'emails' => urlencode(implode('|', $emails)),
			'reasons' => urlencode(implode('|', $reasons))
		], wp_get_referer());
		
		wp_redirect($url); 
		exit;
	}
	
	wp_redirect($_SERVER['HTTP_REFERER']); // Redirect user back to previous page
	exit;
}
add_action('admin_post_process_csv_upload', 'handle_csv_upload');
// ==========================================

// ============ DUPLICATE CHECK =============
// === DUPLICATE NAME AND/OR E-MAIL CHECK ===
function check_vertegenwoordiger_duplicates($name, $email) {
	$duplicates = []; // List for what is duplicate
	$duplicate_names = []; // List for duplicate names
	$duplicate_emails = []; // List for duplicate emails
	$duplicate_reasons = []; // List for the reasons
	
	// Check for duplicate name
	$existing_name = get_posts([
		'post_type' => 'vertegenwoordiger',
		'posts_per_page' => 1,
		'meta_query' => [
			['key' => 'vertegenwoordiger_name', 'value' => $name, 'compare' => '=']
		]
	]);
	
	if (!empty($existing_name)) {
		$duplicates[] = 'naam';
		$duplicate_names[] = $name; // Store the duplicate name
	}
	
	// Check for duplicate email
	$existing_email = get_posts([
		'post_type' => 'vertegenwoordiger',
		'posts_per_page' => 1,
		'meta_query' => [
			['key' => 'vertegenwoordiger_email', 'value' => $email, 'compare' => '=']
		]
	]);
	
	if (!empty($existing_email)) {
		$duplicates[] = 'e-mail';
		$duplicate_emails[] = $email; // Store the duplicate email
	}
	
	// If both name and email are found, modify the reason to show both
	if (!empty($existing_name) && !empty($existing_email)) {
		$duplicate_reasons[] = 'naam en e-mail al bestaan';
	} else {
		// Otherwise, use the individual reasons
		if (!empty($existing_name)) {
			$duplicate_reasons[] = 'naam al bestaat';
		}
		if (!empty($existing_email)) {
			$duplicate_reasons[] = 'e-mail al bestaat';
		}
	}
	
	// If duplicates are found, log them in the URL
	if (!empty($duplicates)) {
		$duplicate_items = implode(', ', $duplicates); // naam|e-mail
		$duplicate_names_string = implode('|', $duplicate_names); // "John Doe|Jane Doe"
		$duplicate_emails_string = implode('|', $duplicate_emails); // "johndoe@example.com|janedoe@example.com"
		$duplicate_reasons_string = implode('|', $duplicate_reasons); // "naam|e-mail|naam en e-mail bestaan al"
		
		// Generate error url
		$url = add_query_arg([
			'error' => 'duplicate',
			'names' => urlencode($duplicate_names_string),
			'emails' => urlencode($duplicate_emails_string),
			'reasons' => urlencode($duplicate_reasons_string)
		], wp_get_referer());
		
		wp_redirect($url);
		exit;
	}
	
	return [
		'duplicate' => !empty($duplicates),
		'name' => $name, 'email' => $email,
		'reason' => implode(' en ', $duplicates) . ' bestaan al'
	];
}
