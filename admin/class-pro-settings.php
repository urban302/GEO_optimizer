<?php
/**
 * Pro Settings — API key management and Pro feature status.
 *
 * @package GEO_Optimizer
 */

declare(strict_types=1);

namespace GEO_Optimizer;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the Pro / AI settings fields and handles AJAX key validation.
 */
class Pro_Settings {

	private const OPTION_API_KEY = 'georank_api_key';
	private const NONCE_VALIDATE = 'geo_optimizer_validate_key';

	/**
	 * Register hooks.
	 */
	public function register(): void {
		add_action( 'admin_init', [ $this, 'register_settings' ] );
		add_action( 'wp_ajax_geo_optimizer_validate_key', [ $this, 'ajax_validate_key' ] );
	}

	/**
	 * Register the API key setting.
	 */
	public function register_settings(): void {
		register_setting(
			'geo_optimizer_pro',
			self::OPTION_API_KEY,
			[
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => '',
			]
		);
	}

	/**
	 * AJAX handler: validate the stored API key.
	 */
	public function ajax_validate_key(): void {
		check_ajax_referer( self::NONCE_VALIDATE, '_nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Onvoldoende rechten.', 'geo-optimizer' ) );
		}

		$result = API_Manager::validate_api_key();

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( $result->get_error_message() );
		}

		wp_send_json_success( __( 'API key is geldig!', 'geo-optimizer' ) );
	}

	/**
	 * Render the Pro / AI settings tab content.
	 */
	public static function render_tab(): void {
		$has_key = API_Manager::has_api_key();
		$api_key = API_Manager::get_api_key();
		?>

		<?php if ( ! $has_key ) : ?>
			<div class="notice notice-info inline" style="margin:20px 0">
				<p>
					<strong><?php esc_html_e( 'Upgrade naar Pro', 'geo-optimizer' ); ?></strong><br>
					<?php esc_html_e( 'Ontgrendel AI-powered content analyse, automatische FAQ suggesties en citeer-scores.', 'geo-optimizer' ); ?>
					<a href="https://www.erwinverbeek.nl/geo-optimizer/" target="_blank" rel="noopener">
						<?php esc_html_e( 'Meer informatie &rarr;', 'geo-optimizer' ); ?>
					</a>
				</p>
			</div>
		<?php endif; ?>

		<table class="form-table" role="presentation">
			<tr>
				<th scope="row">
					<label for="georank_api_key"><?php esc_html_e( 'API Key', 'geo-optimizer' ); ?></label>
				</th>
				<td>
					<input type="password"
						   id="georank_api_key"
						   name="georank_api_key"
						   value="<?php echo esc_attr( $api_key ); ?>"
						   class="regular-text"
						   autocomplete="off" />
					<button type="button"
							id="geo-validate-key"
							class="button button-secondary"
							data-nonce="<?php echo esc_attr( wp_create_nonce( self::NONCE_VALIDATE ) ); ?>">
						<?php esc_html_e( 'Valideer API key', 'geo-optimizer' ); ?>
					</button>
					<span id="geo-key-status" style="margin-left:8px"></span>
				</td>
			</tr>
		</table>

		<h3><?php esc_html_e( 'Pro features', 'geo-optimizer' ); ?></h3>
		<table class="widefat striped" style="max-width:600px">
			<tbody>
				<tr>
					<td><?php esc_html_e( 'AI Content Analyse', 'geo-optimizer' ); ?></td>
					<td><?php self::render_status_badge( $has_key ); ?></td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'FAQ Generator', 'geo-optimizer' ); ?></td>
					<td><?php self::render_status_badge( $has_key ); ?></td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'Citeer-score', 'geo-optimizer' ); ?></td>
					<td><?php self::render_status_badge( $has_key ); ?></td>
				</tr>
			</tbody>
		</table>

		<script>
		(function(){
			var btn    = document.getElementById('geo-validate-key');
			var status = document.getElementById('geo-key-status');
			if (!btn) return;
			btn.addEventListener('click', function(){
				status.textContent = '<?php echo esc_js( __( 'Bezig met valideren...', 'geo-optimizer' ) ); ?>';
				status.style.color = '#666';
				var xhr = new XMLHttpRequest();
				xhr.open('POST', ajaxurl);
				xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
				xhr.onload = function(){
					var res = JSON.parse(xhr.responseText);
					if (res.success) {
						status.textContent = res.data;
						status.style.color = '#46b450';
					} else {
						status.textContent = res.data;
						status.style.color = '#dc3232';
					}
				};
				xhr.onerror = function(){
					status.textContent = '<?php echo esc_js( __( 'Netwerkfout.', 'geo-optimizer' ) ); ?>';
					status.style.color = '#dc3232';
				};
				xhr.send('action=geo_optimizer_validate_key&_nonce=' + encodeURIComponent(btn.dataset.nonce));
			});
		})();
		</script>

		<?php
	}

	/**
	 * Render an active/inactive badge.
	 */
	private static function render_status_badge( bool $active ): void {
		if ( $active ) {
			printf(
				'<span class="dashicons dashicons-yes-alt" style="color:#46b450"></span> %s',
				esc_html__( 'Actief', 'geo-optimizer' )
			);
		} else {
			printf(
				'<span class="dashicons dashicons-marker" style="color:#ccc"></span> %s',
				esc_html__( 'Inactief — API key vereist', 'geo-optimizer' )
			);
		}
	}
}
