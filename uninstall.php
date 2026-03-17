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

// Remove all post meta added by this plugin without a slow meta_query.
delete_metadata( 'post', 0, '_geo_optimizer_score', '', true );
delete_metadata( 'post', 0, '_geo_optimizer_last_checked', '', true );
