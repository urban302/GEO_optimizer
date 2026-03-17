<?php
/**
 * Plugin Name: GEORank
 * Plugin URI:  https://www.erwinverbeek.nl/geo-optimizer/
 * Description: Maak je WordPress website vindbaar voor AI-zoekmachines zoals ChatGPT, Perplexity en Google AI Overviews.
 * Version:     1.0.0
 * Requires PHP: 8.0
 * Author:      Erwin Verbeek
 * Author URI:  https://www.erwinverbeek.nl
 * License:     GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: geo-optimizer
 * Domain Path: /languages
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Plugin constants.
define( 'GEO_OPTIMIZER_VERSION', '1.0.0' );
define( 'GEO_OPTIMIZER_FILE', __FILE__ );
define( 'GEO_OPTIMIZER_PATH', plugin_dir_path( __FILE__ ) );
define( 'GEO_OPTIMIZER_URL', plugin_dir_url( __FILE__ ) );
define( 'GEO_OPTIMIZER_BASENAME', plugin_basename( __FILE__ ) );

// Autoload classes.
require_once GEO_OPTIMIZER_PATH . 'includes/class-schema-manager.php';
require_once GEO_OPTIMIZER_PATH . 'includes/class-llms-txt.php';
require_once GEO_OPTIMIZER_PATH . 'includes/class-geo-score.php';
require_once GEO_OPTIMIZER_PATH . 'includes/class-api-manager.php';
require_once GEO_OPTIMIZER_PATH . 'includes/class-faq-generator.php';
require_once GEO_OPTIMIZER_PATH . 'includes/class-citation-score.php';
require_once GEO_OPTIMIZER_PATH . 'admin/class-admin.php';
require_once GEO_OPTIMIZER_PATH . 'admin/class-settings-page.php';
require_once GEO_OPTIMIZER_PATH . 'admin/class-pro-settings.php';

/**
 * Check whether Pro features are available (API key is set).
 */
function geo_is_pro(): bool {
	return GEO_Optimizer\API_Manager::has_api_key();
}

define( 'GEORANK_PRO', geo_is_pro() );

/**
 * Boot the plugin after all plugins are loaded.
 */
function geo_optimizer_init(): void {
	$schema_manager = new GEO_Optimizer\Schema_Manager();
	$schema_manager->register();

	$llms_txt = new GEO_Optimizer\Llms_Txt();
	$llms_txt->register();

	if ( is_admin() ) {
		$geo_score = new GEO_Optimizer\Geo_Score();
		$geo_score->register();

		$settings_page = new GEO_Optimizer\Settings_Page();
		$settings_page->register();

		$pro_settings = new GEO_Optimizer\Pro_Settings();
		$pro_settings->register();

		$admin = new GEO_Optimizer\Admin();
		$admin->register();
	}
}
add_action( 'plugins_loaded', 'geo_optimizer_init' );

/**
 * Activation hook.
 */
function geo_optimizer_activate(): void {
	// Flush rewrite rules so /llms.txt works immediately.
	$llms_txt = new GEO_Optimizer\Llms_Txt();
	$llms_txt->add_rewrite_rules();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'geo_optimizer_activate' );

/**
 * Deactivation hook.
 */
function geo_optimizer_deactivate(): void {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'geo_optimizer_deactivate' );
