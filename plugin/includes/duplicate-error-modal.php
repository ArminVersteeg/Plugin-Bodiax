<?php
// === DUPLICATE ERROR SHORTCODE ===
function duplicate_modal_shortcode($atts) {
	// Get and sanitize query string parameters
	$names = isset($_GET['names']) ? explode('|', sanitize_text_field($_GET['names'])) : [];
	$emails = isset($_GET['emails']) ? explode('|', sanitize_text_field($_GET['emails'])) : [];
	$reasons = isset($_GET['reasons']) ? explode('|', sanitize_text_field($_GET['reasons'])) : [];
	
	// Build error modal
	ob_start(); ?>
	<?php if (!empty($names)) : ?>
	<div id="error-modal" class="modal-overlay" style="display: none;">
		<div class="modal-box">
			<h3>Deze items zijn overgeslagen i.v.m. duplicaties:</h3>
			<p class="modal-message">
				<?php foreach ($names as $key => $name): ?>
					<?php
						$email = isset($emails[$key]) ? $emails[$key] : '';
						$reason = isset($reasons[$key]) ? $reasons[$key] : 'al in gebruik';
					?>
				<strong><?php echo esc_html($name); ?></strong>
				<?php if (!empty($email)) echo ' <strong>(' . esc_html($email) . ')</strong>'; ?> is overgeslagen omdat deze <strong><?php echo esc_html($reason); ?></strong>.
				<?php if ($key < count($names) - 1) echo '<br>'; ?>
				<?php endforeach; ?>
			</p>
			<button id="modal-ok" class="modal-button">OK</button>
		</div>
	</div>
	<?php endif;
	return ob_get_clean();
}
add_shortcode('duplicate_modal', 'duplicate_modal_shortcode');