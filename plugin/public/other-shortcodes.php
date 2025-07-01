<?php
// Shortcode to display current user's username
function show_current_username() {
	if (is_user_logged_in()) {
		$current_user = wp_get_current_user();
		return esc_html( get_user_meta($current_user->ID, 'first_name', true));
	}
	
	return '';
}
add_shortcode('username', 'show_current_username');

// Header profile widget
function custom_register_header_widget_area() { // Register custom widget
	register_sidebar( array(
		'name'          => 'Header Widget Area',
		'id'            => 'header-widget-area',
		'before_widget' => '<div class="header-widget">',
		'after_widget'  => '</div>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	));
}
add_action( 'widgets_init', 'custom_register_header_widget_area' );

class WP_User_Profile_Dropdown_Widget extends WP_Widget { // Register and display the widget
	public function __construct() {
		parent::__construct(
			'user_profile_dropdown_widget',
			__('User Profile Dropdown', 'text_domain'),
			array('description' => __('Displays the logged-in user\'s avatar with a dropdown menu.'))
		);
	}
	
	public function widget($args, $instance) {
		if (!is_user_logged_in()) return;
		
		$current_user = wp_get_current_user();
		$avatar = get_avatar($current_user->ID, 48);
		$profile_url = get_edit_profile_url($current_user->ID);
		$logout_url = wp_logout_url(home_url());
		
		echo $args['before_widget'];
		?>
		<div class="user-profile-widget">
			<div id="user-avatar" class="avatar">
				<?php echo $avatar; ?>
			</div>
			<div class="profile-dropdown" id="profile-dropdown">
				<p>Accountbeheer</p>
				<a class="profile-btn" href="./profile">Profiel</a>
				<a class="logout-btn" href="<?php echo esc_url($logout_url); ?>">Uitloggen</a>
			</div>
		</div>
		<?php echo $args['after_widget'];
	}
	
	public function form($instance) {
		echo '<p>No settings required.</p>';
	}
	
	public function update($new_instance, $old_instance) {
		return $old_instance;
	}
}

function register_user_profile_dropdown_widget() {
	register_widget('WP_User_Profile_Dropdown_Widget');
}
add_action('widgets_init', 'register_user_profile_dropdown_widget');