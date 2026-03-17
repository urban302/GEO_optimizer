<?php
/**
 * Settings page view for GEO Optimizer.
 *
 * @package GEO_Optimizer
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$active_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'organization'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
?>
<div class="wrap">
	<h1><?php esc_html_e( 'GEO Optimizer — Instellingen', 'geo-optimizer' ); ?></h1>

	<nav class="nav-tab-wrapper">
		<a href="<?php echo esc_url( admin_url( 'options-general.php?page=geo-optimizer-settings&tab=organization' ) ); ?>"
		   class="nav-tab <?php echo 'organization' === $active_tab ? 'nav-tab-active' : ''; ?>">
			<?php esc_html_e( 'Organisatie', 'geo-optimizer' ); ?>
		</a>
		<a href="<?php echo esc_url( admin_url( 'options-general.php?page=geo-optimizer-settings&tab=address' ) ); ?>"
		   class="nav-tab <?php echo 'address' === $active_tab ? 'nav-tab-active' : ''; ?>">
			<?php esc_html_e( 'Adres', 'geo-optimizer' ); ?>
		</a>
	</nav>

	<form method="post" action="options.php">
		<?php settings_fields( \GEO_Optimizer\Settings::option_group() ); ?>

		<?php if ( 'organization' === $active_tab ) : ?>
			<?php do_settings_sections( 'geo-optimizer-organization' ); ?>
		<?php else : ?>
			<?php do_settings_sections( 'geo-optimizer-address' ); ?>
		<?php endif; ?>

		<?php submit_button( __( 'Instellingen opslaan', 'geo-optimizer' ) ); ?>
	</form>
</div>
