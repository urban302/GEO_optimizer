<?php
/**
 * Dashboard view for GEO Optimizer.
 *
 * @package GEO_Optimizer
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap">
	<h1><?php esc_html_e( 'GEO Optimizer', 'geo-optimizer' ); ?></h1>

	<div class="card">
		<h2><?php esc_html_e( 'Status', 'geo-optimizer' ); ?></h2>
		<table class="widefat striped">
			<tbody>
				<tr>
					<td><?php esc_html_e( 'Schema Markup', 'geo-optimizer' ); ?></td>
					<td><span class="dashicons dashicons-yes-alt" style="color:#46b450"></span> <?php esc_html_e( 'Active', 'geo-optimizer' ); ?></td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'LLMs.txt Endpoint', 'geo-optimizer' ); ?></td>
					<td>
						<span class="dashicons dashicons-yes-alt" style="color:#46b450"></span>
						<a href="<?php echo esc_url( home_url( '/llms.txt' ) ); ?>" target="_blank">/llms.txt</a>
					</td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'GEO Score Meta Box', 'geo-optimizer' ); ?></td>
					<td><span class="dashicons dashicons-yes-alt" style="color:#46b450"></span> <?php esc_html_e( 'Active on posts &amp; pages', 'geo-optimizer' ); ?></td>
				</tr>
			</tbody>
		</table>
	</div>

	<div class="card" style="margin-top:20px">
		<h2><?php esc_html_e( 'Quick Links', 'geo-optimizer' ); ?></h2>
		<ul>
			<li><a href="<?php echo esc_url( home_url( '/llms.txt' ) ); ?>" target="_blank"><?php esc_html_e( 'View llms.txt', 'geo-optimizer' ); ?></a></li>
			<li><a href="<?php echo esc_url( admin_url( 'edit.php' ) ); ?>"><?php esc_html_e( 'Edit Posts (check GEO Scores)', 'geo-optimizer' ); ?></a></li>
		</ul>
	</div>
</div>
