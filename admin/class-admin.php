<?php
/**
 * Admin — dashboard page and settings for GEO Optimizer.
 *
 * @package GEO_Optimizer
 */

declare(strict_types=1);

namespace GEO_Optimizer;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles the admin menu page and plugin settings link.
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
	 * Register the admin menu page.
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
		$url  = admin_url( 'admin.php?page=geo-optimizer' );
		$link = '<a href="' . esc_url( $url ) . '">' . __( 'Settings', 'geo-optimizer' ) . '</a>';
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
