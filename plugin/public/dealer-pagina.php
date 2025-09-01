<?php
// =============== CRUD =================
// === NEW + UPLOAD BUTTONS SHORTCODE ===
function toggle_dealer_buttons_and_containers() {
	ob_start(); ?>
	<div class="toggle-buttons-container">
		<button id="toggle-create" class="toggle-button custom-button" type="button">Nieuw</button>
		<form class="csv-upload-form" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST" enctype="multipart/form-data">
			<input type="hidden" name="action" value="process_dealer_csv_upload">
			<?php wp_nonce_field('dealer_csv_upload_action', 'csv_upload_nonce'); ?>
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
add_shortcode('toggle_dealer_buttons_and_containers', 'toggle_dealer_buttons_and_containers');

// === CREATE FORM SHORTCODE ===
function dealer_form_shortcode() {
	ob_start(); ?>
	<div id="create-container" class="toggle-container" style="display:none;">
		<form class="dealer-form" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST">
			<input type="hidden" name="action" value="dealer_create">
			<?php wp_nonce_field('dealer_create_action', 'dealer_nonce'); ?>
			<div class="form-group-dealer">
				<input placeholder="Naam" type="text" name="dealer_name" required>
			</div>
			<div class="form-group-dealer">
				<input placeholder="E-mail" type="email" name="dealer_email" required>
			</div>
			<div class="form-group-dealer">
				<input placeholder="Adres" type="text" name="dealer_address" required>
			</div>
			<div class="form-group-dealer-button">
				<button class="custom-button" type="submit">Toevoegen</button>
			</div>
		</form>
	</div>
	<?php return ob_get_clean();
}
add_shortcode('dealer_form', 'dealer_form_shortcode');

// === DEALER TABLE ===
function dealer_table_html($entries) {
	if (!$entries) return '<table class="dealer-table" style="border-collapse: collapse;">
								<thead>
									<tr class="dealer-table-row">
										<th style="width: 25%;">Naam</th>
										<th style="width: 30%;">E-mail</th>
										<th style="width: 40%;">Adres</th>
										<th style="width: 5%;"></th>
									</tr>
								</thead>
								<tbody>
								</tbody>
								</table>
								<div class="error-container">
									<p class="error-message">Geen dealers gevonden.</p>
								</div>';

	$html = '<table class="dealer-table" style="border-collapse: collapse;">
				<thead>
					<tr class="dealer-table-row">
						<th style="width: 25%;">Naam</th>
						<th style="width: 30%;">E-mail</th>
						<th style="width: 40%;">Adres</th>
						<th style="width: 5%;"></th>
					</tr>
					</thead>
					<tbody>';

	foreach ($entries as $entry) {
		$name = get_post_meta($entry->ID, 'dealer_name', true);
		$email = get_post_meta($entry->ID, 'dealer_email', true);
		$address = get_post_meta($entry->ID, 'dealer_address', true);
		$edit_url = add_query_arg(['id' => $entry->ID], site_url('/dealer-bewerken'));
		$delete_url = wp_nonce_url(admin_url('admin-post.php?action=dealer_delete&id=' . $entry->ID), 'delete_dealer_' . $entry->ID);

		$html .= '<tr>';
		$html .= '<td class="dealer-name">' . esc_html($name) . '</td>';
		$html .= '<td class="dealer-email"><a class="table-mail-link" href="mailto:' . esc_attr($email) . '">' . esc_html($email) . '</a></td>';
		$html .= '<td class="dealer-adres">' . esc_html($address) . '</td>';
		$html .= '<td class="dealer-menu">
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
function list_dealers_shortcode() {
	$entries = get_posts([
		'post_type' => 'dealer',
		'numberposts' => 10,
		'paged' => 1
	]);
	
	$output = '<div class="dealer-spinner-container">
				<div id="custom-spinner" style="display: none;"></div>
			</div>
			<div class="dealer-search-container">
				<input type="text" id="ajax-search" placeholder="Zoeken..." />
			</div>
			<div id="ajax-results" data-action="search_dealers">' 
				. dealer_table_html($entries) .
			'</div>';
	
	return $output;
}
add_shortcode('list_dealers', 'list_dealers_shortcode');

// === UPDATE SHORTCODE ===
function edit_dealer_shortcode($atts) {
	$atts = shortcode_atts(['id' => 0], $atts);
	$id = isset($_GET['id']) ? intval($_GET['id']) : intval($atts['id']);

	// Check if post ID is invalid
	if (!$id || get_post_type($id) !== 'dealer') {
		return 'Ongeldig ID';
	}

	$post = get_post($id);
	$name = get_post_meta($id, 'dealer_name', true);
	$email = get_post_meta($id, 'dealer_email', true);
	$address = get_post_meta($id, 'dealer_address', true);

	// Build form
	ob_start(); ?>
	<form class="dealer-form" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST">
		<input type="hidden" name="action" value="dealer_update">
		<input type="hidden" name="id" value="<?php echo esc_attr($id); ?>">
		<?php wp_nonce_field('dealer_update_action_' . $id, 'dealer_nonce'); ?>
		<div class="form-group-dealer">
			<label>Update de gegevens:</label>
			<input placeholder="Naam" type="text" name="dealer_name" value="<?php echo esc_attr($name); ?>" required>
		</div>
		<div class="form-group-dealer">
			<input placeholder="E-mail" type="email" name="dealer_email" value="<?php echo esc_attr($email); ?>" required>
		</div>
		<div class="form-group-dealer">
			<input placeholder="Adres" type="text" name="dealer_address" value="<?php echo esc_attr($address); ?>" required>
		</div>
		<div class="form-group-dealer-button">
			<button class="custom-button" type="submit">Toevoegen</button>
		</div>
	</form>
	<?php return ob_get_clean();
}
add_shortcode('edit_dealer', 'edit_dealer_shortcode');

// === CREATE HANDLER ===
add_action('admin_post_dealer_create', 'handle_dealer_create');
add_action('admin_post_nopriv_dealer_create', 'handle_dealer_create');

function handle_dealer_create() {
	// Validate nonce: if invalid, stop
	if (!isset($_POST['dealer_nonce']) || !wp_verify_nonce($_POST['dealer_nonce'], 'dealer_create_action')) {
		wp_die('Beveiligingsfout');
	}

	$name = sanitize_text_field($_POST['dealer_name']);
	$email = sanitize_email($_POST['dealer_email']);
	$address = sanitize_text_field($_POST['dealer_address']);

	// Validate input fields: name, email, address. If invalid, stop
	if (!$name || !is_email($email) || !$address) {
		wp_die('Ongeldige invoer');
	}

	// Run duplicates check
	$check = check_dealer_duplicates($name, $email);
	$skipped = [];
	
	if ($check['duplicate']) {
		// Safely encode duplicates using "|" as separator
		$error_items = implode('|', $check['duplicates']);
		
		$redirect_url = home_url('/dealers/');
		$redirect_url = add_query_arg('error', 'duplicate', $redirect_url);
		$redirect_url = add_query_arg('skipped', urlencode($error_items), $redirect_url);
		
		wp_redirect($redirect_url);
		exit;
	}

	// Add post to custom post type 'dealer'
	$post_id = wp_insert_post([
		'post_type' => 'dealer',
		'post_title' => $name,
		'post_status' => 'publish'
	]);

	if ($post_id) {
		update_post_meta($post_id, 'dealer_name', $name);
		update_post_meta($post_id, 'dealer_email', $email);
		update_post_meta($post_id, 'dealer_address', $address);
	}

	wp_redirect(home_url('/dealers'));
	exit;
}

// === UPDATE HANDLER ===
add_action('admin_post_dealer_update', 'handle_dealer_update');

function handle_dealer_update() {
	$id = intval($_POST['id'] ?? 0);

	// Validate ID: if invalid, stop
	if (!$id || get_post_type($id) !== 'dealer') {
		wp_die('Ongeldig ID');
	}

	// Validate nonce: if invalid, stop
	if (!isset($_POST['dealer_nonce']) || !wp_verify_nonce($_POST['dealer_nonce'], 'dealer_update_action_' . $id)) {
		wp_die('Beveiligingsfout');
	}

	$name = sanitize_text_field($_POST['dealer_name']);
	$email = sanitize_email($_POST['dealer_email']);
	$address = sanitize_text_field($_POST['dealer_address']);

	// Update post with new information
	update_post_meta($id, 'dealer_name', $name);
	update_post_meta($id, 'dealer_email', $email);
	update_post_meta($id, 'dealer_address', $address);

	wp_redirect(home_url('/dealers'));
	exit;
}

// === DELETE HANDLER ===
add_action('admin_post_dealer_delete', 'handle_dealer_delete');

function handle_dealer_delete() {
	// Validate ID: if invalid, stop
	if (!isset($_GET['id'])) {
		wp_die('Geen ID opgegeven');
	}

	$id = intval($_GET['id'] ?? 0);
	$nonce = $_GET['_wpnonce'];

	// Validate nonce: if invalid, stop
	if (!wp_verify_nonce($nonce, 'delete_dealer_' . $id)) {
		wp_die('Ongeldige nonce');
	}

	// Delete post and send user back to 'dealers' page
	wp_delete_post($id, true);
	wp_redirect(home_url('/dealers'));
	exit;
}

// ================== AJAX ====================
// === AJAX HANDLER FOR SEARCH + PAGINATION ===
add_action('wp_ajax_search_dealers', 'search_dealers_ajax');
add_action('wp_ajax_nopriv_search_dealers', 'search_dealers_ajax');

function search_dealers_ajax() {
	$search = sanitize_text_field($_POST['search'] ?? '');
	$page = max(1, intval($_POST['page'] ?? 1));
	$per_page = 10;
	
	// Build the query arguments
	$query_args = [
		'post_type' => 'dealer',
		'posts_per_page' => $per_page,
		'paged' => $page,
		'meta_query' => [
			'relation' => 'OR',
			['key' => 'dealer_name', 'value' => $search, 'compare' => 'LIKE'],
			['key' => 'dealer_email', 'value' => $search, 'compare' => 'LIKE'],
			['key' => 'dealer_address', 'value' => $search, 'compare' => 'LIKE'],
		],
	];
	
	// Run the query
	$query = new WP_Query($query_args);
	
	// Build empty table if no results
	if (!$query->have_posts()) {
		$html = '<table class="dealer-table" style="border-collapse: collapse;">
					<thead>
						<tr class="dealer-table-row">
							<th style="width: 25%;">Naam</th>
							<th style="width: 30%;">E-mail</th>
							<th style="width: 40%;">Regio</th>
							<th style="width: 5%;"></th>
						</tr>
					</thead>
					<tbody>
					</tbody>
					</table>
					<div class="error-container">
						<p class="error-message">Geen dealers gevonden.</p>
					</div>';
		
		echo $html;
		wp_die();
	}
	
	// Output the results in table format
	echo dealer_table_html($query->posts);
	
	// Pagination
	$total = $query->found_posts;
	$total_pages = ceil($total / $per_page);
	
	if ($total_pages > 1) {
		echo '<div class="dealer-pagination">';
		
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

// =========== APPLY SEARCH FILTER ============
function filter_dealer_post_title($where, $wp_query) {
	if (!is_admin() && $wp_query->get('post_type') === 'dealer') {
		global $wpdb;
		
		// Get the search term
		$search = $wp_query->get('s');
		
		if (!empty($search)) {
			$where .= " AND {$wpdb->posts}.post_title LIKE '%" . esc_sql($search) . "%'";
		}
	}
	return $where;
}
add_filter('posts_where', 'filter_dealer_post_title', 10, 2);
// ============================================


// =============== CSV UPLOAD =================
// ============ PROCESS CSV FILE ==============
function handle_dealer_csv_upload() {
	// Check nonce for security
	if (!isset($_POST['csv_upload_nonce']) || !wp_verify_nonce($_POST['csv_upload_nonce'], 'dealer_csv_upload_action')) {
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

	$handle = fopen($file['tmp_name'], 'r');
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
			$name = sanitize_text_field($data[0]);
			$email = sanitize_email($data[1]);
			$address = sanitize_text_field($data[2]);

			// Check for duplicate name
			$existing_name = get_posts([
				'post_type' => 'dealer',
				'posts_per_page' => 1,
				'meta_query' => [
					['key' => 'dealer_name', 'value' => $name, 'compare' => '=']
				]
			]);
			
			// Check for duplicate email
			$existing_email = get_posts([
				'post_type' => 'dealer',
				'posts_per_page' => 1,
				'meta_query' => [
					['key' => 'dealer_email', 'value' => $email, 'compare' => '=']
				]
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

			// Insert the data into the custom post type 'dealer'
			$post_id = wp_insert_post([
				'post_type' => 'dealer',
				'post_title' => $name,
				'post_status' => 'publish',
				'post_date' => date('Y-m-d H:i:s', $unique_timestamp),
			]);

			if ($post_id) {
				update_post_meta($post_id, 'dealer_name', $name);
				update_post_meta($post_id, 'dealer_email', $email);
				update_post_meta($post_id, 'dealer_address', $address);
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
add_action('admin_post_process_dealer_csv_upload', 'handle_dealer_csv_upload');
// ==========================================


// ============ DUPLICATE CHECK =============
// === DUPLICATE NAME AND/OR E-MAIL CHECK ===
function check_dealer_duplicates($name, $email) {
	$duplicates = []; // List for what is duplicate
	$duplicate_names = []; // List for duplicate names
	$duplicate_emails = []; // List for duplicate emails
	$duplicate_reasons = []; // List for the reasons
	
	// Check for duplicate name
	$existing_name = get_posts([
		'post_type' => 'dealer',
		'posts_per_page' => 1,
		'meta_query' => [
			['key' => 'dealer_name', 'value' => $name, 'compare' => '=']
		],
	]);
	
	if (!empty($existing_name)) {
		$duplicates[] = 'naam';
		$duplicate_names[] = $name; // Store the duplicate name
	}
	
	// Check for duplicate email
	$existing_email = get_posts([
		'post_type' => 'dealer',
		'posts_per_page' => 1,
		'meta_query' => [
			['key' => 'dealer_email', 'value' => $email, 'compare' => '=']
		],
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
			'reasons' => urlencode($duplicate_reasons_string),
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