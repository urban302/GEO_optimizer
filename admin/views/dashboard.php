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
	<h1><?php esc_html_e( 'GEORank', 'georank' ); ?></h1>

	<div class="card">
		<h2><?php esc_html_e( 'Status', 'georank' ); ?></h2>
		<table class="widefat striped">
			<tbody>
				<tr>
					<td><?php esc_html_e( 'Schema Markup', 'georank' ); ?></td>
					<td><span class="dashicons dashicons-yes-alt" style="color:#46b450"></span> <?php esc_html_e( 'Active', 'georank' ); ?></td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'LLMs.txt Endpoint', 'georank' ); ?></td>
					<td>
						<span class="dashicons dashicons-yes-alt" style="color:#46b450"></span>
						<a href="<?php echo esc_url( home_url( '/llms.txt' ) ); ?>" target="_blank">/llms.txt</a>
					</td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'GEO Score Meta Box', 'georank' ); ?></td>
					<td><span class="dashicons dashicons-yes-alt" style="color:#46b450"></span> <?php esc_html_e( 'Active on posts &amp; pages', 'georank' ); ?></td>
				</tr>
			</tbody>
		</table>
	</div>

	<div class="card" style="margin-top:20px">
		<h2><?php esc_html_e( 'Quick Links', 'georank' ); ?></h2>
		<ul>
			<li><a href="<?php echo esc_url( home_url( '/llms.txt' ) ); ?>" target="_blank"><?php esc_html_e( 'View llms.txt', 'georank' ); ?></a></li>
			<li><a href="<?php echo esc_url( admin_url( 'edit.php' ) ); ?>"><?php esc_html_e( 'Edit Posts (check GEO Scores)', 'georank' ); ?></a></li>
		</ul>
	</div>
</div>
