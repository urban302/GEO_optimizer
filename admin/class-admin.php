<?php
/**
 * Admin — dashboard page, settings page, and asset loading.
 *
 * @package GEO_Optimizer
 */

declare(strict_types=1);

namespace GEO_Optimizer;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles admin menu pages, settings registration, and asset enqueuing.
 */
class Admin {

	/**
	 * Register hooks.
	 */
	public function register(): void {
		add_action( 'admin_menu', [ $this, 'add_menu_pages' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_settings_assets' ] );
		add_filter( 'plugin_action_links_' . GEO_OPTIMIZER_BASENAME, [ $this, 'add_settings_link' ] );
	}

	/**
	 * Register the admin menu pages.
	 */
	public function add_menu_pages(): void {
		// Top-level dashboard page.
		add_menu_page(
			__( 'GEO Optimizer', 'geo-optimizer' ),
			__( 'GEO Optimizer', 'geo-optimizer' ),
			'manage_options',
			'geo-optimizer',
			[ $this, 'render_dashboard' ],
			'dashicons-chart-area',
			80
		);

		// Settings page under Instellingen.
		add_options_page(
			__( 'GEO Optimizer — Instellingen', 'geo-optimizer' ),
			__( 'GEO Optimizer', 'geo-optimizer' ),
			'manage_options',
			'geo-optimizer-settings',
			[ $this, 'render_settings' ]
		);
	}

	/**
	 * Enqueue CSS and JS for the settings page.
	 *
	 * @param string $hook_suffix Current admin page hook suffix.
	 */
	public function enqueue_settings_assets( string $hook_suffix ): void {
		if ( 'settings_page_geo-optimizer-settings' !== $hook_suffix ) {
			return;
		}

		// WordPress media uploader.
		wp_enqueue_media();

		wp_enqueue_script(
			'geo-optimizer-admin-settings',
			GEO_OPTIMIZER_URL . 'assets/js/admin-settings.js',
			[ 'jquery', 'media-upload', 'thickbox' ],
			GEO_OPTIMIZER_VERSION,
			true
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

	/**
	 * Render the settings page.
	 */
	public function render_settings(): void {
		require_once GEO_OPTIMIZER_PATH . 'admin/views/settings.php';
	}
}
