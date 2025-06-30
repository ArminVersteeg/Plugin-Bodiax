<?php
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