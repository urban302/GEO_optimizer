<?php
/**
 * GEO Score — calculates and displays a GEO optimization score in the editor.
 *
 * @package GEO_Optimizer
 */

declare(strict_types=1);

namespace GEO_Optimizer;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Adds a meta box that shows a 0-100 GEO score with per-factor indicators.
 */
class Geo_Score {

	private const NONCE_ACTION = 'geo_optimizer_score_nonce';
	private const NONCE_FIELD  = '_geo_optimizer_nonce';

	/**
	 * Register hooks.
	 */
	public function register(): void {
		add_action( 'add_meta_boxes', [ $this, 'add_meta_box' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
		add_action( 'wp_ajax_geo_optimizer_score', [ $this, 'ajax_calculate_score' ] );
	}

	/**
	 * Register the meta box for posts and pages.
	 *
	 * Uses __back_compat_meta_box = false so the box renders inside the
	 * Gutenberg sidebar instead of only in the classic editor.
	 */
	public function add_meta_box(): void {
		$post_types = [ 'post', 'page' ];

		foreach ( $post_types as $post_type ) {
			add_meta_box(
				'geo_optimizer_score',
				__( 'GEO Score', 'georank' ),
				[ $this, 'render_meta_box' ],
				$post_type,
				'side',
				'high',
				[
					'__back_compat_meta_box' => false,
				]
			);
		}
	}

	/**
	 * Enqueue CSS and JS for the meta box.
	 *
	 * @param string $hook_suffix Current admin page.
	 */
	public function enqueue_assets( string $hook_suffix ): void {
		if ( ! in_array( $hook_suffix, [ 'post.php', 'post-new.php' ], true ) ) {
			return;
		}

		wp_enqueue_style(
			'geo-optimizer-score',
			GEO_OPTIMIZER_URL . 'assets/css/geo-score.css',
			[],
			GEO_OPTIMIZER_VERSION
		);

		wp_enqueue_script(
			'geo-optimizer-score',
			GEO_OPTIMIZER_URL . 'assets/js/geo-score.js',
			[ 'jquery' ],
			GEO_OPTIMIZER_VERSION,
			true
		);

		wp_localize_script( 'geo-optimizer-score', 'geoOptimizerScore', [
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( self::NONCE_ACTION ),
			'postId'  => get_the_ID(),
		] );
	}

	/**
	 * Render the meta box HTML.
	 *
	 * @param \WP_Post $post Current post.
	 */
	public function render_meta_box( \WP_Post $post ): void {
		$factors = $this->calculate_factors( $post );
		$score   = $this->calculate_total_score( $factors );
		?>
		<div class="geo-score-wrap" id="geo-score-wrap">
			<div class="geo-score-total">
				<span class="geo-score-number <?php echo esc_attr( $this->score_color_class( $score ) ); ?>">
					<?php echo esc_html( (string) $score ); ?>
				</span>
				<span class="geo-score-label">/100</span>
			</div>

			<ul class="geo-score-factors">
				<?php foreach ( $factors as $factor ) : ?>
					<li class="geo-factor geo-factor--<?php echo esc_attr( $factor['status'] ); ?>">
						<span class="geo-factor-indicator"></span>
						<span class="geo-factor-label"><?php echo esc_html( $factor['label'] ); ?></span>
						<span class="geo-factor-points"><?php echo esc_html( $factor['points'] . '/' . $factor['max'] ); ?></span>
					</li>
				<?php endforeach; ?>
			</ul>

			<button type="button" class="button button-small" id="geo-score-refresh">
				<?php esc_html_e( 'Recalculate', 'georank' ); ?>
			</button>
		</div>
		<?php
	}

	/**
	 * AJAX handler to recalculate the score.
	 */
	public function ajax_calculate_score(): void {
		check_ajax_referer( self::NONCE_ACTION, 'nonce' );

		$post_id = isset( $_POST['post_id'] ) ? absint( wp_unslash( $_POST['post_id'] ) ) : 0;
		$post    = get_post( $post_id );

		if ( ! $post || ! current_user_can( 'edit_post', $post_id ) ) {
			wp_send_json_error( 'Invalid post.' );
		}

		$factors = $this->calculate_factors( $post );
		$score   = $this->calculate_total_score( $factors );

		wp_send_json_success( [
			'score'      => $score,
			'colorClass' => $this->score_color_class( $score ),
			'factors'    => $factors,
		] );
	}

	/**
	 * Calculate all scoring factors for a post.
	 *
	 * @param \WP_Post $post The post.
	 * @return array<int, array{label: string, points: int, max: int, status: string}>
	 */
	public function calculate_factors( \WP_Post $post ): array {
		$content     = $post->post_content;
		$rendered    = wp_kses_post( $content );
		$word_count  = str_word_count( wp_strip_all_tags( $rendered ) );

		$factors = [];

		// 1. Schema markup (max 20).
		// Full points if a SEO plugin handles schema, or if FAQ content is present.
		$seo_active   = Schema_Manager::is_seo_plugin_active();
		$has_faq      = $this->content_has_faq( $rendered );
		$schema_points = match ( true ) {
			$seo_active        => 20,
			$has_faq           => 20,
			default            => 15, // Schema_Manager is always loaded.
		};
		$factors[] = [
			'label'  => __( 'Schema Markup', 'georank' ),
			'points' => $schema_points,
			'max'    => 20,
			'status' => $this->point_status( $schema_points, 20 ),
		];

		// 2. FAQ blocks (max 20).
		$faq_count  = $this->count_faq_items( $rendered );
		$faq_points = match ( true ) {
			$faq_count >= 3 => 20,
			$faq_count >= 1 => 12,
			default         => 0,
		};
		$factors[] = [
			'label'  => __( 'FAQ Blocks', 'georank' ),
			'points' => $faq_points,
			'max'    => 20,
			'status' => $this->point_status( $faq_points, 20 ),
		];

		// 3. Author information (max 20).
		$author_id    = (int) $post->post_author;
		$has_bio      = (bool) get_the_author_meta( 'description', $author_id );
		$author_points = $has_bio ? 20 : 5;
		$factors[]    = [
			'label'  => __( 'Author Info', 'georank' ),
			'points' => $author_points,
			'max'    => 20,
			'status' => $this->point_status( $author_points, 20 ),
		];

		// 4. Internal links (max 20).
		$internal_links = $this->count_internal_links( $rendered );
		$link_points    = match ( true ) {
			$internal_links >= 5 => 20,
			$internal_links >= 3 => 15,
			$internal_links >= 1 => 8,
			default              => 0,
		};
		$factors[] = [
			'label'  => __( 'Internal Links', 'georank' ),
			'points' => $link_points,
			'max'    => 20,
			'status' => $this->point_status( $link_points, 20 ),
		];

		// 5. Content length (max 20).
		$length_points = match ( true ) {
			$word_count >= 1500 => 20,
			$word_count >= 800  => 15,
			$word_count >= 300  => 10,
			default             => 3,
		};
		$factors[] = [
			'label'  => __( 'Content Length', 'georank' ),
			'points' => $length_points,
			'max'    => 20,
			'status' => $this->point_status( $length_points, 20 ),
		];

		return $factors;
	}

	/**
	 * Sum the total score from factors.
	 *
	 * @param array<int, array{points: int}> $factors Scoring factors.
	 */
	private function calculate_total_score( array $factors ): int {
		return array_sum( array_column( $factors, 'points' ) );
	}

	/**
	 * Get a color status string based on how many points were earned.
	 */
	private function point_status( int $points, int $max ): string {
		$ratio = $points / $max;

		return match ( true ) {
			$ratio >= 0.75 => 'green',
			$ratio >= 0.4  => 'orange',
			default        => 'red',
		};
	}

	/**
	 * Get a CSS class for the total score.
	 */
	private function score_color_class( int $score ): string {
		return match ( true ) {
			$score >= 75 => 'geo-score--green',
			$score >= 40 => 'geo-score--orange',
			default      => 'geo-score--red',
		};
	}

	/**
	 * Check if rendered content has FAQ-style headings.
	 */
	private function content_has_faq( string $html ): bool {
		return (bool) preg_match( '/<h[2-3][^>]*>.+?\?<\/h[2-3]>/i', $html );
	}

	/**
	 * Count FAQ items (headings ending with ?).
	 */
	private function count_faq_items( string $html ): int {
		preg_match_all( '/<h[2-3][^>]*>.+?\?<\/h[2-3]>/i', $html, $matches );
		return count( $matches[0] );
	}

	/**
	 * Count internal links in rendered HTML.
	 */
	private function count_internal_links( string $html ): int {
		$home = home_url();
		preg_match_all( '/<a\s[^>]*href=["\']([^"\']+)["\'][^>]*>/i', $html, $matches );

		$count = 0;
		foreach ( $matches[1] as $url ) {
			if ( str_starts_with( $url, $home ) || str_starts_with( $url, '/' ) ) {
				$count++;
			}
		}

		return $count;
	}
}
