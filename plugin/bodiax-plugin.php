<?php
/**
 * Plugin Name: Bodiax
 * Description: Custom CRUD's for dealers and representatives, also adds small other functions/widgets. Created for the Bodiax dealerportal.
 * Version: 1.2.3
 * Author: Armin Versteeg
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


// Load AJAX and pass it to JS
function enqueue_plugin_scripts() {
	wp_enqueue_script('ajax', plugin_dir_url(__FILE__) . 'assets/js/ajax.js', [], null, true);
	
	wp_localize_script('ajax', 'BodiaxData', [
		'ajaxUrl' => admin_url('admin-ajax.php'),
		'profileEditNonce' => wp_create_nonce('save_profile_edits'),
		'passwordUpdateNonce' => wp_create_nonce('custom_update_password')
	]);
}
add_action('wp_enqueue_scripts', 'enqueue_plugin_scripts');


// Define plugin directory path
define( 'BODIAX_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );


// Enqueue plugin scripts
function bodiax_enqueue_scripts_frontend() {
	bodiax_enqueue_scripts_from_dir('public/js/'); // Load public scripts (frontend only)
	bodiax_enqueue_scripts_from_dir('assets/js/'); // Load shared assets (frontend)
}
add_action('wp_enqueue_scripts', 'bodiax_enqueue_scripts_frontend');

function bodiax_enqueue_scripts_admin() {
	bodiax_enqueue_scripts_from_dir('admin/js/'); // Load admin scripts (admin only)
	bodiax_enqueue_scripts_from_dir('assets/js/'); // Load shared assets (admin)
}
add_action('admin_enqueue_scripts', 'bodiax_enqueue_scripts_admin');

function bodiax_enqueue_scripts_from_dir($relative_dir) {
	$dir_path = plugin_dir_path(__FILE__) . $relative_dir;
	$dir_url  = plugin_dir_url(__FILE__) . $relative_dir;
	
	foreach (glob($dir_path . '*.js') as $file_path) {
		$handle = 'bodiax-' . md5($file_path);
		$file_url = $dir_url . basename($file_path);
		wp_enqueue_script($handle, $file_url, [], null, true);
	}
}


// Enqueue plugin styles
function bodiax_enqueue_styles_frontend() {
	bodiax_enqueue_styles_from_dir('public/style/'); // Load public styles (frontend only)
	bodiax_enqueue_styles_from_dir('assets/style/'); // Load shared assets (frontend)
}
add_action('wp_enqueue_scripts', 'bodiax_enqueue_styles_frontend');

function bodiax_enqueue_styles_admin() {
	bodiax_enqueue_styles_from_dir('admin/style/'); // Load admin styles (admin only)
	bodiax_enqueue_styles_from_dir('assets/style/'); // Load shared assets (admin)
}
add_action('admin_enqueue_scripts', 'bodiax_enqueue_styles_admin');

function bodiax_enqueue_styles_from_dir($relative_dir) {
	$dir_path = plugin_dir_path(__FILE__) . $relative_dir;
	$dir_url  = plugin_dir_url(__FILE__) . $relative_dir;
	
	foreach (glob($dir_path . '*.css') as $file_path) {
		$handle = 'bodiax-' . md5($file_path);
		$file_url = $dir_url . basename($file_path);
		wp_enqueue_style($handle, $file_url, [], null);
	}
}


// Include php files
function include_multiple_php_paths() {
	$php_directories = [
		'includes/',
		'public/',
		'admin/',
	];
	
	foreach ($php_directories as $relative_dir) {
		$full_path = plugin_dir_path(__FILE__) . $relative_dir;
		
		if (is_dir($full_path)) {
			foreach (glob($full_path . '*.php') as $php_file) {
				require_once $php_file;
			}
		}
	}
}
include_multiple_php_paths();

