<?php
// ====================== DATABASE TABLE ======================
function cr_create_dealers_table() {
    global $wpdb;
    $table_name = 'pmsi_dealers';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
				id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				name VARCHAR(255) NOT NULL,
				site VARCHAR(255),
				phone VARCHAR(50),
				email VARCHAR(50),
				street VARCHAR(255),
				house_number VARCHAR(50),
				zipcode VARCHAR(50),
				city VARCHAR(100),
				full_address VARCHAR(255),
				lat DECIMAL(10,7),
				lon DECIMAL(10,7),
				created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
				PRIMARY KEY (id)
			) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'cr_create_dealers_table');


// ====================== SHORTCODES ======================
// === Buttons + CSV Upload ===
function toggle_dealer_buttons_and_containers() {
	// Build button container
    ob_start(); ?>
    <div class="toggle-buttons-container">
        <button id="toggle-create" class="toggle-button custom-button">Nieuw</button>
        <form class="csv-upload-form" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="process_dealer_csv_upload">
            <?php wp_nonce_field('dealer_csv_upload_action', 'csv_upload_nonce'); ?>
            <input type="file" style="display: none;" name="csv_file" id="csv_file" required>
            <button class="toggle-button custom-button" type="button" id="upload-button">
				<!-- Upload icon SVG -->
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


// === Dealer Create Form ===
function dealer_form_shortcode() {
    ob_start(); ?>
    <div id="create-container" class="toggle-container">
        <form class="dealer-form" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST">
            <input type="hidden" name="action" value="dealer_create">
            <?php wp_nonce_field('dealer_create_action', 'dealer_nonce'); ?>
            <div class="dealer-form-row">
                <input class="dealer-input-50" placeholder="Bedrijfsnaam" type="text" name="dealer_name" required>
                <input class="dealer-input-50" placeholder="Website" type="text" name="dealer_site">
            </div>
            <div class="dealer-form-row">
                <input class="dealer-input-75" placeholder="Straatnaam" type="text" name="dealer_street" required>
                <input class="dealer-input-25" placeholder="Huisnr" type="text" name="dealer_house_number" required>
            </div>
            <div class="dealer-form-row">
                <input class="dealer-input-50" placeholder="Postcode" type="text" name="dealer_zipcode" required>
                <input class="dealer-input-50" placeholder="Woonplaats" type="text" name="dealer_city" required>
            </div>
            <div class="dealer-form-row">
				<input class="dealer-input-50" placeholder="E-mail" name="dealer_email" required>
                <input class="dealer-input-50" placeholder="Tel nr" type="tel" name="dealer_tel" required>
            </div>
            <div class="form-group-dealer-button">
                <button class="custom-button" type="submit">Toevoegen</button>
            </div>
        </form>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('dealer_form', 'dealer_form_shortcode');


// === Update Shortcode ===
function edit_dealer_shortcode() {
    if (!is_user_logged_in()) return '<p>Je moet ingelogd zijn om dit te bewerken.</p>';

    if (!isset($_GET['id'])) return '<p>Geen dealer geselecteerd.</p>';
    $id = intval($_GET['id']);
    
    global $wpdb;
    $table = 'pmsi_dealers';
    $dealer = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id));
    if (!$dealer) return '<p>Dealer niet gevonden.</p>';

    // Extract dealer fields
    $name = $dealer->name;
    $website = $dealer->site;
    $street = $dealer->street;
    $houseNo = $dealer->house_number;
    $zipcode = $dealer->zipcode;
    $city = $dealer->city;
    $phone = $dealer->phone;
	$email = $dealer->email;

    ob_start(); ?>
    <form class="dealer-form" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST">
        <input type="hidden" name="action" value="dealer_update">
        <input type="hidden" name="id" value="<?php echo esc_attr($id); ?>">
        <?php wp_nonce_field('dealer_update_action_' . $id, 'dealer_nonce'); ?>

        <div class="dealer-form-row">
            <input class="dealer-input-50" placeholder="Bedrijfsnaam" type="text" name="dealer_name" value="<?php echo esc_attr($name); ?>" required>
            <input class="dealer-input-50" placeholder="Website" type="text" name="dealer_site" value="<?php echo esc_attr($website); ?>" required>
        </div>
        <div class="dealer-form-row">
            <input class="dealer-input-75" placeholder="Straatnaam" type="text" name="dealer_street" value="<?php echo esc_attr($street); ?>" required>
            <input class="dealer-input-25" placeholder="Huisnr" type="text" name="dealer_house_number" value="<?php echo esc_attr($houseNo); ?>" required>
        </div>
        <div class="dealer-form-row">
            <input class="dealer-input-50" placeholder="Postcode" type="text" name="dealer_zipcode" value="<?php echo esc_attr($zipcode); ?>" required>
            <input class="dealer-input-50" placeholder="Woonplaats" type="text" name="dealer_city" value="<?php echo esc_attr($city); ?>" required>
        </div>
        <div class="dealer-form-row">
			<input class="dealer-input-50" placeholder="E-mail" name="dealer_email" value="<?php echo esc_attr($email); ?>" required>
            <input class="dealer-input-50" placeholder="Tel nr" type="tel" name="dealer_phone" value="<?php echo esc_attr($phone); ?>" required>
        </div>
        <div class="form-group-dealer-button">
            <button class="custom-button" type="submit">Bijwerken</button>
        </div>
    </form>
    <?php
    return ob_get_clean();
}
add_shortcode('edit_dealer', 'edit_dealer_shortcode');


// === Dealer Table HTML ===
function dealer_table_html($entries) {
    if (!$entries) return '<table class="dealer-table" style="border-collapse: collapse;">
								<thead>
									<tr class="vertegenwoordigers-table-row">
										<th style="width: 25%;">Naam</th>
										<th style="width: 20%;">Telnr</th>
										<th style="width: 20%;">E-mail</th>
										<th style="width: 30%;">Adres</th>
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
						<th style="width: 20%;">Telnr</th>
						<th style="width: 20%;">E-mail</th>
						<th style="width: 30%;">Adres</th>
						<th style="width: 5%;"></th>
                    </tr>
                </thead>
                <tbody>';

    foreach ($entries as $entry) {
        $edit_url = add_query_arg(['id' => $entry->id], site_url('/dealer-bewerken'));
        $delete_url = wp_nonce_url(admin_url('admin-post.php?action=dealer_delete&id=' . $entry->id), 'delete_dealer_' . $entry->id);

        $html .= '<tr>';
        if (!empty($entry->site)) {
            $html .= '<td class="dealer-name">
						<a href="https://' . esc_attr($entry->site) . '" target="_blank" rel="noopener noreferrer">' . esc_html($entry->name) . '</a>
					</td>';
        } else {
            $html .= '<td class="dealer-name">' . esc_html($entry->name) . '</td>';
        }
        $html .= '<td class="dealer-phone"><a href="tel:' . esc_attr($entry->phone) . '">' . esc_html($entry->phone) . '</a></td>';
		$html .= '<td class="dealer-email"><a href="mailto:' . esc_attr($entry->email) . '">' . esc_html($entry->email) . '</a></td>';
        $html .= '<td class="dealer-address"><a href="' . esc_url(site_url('/dealer-kaart') . '?address=' . urlencode($entry->full_address)) . '">' . esc_html($entry->full_address) . '</a></td>';
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


// === List Dealers Shortcode ===
function list_dealers_shortcode() {
    global $wpdb;
    $table = 'pmsi_dealers';
    $per_page = 20;
    $current_page = 1;

    // Count total entries for pagination
    $total = $wpdb->get_var("SELECT COUNT(*) FROM $table");
    $total_pages = ceil($total / $per_page);

    // Fetch first page
    $offset = ($current_page - 1) * $per_page;
    $entries = $wpdb->get_results("SELECT * FROM $table ORDER BY created_at DESC LIMIT $offset, $per_page");

    // Build table HTML
    $table_html = dealer_table_html($entries);

    // Build ellipsis-style pagination HTML
    $pagination_html = '';
    if ($total_pages > 1) {
        $pagination_html .= '<div class="dealer-pagination">';

        if ($current_page > 1) {
            $pagination_html .= '<button class="pagination-page-btn" data-page="' . ($current_page - 1) . '">‹</button>';
        }

        if ($current_page > 2) {
            $pagination_html .= '<button class="pagination-page-btn" data-page="1">1</button>';
            if ($current_page > 3) {
                $pagination_html .= '<span class="dots">...</span>';
            }
        }

        for ($i = max(1, $current_page - 2); $i <= min($total_pages, $current_page + 2); $i++) {
            if ($i == $current_page) {
                $pagination_html .= '<button class="current" disabled>' . $i . '</button>';
            } else {
                $pagination_html .= '<button class="pagination-page-btn" data-page="' . $i . '">' . $i . '</button>';
            }
        }

        if ($current_page < $total_pages - 1) {
            if ($current_page < $total_pages - 2) {
                $pagination_html .= '<span class="dots">...</span>';
            }
            $pagination_html .= '<button class="pagination-page-btn" data-page="' . $total_pages . '">' . $total_pages . '</button>';
        }

        if ($current_page < $total_pages) {
            $pagination_html .= '<button class="pagination-page-btn" data-page="' . ($current_page + 1) . '">›</button>';
        }

        $pagination_html .= '</div>';
    }

    // Output container + table + pagination
    $output = '<div class="dealer-spinner-container">
                    <div id="custom-spinner" style="display: none;"></div>
               </div>
               <div class="dealer-search-container">
                    <input type="text" id="ajax-search" placeholder="Zoeken..." />
               </div>
               <div id="ajax-results" data-action="search_dealers">' 
                    . $table_html . $pagination_html .
               '</div>';

    return $output;
}
add_shortcode('list_dealers', 'list_dealers_shortcode');


// ====================== CREATE / UPDATE / DELETE HANDLERS ======================
// === Create ===
add_action('admin_post_dealer_create', 'handle_dealer_create');
add_action('admin_post_nopriv_dealer_create', 'handle_dealer_create');
function handle_dealer_create() {
    global $wpdb;
    $table = 'pmsi_dealers';

    if (!isset($_POST['dealer_nonce']) || !wp_verify_nonce($_POST['dealer_nonce'], 'dealer_create_action')) wp_die('Beveiligingsfout');

    $name = sanitize_text_field($_POST['dealer_name']);
    $site = sanitize_text_field($_POST['dealer_site']);
    $street = sanitize_text_field($_POST['dealer_street']);
    $houseNo = sanitize_text_field($_POST['dealer_house_number']);
    $zipcode = sanitize_text_field($_POST['dealer_zipcode']);
    $city = sanitize_text_field($_POST['dealer_city']);
    $phone = sanitize_text_field($_POST['dealer_tel']);
	$email = sanitize_text_field($_POST['dealer_email']);
    $address = trim($street . ' ' . $houseNo) . ', ' . trim($zipcode . ' ' . $city);

    // Geocode
    $lat = $lon = null;
    $geocode_url = 'https://nominatim.openstreetmap.org/search?format=json&limit=1&q=' . urlencode($address);
    $response = wp_remote_get($geocode_url, [
        'headers' => ['User-Agent' => 'MyWordPressPlugin/1.0'],
        'timeout' => 10
    ]);
    if (!is_wp_error($response)) {
        $data_geo = json_decode(wp_remote_retrieve_body($response), true);
        if (!empty($data_geo)) {
            $lat = $data_geo[0]['lat'];
            $lon = $data_geo[0]['lon'];
        }
    }

    $wpdb->insert($table, [
        'name' => $name,
        'site' => $site,
        'phone' => $phone,
		'email' => $email,
        'street' => $street,
        'house_number' => $houseNo,
        'zipcode' => $zipcode,
        'city' => $city,
        'full_address' => $address,
        'lat' => $lat,
        'lon' => $lon
    ]);

    wp_redirect(home_url('/dealers'));
    exit;
}


// === Update ===
add_action('admin_post_dealer_update', 'handle_dealer_update');
function handle_dealer_update() {
    global $wpdb;
    $table = 'pmsi_dealers';
    $id = intval($_POST['id'] ?? 0);

    if (!$id || !isset($_POST['dealer_nonce']) || !wp_verify_nonce($_POST['dealer_nonce'], 'dealer_update_action_' . $id)) wp_die('Beveiligingsfout');

    $name = sanitize_text_field($_POST['dealer_name']);
    $site = sanitize_text_field($_POST['dealer_site']);
    $street = sanitize_text_field($_POST['dealer_street']);
    $houseNo = sanitize_text_field($_POST['dealer_house_number']);
    $zipcode = sanitize_text_field($_POST['dealer_zipcode']);
    $city = sanitize_text_field($_POST['dealer_city']);
    $phone = sanitize_text_field($_POST['dealer_tel']);
	$email = sanitize_text_field($_POST['dealer_email']);
    $address = trim($street . ' ' . $houseNo) . ', ' . trim($zipcode . ' ' . $city);

    // Geocode
    $lat = $lon = null;
    $geocode_url = 'https://nominatim.openstreetmap.org/search?format=json&limit=1&q=' . urlencode($address);
    $response = wp_remote_get($geocode_url, [
        'headers' => ['User-Agent' => 'MyWordPressPlugin/1.0'],
        'timeout' => 10
    ]);
    if (!is_wp_error($response)) {
        $data_geo = json_decode(wp_remote_retrieve_body($response), true);
        if (!empty($data_geo)) {
            $lat = $data_geo[0]['lat'];
            $lon = $data_geo[0]['lon'];
        }
    }

    $wpdb->update($table, [
        'name' => $name,
        'site' => $site,
        'phone' => $phone,
		'email' => $email,
        'street' => $street,
        'house_number' => $houseNo,
        'zipcode' => $zipcode,
        'city' => $city,
        'full_address' => $address,
        'lat' => $lat,
        'lon' => $lon
    ], ['id' => $id]);

    wp_redirect(home_url('/dealers'));
    exit;
}


// === Delete ===
add_action('admin_post_dealer_delete', 'handle_dealer_delete');
function handle_dealer_delete() {
    global $wpdb;
    $table = 'pmsi_dealers';
    $id = intval($_GET['id'] ?? 0);
    $nonce = $_GET['_wpnonce'] ?? '';

    if (!$id || !wp_verify_nonce($nonce, 'delete_dealer_' . $id)) wp_die('Ongeldige nonce');

    $wpdb->delete($table, ['id' => $id]);
    wp_redirect(home_url('/dealers'));
    exit;
}


// ====================== AJAX ======================
add_action('wp_ajax_search_dealers', 'search_dealers_ajax');
add_action('wp_ajax_nopriv_search_dealers', 'search_dealers_ajax');

function search_dealers_ajax() {
    global $wpdb;
    $table = 'pmsi_dealers';

    $search = sanitize_text_field($_POST['search'] ?? '');
    $page = max(1, intval($_POST['page'] ?? 1));
    $per_page = 20;
    $offset = ($page - 1) * $per_page;

    if ($search) {
        $like = '%' . $wpdb->esc_like($search) . '%';
        $entries = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table
                 WHERE name LIKE %s OR phone LIKE %s OR site LIKE %s OR full_address LIKE %s
                 ORDER BY created_at DESC
                 LIMIT %d, %d",
                 $like, $like, $like, $like, $offset, $per_page
            )
        );

        $total = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM $table
                 WHERE name LIKE %s OR phone LIKE %s OR site LIKE %s OR full_address LIKE %s",
                 $like, $like, $like, $like
            )
        );
    } else {
        $entries = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table
                 ORDER BY created_at DESC
                 LIMIT %d, %d",
                 $offset, $per_page
            )
        );
        $total = $wpdb->get_var("SELECT COUNT(*) FROM $table");
    }

    echo dealer_table_html($entries);

    // Pagination
	$total_pages = ceil($total / $per_page);
	if ($total_pages > 1) {
		echo '<div class="dealer-pagination">';
		
		// Previous button
		if ($page > 1) echo '<button class="pagination-page-btn" data-page="' . ($page - 1) . '">‹</button>';
		
		// First page
		if ($page > 2) {
			echo '<button class="pagination-page-btn" data-page="1">1</button>';
			if ($page > 3) echo '<span class="dots">...</span>';
		}
		
		// Other pages
		for ($i = max(1, $page - 1); $i <= min($total_pages, $page + 1); $i++) {
			if ($i == $page) echo '<button class="current" disabled>' . $i . '</button>';
			else echo '<button class="pagination-page-btn" data-page="' . $i . '">' . $i . '</button>';
		}
		
		// Last page
		if ($page < $total_pages - 1) {
			if ($page < $total_pages - 2) echo '<span class="dots">...</span>';
			echo '<button class="pagination-page-btn" data-page="' . $total_pages . '">' . $total_pages . '</button>';
		}
		
		// Next button
		if ($page < $total_pages) echo '<button class="pagination-page-btn" data-page="' . ($page + 1) . '">›</button>';
		
		echo '</div>';
	}

    wp_die();
}


// ====================== CSV UPLOAD ======================
add_action('admin_post_process_dealer_csv_upload', 'handle_dealer_csv_upload');
function handle_dealer_csv_upload() {
    global $wpdb;
    $table = 'pmsi_dealers';

    if (!isset($_POST['csv_upload_nonce']) || !wp_verify_nonce($_POST['csv_upload_nonce'], 'dealer_csv_upload_action')) wp_die('Security check failed.');
    if (empty($_FILES['csv_file']['tmp_name'])) wp_die('No file uploaded.');

    $file = $_FILES['csv_file'];
    if ($file['type'] !== 'text/csv' && $file['type'] !== 'application/vnd.ms-excel') wp_die('Ongeldig bestandstype.');

    $handle = fopen($file['tmp_name'], 'r');
    $row = 0;

    if ($handle !== false) {
        while (($data = fgetcsv($handle, 1000, ';')) !== false) {
            if ($row === 0) { $row++; continue; }

            $name = sanitize_text_field($data[1]);
            $website = sanitize_text_field($data[6]);
            $phone = sanitize_text_field($data[7]);
			// $email = sanitize_text_field($data['?']); Change this when Laura updates the CSV file layout
            $street = sanitize_text_field($data[2]);
            $houseNo = sanitize_text_field($data[3]);
            $zipcode = sanitize_text_field($data[4]);
            $city = sanitize_text_field($data[5]);
            $address = trim($street . ' ' . $houseNo) . ', ' . trim($zipcode . ', ' . $city);

            // Geocode
            $lat = $lon = null;
            $geocode_url = 'https://nominatim.openstreetmap.org/search?format=json&limit=1&q=' . urlencode($address);
            $response = wp_remote_get($geocode_url, [
                'headers' => ['User-Agent' => 'MyWordPressPlugin/1.0'],
                'timeout' => 10
            ]);
            if (!is_wp_error($response)) {
                $data_geo = json_decode(wp_remote_retrieve_body($response), true);
                if (!empty($data_geo)) {
                    $lat = $data_geo[0]['lat'];
                    $lon = $data_geo[0]['lon'];
                }
            }

            $wpdb->insert($table, [
                'name' => $name,
                'site' => $website,
                'phone' => $phone,
				'email' => $email,
                'street' => $street,
                'house_number' => $houseNo,
                'zipcode' => $zipcode,
                'city' => $city,
                'full_address' => $address,
                'lat' => $lat,
                'lon' => $lon
            ]);

            $row++;
        }
        fclose($handle);
    }
	
    wp_redirect($_SERVER['HTTP_REFERER']);
    exit;
}
