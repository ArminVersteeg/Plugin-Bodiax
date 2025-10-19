<?php
// IMPORTANT NOTE:
// Cant send emails yet, need to add mailserver into WordPress through another plugin eg.
// I didn't have the time to add that yet.
// 
// Plugin (just the mail tbh) should be fully functional after thats setup.

// === Create the table if it doesn't exist ===
function cr_create_customer_requests_table() {
	global $wpdb;
	$table_name = 'pmsi_customer_requests';
	$charset_collate = $wpdb->get_charset_collate();
	
	$sql = "CREATE TABLE IF NOT EXISTS $table_name (
				id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				name VARCHAR(255) NOT NULL,
				email VARCHAR(255) NOT NULL,
				zipcode VARCHAR(255) NOT NULL,
				message TEXT NOT NULL,
				dealer VARCHAR(255),
				created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
				PRIMARY KEY (id)
			) $charset_collate;";
	
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql);
}
register_activation_hook(__FILE__, 'cr_create_customer_requests_table');

// === Shortcode: Customer Request Form ===
function cr_request_form_shortcode() {
    global $wpdb;

    $customer_table = 'pmsi_customer_requests';
    $dealer_table = 'pmsi_dealers';

    // Get all dealers with lat/lon
    $dealers = $wpdb->get_results("SELECT * FROM $dealer_table WHERE lat IS NOT NULL AND lon IS NOT NULL");
    $dealer_data = [];
    foreach ($dealers as $d) {
        $dealer_data[] = [
            'id' => $d->id,
            'name' => $d->name,
            'address' => $d->full_address ?? '',
            'lat' => $d->lat,
            'lon' => $d->lon,
            'email' => $d->email,
        ];
    }

    ob_start();

    // Handle form submission
    if (isset($_POST['cr_submit_request'])) {
        $name = sanitize_text_field($_POST['cr_name']);
        $email = sanitize_email($_POST['cr_email']);
        $zipcode = sanitize_text_field($_POST['cr_zipcode']);
        $message = sanitize_textarea_field($_POST['cr_message']);
        $dealer_id = intval($_POST['cr_selected_dealer']);

        if ($dealer_id <= 0) {
            echo '<p style="color:red;">Selecteer een dealer voordat u het formulier verzendt.</p>';
        } else {
            // Save to database including dealer_id
            $wpdb->insert($customer_table, [
                'name' => $name,
                'email' => $email,
                'zipcode' => $zipcode,
                'message' => $message,
                'dealer' => $dealer_id
            ]);

            // Send email to stagiair instead of actual dealer, to test
            $to = 'stagiair-development@heditex.com'; // Change to eventually send it to the selected dealer's email
            $subject = 'Nieuw klantenverzoek ontvangen';
            $body = "Er is een nieuw klantenverzoek ontvangen:\n\n";
            $body .= "Naam: $name\n";
            $body .= "Email: $email\n";
            $body .= "Postcode: $zipcode\n";
            $body .= "Bericht:\n$message\n\n";

            // Include dealer info for reference
            $dealer = $wpdb->get_row($wpdb->prepare("SELECT name, address FROM $dealer_table WHERE id=%d", $dealer_id));
            if ($dealer) {
                $body .= "Geselecteerde dealer: {$dealer->name}\nAdres: {$dealer->address}";
            }

			// Send final email to the selected dealer
            $headers = ['Content-Type: text/plain; charset=UTF-8'];
            wp_mail($to, $subject, $body, $headers);

            echo '<p style="color:green;">Uw verzoek is verzonden naar de geselecteerde dealer.</p>';
        }
    }

    ?>
    <form id="cr-request-form" method="post" style="margin-bottom:20px;">
        <input placeholder="Naam*" type="text" name="cr_name" required>
        <input placeholder="Email*" type="email" name="cr_email" required>
        <input placeholder="Postcode*" type="text" id="cr-zipcode" name="cr_zipcode" required>
        <textarea placeholder="Stel hier uw vraag..." name="cr_message"></textarea>

        <!-- Hidden input for selected dealer -->
        <input type="hidden" name="cr_selected_dealer" id="cr-selected-dealer" required>

        <div id="nearest-dealers" class="dealer-cards" style="margin-top:15px;"></div>

        <button type="submit" name="cr_submit_request" class="custom-button" style="margin-top:10px;">Verstuur</button>
    </form>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const dealers = <?php echo json_encode($dealer_data); ?>;

        function getDistance(lat1, lon1, lat2, lon2) {
            const R = 6371;
            const dLat = (lat2-lat1) * Math.PI/180;
            const dLon = (lon2-lon1) * Math.PI/180;
            const a = Math.sin(dLat/2)**2 + Math.cos(lat1*Math.PI/180)*Math.cos(lat2*Math.PI/180)*Math.sin(dLon/2)**2;
            return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        }

        function renderNearestDealers(userLat, userLon) {
            const nearest = dealers.map(d => ({
                ...d,
                distance: getDistance(userLat, userLon, parseFloat(d.lat), parseFloat(d.lon))
            })).sort((a,b) => a.distance-b.distance).slice(0,5);

            const container = document.getElementById('nearest-dealers');
            container.innerHTML = nearest.map(d => `
                <div class="dealer-card" data-id="${d.id}" style="border:2px solid #ccc; padding:10px; margin-bottom:10px; cursor:pointer; border-radius:5px; transition: border 0.2s;">
                    <h4>${d.name}</h4>
                    <p>${d.address}</p>
                    <p>Afstand: ${d.distance.toFixed(1)} km</p>
                </div>
            `).join('');

            const dealerCards = container.querySelectorAll('.dealer-card');
            dealerCards.forEach(card => {
                card.addEventListener('click', function() {
                    // Remove selected style from other cards
                    dealerCards.forEach(c => c.style.border = "2px solid #ccc");

                    // Add selected style to clicked card
                    this.style.border = "3px solid #0073aa"; // thick blue border

                    // Update hidden input for form submission
                    document.getElementById('cr-selected-dealer').value = this.dataset.id;
                });
            });
        }

        const zipcodeInput = document.getElementById('cr-zipcode');
        zipcodeInput.addEventListener('blur', function() {
            const zipcode = this.value.trim();
            if (!zipcode) return;

            fetch("https://nominatim.openstreetmap.org/search?format=json&limit=1&q=" + encodeURIComponent(zipcode + " Netherlands"))
                .then(res => res.json())
                .then(data => {
                    if (!data.length) {
                        alert("Geen locatie gevonden voor deze postcode.");
                        return;
                    }
                    const userLat = parseFloat(data[0].lat);
                    const userLon = parseFloat(data[0].lon);
                    renderNearestDealers(userLat, userLon);
                })
                .catch(err => console.error(err));
        });
    });
    </script>
    <?php
	
    return ob_get_clean();
}
add_shortcode('customer_request_form', 'cr_request_form_shortcode');

// === Shortcode: Dealer Requests (Read Out) ===
// === Shortcode: Dealer Requests (Read Out) with Dealer Info ===
function cr_dealer_requests_shortcode() {
    global $wpdb;
    $customer_table = 'pmsi_customer_requests';
    $dealer_table = 'pmsi_dealers';

    $requests = $wpdb->get_results("SELECT * FROM $customer_table ORDER BY created_at DESC");

    ob_start();

    if ($requests) {
        echo '<div class="cr-entries">';

        $today = new DateTime('today');

        foreach ($requests as $req) {
            $dt = new DateTime($req->created_at);
            $date = $dt->format('d-m-Y');
            $time = $dt->format('H:i');
            $display = ($dt->format('Y-m-d') === $today->format('Y-m-d')) ? $time : $date;

            // Get dealer info
            $dealer_info = $wpdb->get_row($wpdb->prepare("SELECT name FROM $dealer_table WHERE id=%d", $req->dealer));
            $dealer_name = $dealer_info ? $dealer_info->name : 'Onbekend';

			echo '<div class="cr-entry" data-name="' . esc_attr($req->name) . '" data-email="' . esc_attr($req->email) . '" data-zipcode="' . esc_attr($req->zipcode) . '" data-message="' . esc_attr($req->message) . '" data-date="' . esc_attr($date) . '" data-time="' . esc_attr($time) . '" data-dealer-name="' . esc_attr($dealer_name) . '">';
			
			echo '<div class="entry-block1">'; echo '<strong>' . esc_html($req->name) . '</strong>';
			echo '<p class="cr-message">' . esc_html($req->message) . '</p>';
			echo '</div>';
			echo '<div class="entry-block2">';
			echo '<p>' . esc_html($display) . '</p>'; 
			echo '</div>';
			echo '</div>';
			
        }

        echo '</div>';
    } else {
        echo '<p>Geen berichten ontvangen.</p>';
    }

    ?>
    <!-- Lightbox Modal -->
    <div id="cr-modal" style="display:none;">
        <div class="cr-modal-content">
            <span id="cr-modal-close">&times;</span>
            <h3>Klantenverzoek</h3>
            <p><strong>Naam Klant:</strong> <span id="cr-modal-name"></span></p>
            <p><strong>Email Klant:</strong> <span id="cr-modal-email"></span></p>
            <p><strong>Postcode Klant:</strong> <span id="cr-modal-zipcode"></span></p>
            <p style="margin-bottom: 0px;"><strong>Bericht:</strong></p>
            <p id="cr-modal-message"></p>
            <p><strong>Geselecteerde dealer:</strong> <span id="cr-modal-dealer"></span></p>
            <p><strong>Aangevraagd op:</strong> <span id="cr-modal-date"></span>, <span id="cr-modal-time"></span></p>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('cr-modal');
            const closeBtn = document.getElementById('cr-modal-close');

            document.querySelectorAll('.cr-entry').forEach(entry => {
                entry.addEventListener('click', function() {
                    document.getElementById('cr-modal-name').textContent = this.dataset.name;
                    document.getElementById('cr-modal-email').textContent = this.dataset.email;
                    document.getElementById('cr-modal-zipcode').textContent = this.dataset.zipcode;
                    document.getElementById('cr-modal-message').textContent = this.dataset.message;
                    document.getElementById('cr-modal-date').textContent = this.dataset.date;
                    document.getElementById('cr-modal-time').textContent = this.dataset.time;
                    document.getElementById('cr-modal-dealer').textContent = this.dataset.dealerName;
                    modal.style.display = 'flex';
                });
            });

            closeBtn.addEventListener('click', () => modal.style.display = 'none');
            modal.addEventListener('click', e => {
                if (e.target === modal) modal.style.display = 'none';
            });
        });
    </script>
    <?php

    return ob_get_clean();
}
add_shortcode('dealer_requests', 'cr_dealer_requests_shortcode');

