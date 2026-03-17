<?php
/**
 * GEO Optimizer uninstall script.
 *
 * Fired when the plugin is deleted via the WordPress admin.
 */

declare(strict_types=1);

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Remove plugin options.
delete_option( 'geo_optimizer_settings' );

// Remove per-post meta data.
global $wpdb;
$wpdb->query( "DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE '_geo_optimizer_%'" );
