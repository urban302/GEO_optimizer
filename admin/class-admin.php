<?php
/**
 * Admin — dashboard page and plugin action links.
 *
 * @package GEO_Optimizer
 */

declare(strict_types=1);

namespace GEO_Optimizer;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles the top-level GEO Optimizer dashboard menu page.
 */
class Admin {

	/**
	 * Register hooks.
	 */
	public function register(): void {
		add_action( 'admin_menu', [ $this, 'add_menu_page' ] );
		add_filter( 'plugin_action_links_' . GEO_OPTIMIZER_BASENAME, [ $this, 'add_settings_link' ] );
	}

	/**
	 * Register the top-level dashboard page.
	 */
	public function add_menu_page(): void {
		add_menu_page(
			__( 'GEO Optimizer', 'geo-optimizer' ),
			__( 'GEO Optimizer', 'geo-optimizer' ),
			'manage_options',
			'geo-optimizer',
			[ $this, 'render_dashboard' ],
			'dashicons-chart-area',
			80
		);
	}

	/**
	 * Add a "Settings" link on the plugins list page.
	 *
	 * @param string[] $links Existing action links.
	 * @return string[]
	 */
	public function add_settings_link( array $links ): array {
		$url  = admin_url( 'options-general.php?page=geo-optimizer-settings' );
		$link = '<a href="' . esc_url( $url ) . '">' . __( 'Instellingen', 'geo-optimizer' ) . '</a>';
		array_unshift( $links, $link );
		return $links;
	}

	/**
	 * Render the dashboard page.
	 */
	public function render_dashboard(): void {
		require_once GEO_OPTIMIZER_PATH . 'admin/views/dashboard.php';
	}
}
