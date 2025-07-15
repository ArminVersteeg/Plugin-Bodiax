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
		'supports' => ['title'],
		'menu_icon' => 'dashicons-businessperson',
		'show_in_rest' => true,
	]);
}
add_action('init', 'register_dealer_post_type');