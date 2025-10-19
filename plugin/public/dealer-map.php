<?php
function dealer_map_shortcode() {
    global $wpdb;
    $table = 'pmsi_dealers';

    // Get all dealers with lat/lon
    $dealers = $wpdb->get_results(
        "SELECT * FROM $table WHERE lat IS NOT NULL AND lon IS NOT NULL ORDER BY name ASC"
    );

    $dealer_data = [];
    foreach ($dealers as $dealer) {
        $dealer_data[] = [
            'id' => $dealer->id,
            'name' => $dealer->name,
            'address' => $dealer->full_address,
            'lat' => $dealer->lat,
            'lon' => $dealer->lon,
            'website' => $dealer->site,
            'phone' => $dealer->phone
        ];
    }

    $default_icon_url = plugins_url('/icons/store-icon.png', __FILE__);
    $current_location_url = plugins_url('/icons/location.png', __FILE__);
    $highlight_icon_url = plugins_url('/icons/highlight.png', __FILE__);

    ob_start(); ?>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />

    <div class="dealer-map-wrapper">
        <div id="dealer-sidebar" class="dealer-sidebar">
            <form id="zipcode-search-form" class="zipcode-search" style="margin-bottom:15px;">
                <input type="text" id="zipcode-input" placeholder="Voer uw postcode in..." />
                <button class="custom-button" type="submit" style="min-height: 100%;">Zoeken</button>
            </form>
            <div class="dealer-card-wrapper">
                <?php foreach ($dealer_data as $dealer): ?>
                    <div class="dealer-entry" data-id="<?php echo esc_attr($dealer['id']); ?>" data-lat="<?php echo esc_attr($dealer['lat']); ?>" data-lon="<?php echo esc_attr($dealer['lon']); ?>">
                        <div class="card-text">
                            <h4 class="card-title">
                                <?php if ($dealer['website']): 
                                    $website = preg_match('#^https?://#', $dealer['website']) ? $dealer['website'] : 'https://' . $dealer['website'];
                                ?>
                                <a href="<?php echo esc_url($website); ?>" target="_blank" rel="noopener noreferrer">
                                	<?php echo esc_html($dealer['name']); ?>
                                </a>
                                <?php else: ?>
                                    <?php echo esc_html($dealer['name']); ?>
                                <?php endif; ?>
                            </h4>
                            <span><?php echo esc_html($dealer['address']); ?></span><br>
                            <?php if (!empty($dealer['phone'])): ?>
                                <span><?php echo esc_html($dealer['phone']); ?></span><br>
                            <?php endif; ?>
                            <button class="custom-button locate-btn" type="button">Op kaart weergeven</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div id="map" class="dealer-map" style="position:relative;">
            <div id="map-loader">
                <div class="spinner"></div>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script>
	document.addEventListener("DOMContentLoaded", function() {

		function normalizeAddress(address) {
			return address.trim().toLowerCase().replace(/,+$/, '');
		}

		function getQueryParam(name) {
			const urlParams = new URLSearchParams(window.location.search);
			return urlParams.get(name);
		}

		var highlightAddress = getQueryParam('address');
		if (highlightAddress) highlightAddress = decodeURIComponent(highlightAddress);

		var netherlandsCenter = [52.1326, 5.2913]; // center on The Netherlands
		var map = L.map('map').setView(netherlandsCenter, 7);

		L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
			attribution: '&copy; OpenStreetMap contributors'
		}).addTo(map);

		// Dealers from the database
		var dealers = <?php echo json_encode($dealer_data); ?>;

		var markerLayer = L.layerGroup().addTo(map);
		var zipcodeMarker = null;
		var markersById = {};

		var defaultIcon = L.icon({ iconUrl: "<?php echo esc_url($default_icon_url); ?>", iconSize: [25,25] });
		var currentLocationIcon = L.icon({ iconUrl: "<?php echo esc_url($current_location_url); ?>", iconSize: [20,20] });
		var highlightIcon = L.icon({ iconUrl: "<?php echo esc_url($highlight_icon_url); ?>", iconSize: [30,30] });

		function renderDealers(list) {
			// Clear map markers
			markerLayer.clearLayers();
			markersById = {};

			// Update sidebar
			const sidebarWrapper = document.querySelector('.dealer-card-wrapper');
			if (sidebarWrapper) {
				sidebarWrapper.innerHTML = list.map(d => `
					<div class="dealer-entry" data-id="${d.id}" data-lat="${d.lat}" data-lon="${d.lon}">
						<div class="card-text">
							<h4 class="card-title">
								${d.website 
									? `<a href="${d.website.startsWith('http') ? d.website : 'https://' + d.website}" target="_blank" rel="noopener noreferrer">${d.name}</a>` 
									: d.name}
							</h4>
							<span style="margin-bottom: 4px;">${d.address}</span>
							${d.distance ? `<small style="margin-bottom: 4px;">Afstand: ${d.distance.toFixed(1)} km</small>` : ''}
							<button class="custom-button locate-btn" type="button">Op kaart weergeven</button>
						</div>
					</div>
				`).join('');
			}

			// Add markers to map
			list.forEach(function(d) {
				var marker = L.marker([d.lat, d.lon], { icon: defaultIcon })
					.bindPopup("<b>"+d.name+"</b><br>"+d.address+(d.distance ? "<br>"+d.distance.toFixed(1)+" km" : ""));
				markerLayer.addLayer(marker);
				markersById[d.id] = marker;

				marker.on('click', function() {
					marker.setIcon(highlightIcon);
					Object.keys(markersById).forEach(function(mid) {
						if (mid != d.id) markersById[mid].setIcon(defaultIcon);
					});
				});
			});

			attachLocateEvents();
		}

		renderDealers(dealers);

		if (highlightAddress) {
			const dealerToHighlight = dealers.find(d => normalizeAddress(d.address) === normalizeAddress(highlightAddress));
			if (dealerToHighlight) {
				const marker = markersById[dealerToHighlight.id];
				if (marker) {
					marker.setIcon(highlightIcon);
					map.flyTo(marker.getLatLng(), 14, { duration: 1.5 });
					marker.openPopup();
				}
			}
		}

		function attachLocateEvents() {
			document.querySelectorAll(".locate-btn").forEach(function(btn) {
				btn.addEventListener("click", function() {
					var card = btn.closest(".dealer-entry");
					var id = card.getAttribute("data-id");
					var marker = markersById[id];
					if (marker) {
						marker.setIcon(highlightIcon);
						map.flyTo(marker.getLatLng(), 14, { duration: 1.5 });
						marker.openPopup();
						Object.keys(markersById).forEach(function(mid) {
							if (mid !== id) markersById[mid].setIcon(defaultIcon);
						});
					}
				});
			});
		}

		function getDistance(lat1, lon1, lat2, lon2) {
			var R = 6371;
			var dLat = (lat2-lat1) * Math.PI / 180;
			var dLon = (lon2-lon1) * Math.PI / 180;
			var a = Math.sin(dLat/2) * Math.sin(dLat/2) +
					Math.cos(lat1 * Math.PI/180) * Math.cos(lat2 * Math.PI/180) *
					Math.sin(dLon/2) * Math.sin(dLon/2);
			return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
		}

		function showLoader() { document.getElementById("map-loader").style.display = "flex"; }
		function hideLoader() { document.getElementById("map-loader").style.display = "none"; }

		// Geolocation with fallback
		if (navigator.geolocation) {
			navigator.geolocation.getCurrentPosition(function(position) {
				map.flyTo([position.coords.latitude, position.coords.longitude], 9, { duration: 1.5 });
				L.marker([position.coords.latitude, position.coords.longitude], {
					icon: L.icon({
						iconUrl: 'https://cdn-icons-png.flaticon.com/512/64/64113.png',
						iconSize: [25, 25]
					})
				}).addTo(map).bindPopup("Uw locatie");
			}, function(error) {
				map.flyTo(netherlandsCenter, 7, { duration: 1.5 });
			});
		} else {
			map.flyTo(netherlandsCenter, 7, { duration: 1.5 });
		}

		// Zipcode search logic
		document.addEventListener("submit", function(e) {
			if (e.target && e.target.id === "zipcode-search-form") {
				e.preventDefault();
				var zipcode = document.getElementById("zipcode-input").value.trim();

				if (!zipcode) {
					renderDealers(dealers);
					if (zipcodeMarker) { map.removeLayer(zipcodeMarker); zipcodeMarker = null; }
					return;
				}

				showLoader();

				fetch("https://nominatim.openstreetmap.org/search?format=json&limit=1&q=" + encodeURIComponent(zipcode + " Netherlands"))
					.then(res => res.json())
					.then(data => {
						hideLoader();

						if (data.length === 0) {
							alert("Geen locatie gevonden voor deze postcode.");
							return;
						}

						var userLat = parseFloat(data[0].lat);
						var userLon = parseFloat(data[0].lon);

						var nearest = dealers.map(d => ({
								...d,
								distance: getDistance(userLat, userLon, parseFloat(d.lat), parseFloat(d.lon))
							}))
							.sort((a, b) => a.distance - b.distance)
							.slice(0, 5);

						renderDealers(nearest);

						if (zipcodeMarker) { map.removeLayer(zipcodeMarker); }
						zipcodeMarker = L.marker([userLat, userLon], { icon: currentLocationIcon })
							.bindPopup("Zoeklocatie: " + zipcode)
							.addTo(map)
							.openPopup();

						var bounds = L.latLngBounds(nearest.map(d => [d.lat, d.lon]));
						bounds.extend([userLat, userLon]);
						map.flyToBounds(bounds, { padding: [50, 50], duration: 1.5 });
					})
					.catch(err => {
						hideLoader();
						console.error(err);
						alert("Fout bij het zoeken van postcode.");
					});
			}
		});
	});
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('dealer_map', 'dealer_map_shortcode');
 