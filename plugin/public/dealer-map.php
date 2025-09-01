<?php
function dealer_map_shortcode() {
	$dealers = get_posts([
		'post_type'      => 'dealer',
		'posts_per_page' => -1,
		'post_status'    => 'publish'
	]);

	$addresses = [];
	foreach ($dealers as $dealer) {
		$addresses[] = [
			'name'    => get_post_meta($dealer->ID, 'dealer_name', true),
			'address' => get_post_meta($dealer->ID, 'dealer_address', true),
		];
	}

	$highlight_address = isset($_GET['address']) ? sanitize_text_field($_GET['address']) : '';

	ob_start(); ?>
	<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
	<div class="dealer-map-wrapper">
		<div id="map" class="dealer-map" style="height:500px; margin-bottom:20px;"></div>
		<div class="dealer-sidebar">
			<?php foreach ($dealers as $dealer):
				$name    = get_post_meta($dealer->ID, 'dealer_name', true);
				$address = get_post_meta($dealer->ID, 'dealer_address', true);
				$email   = get_post_meta($dealer->ID, 'dealer_email', true);
			?>
			
			<div class="dealer-entry">
				<div class="card-text">
					<h4 class="card-title"><?php echo esc_html($name); ?></h4>
					<span><?php echo esc_html($address); ?></span>
					<small><?php echo esc_html($email); ?></small>
				</div>
				<button class="custom-button"><a href="<?php echo esc_url( add_query_arg( 'address', urlencode($address) ) ); ?>" class="locate-btn">Weergeven op kaart</a></button>
			</div>
			
			<?php endforeach; ?>
		</div>
	</div>

	<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
	<script>
		document.addEventListener("DOMContentLoaded", function() {
			var map = L.map('map').setView([51.505, -0.09], 5);

			L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
				attribution: '&copy; OpenStreetMap contributors'
			}).addTo(map);

			var dealers = <?php echo json_encode($addresses); ?>;
			var highlightAddress = <?php echo json_encode($highlight_address); ?>;
			var markers = {}; // store markers by address

			var highlightIcon = L.icon({
				iconUrl: 'https://cdn-icons-png.flaticon.com/512/252/252025.png',
				iconSize: [30, 30]
			});

			// Geolocation zoom if no highlight
			if (navigator.geolocation) {
				navigator.geolocation.getCurrentPosition(function(position) {
					if (!highlightAddress) {
						map.setView([position.coords.latitude, position.coords.longitude], 9);
					}
					L.marker([position.coords.latitude, position.coords.longitude], {
						icon: L.icon({
							iconUrl: 'https://cdn-icons-png.flaticon.com/512/64/64113.png',
							iconSize: [25, 25]
						})
					}).addTo(map).bindPopup("Uw locatie");
				});
			}

			// Add markers
			dealers.forEach(function(dealer) {
				fetch("https://nominatim.openstreetmap.org/search?format=json&q=" + encodeURIComponent(dealer.address))
					.then(res => res.json())
					.then(data => {
					if (data.length > 0) {
						var lat = parseFloat(data[0].lat);
						var lon = parseFloat(data[0].lon);
						var isHighlight = (dealer.address === highlightAddress);

						var marker = L.marker([lat, lon], isHighlight ? { icon: highlightIcon } : {}).addTo(map);
						marker.bindPopup("<b>" + dealer.name + "</b><br>" + dealer.address);

						markers[dealer.address] = marker;

						if (isHighlight) {
							map.setView([lat, lon], 14);
							marker.openPopup();
						}
					}
				});
			});
		});
	</script>
	<?php return ob_get_clean();
}
add_shortcode('dealer_map', 'dealer_map_shortcode');