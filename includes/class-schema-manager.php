<?php
/**
 * Schema Manager — generates JSON-LD structured data for AI engines.
 *
 * @package GEO_Optimizer
 */

declare(strict_types=1);

namespace GEO_Optimizer;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Outputs JSON-LD schema markup in wp_head based on post type and content.
 *
 * All individual schemas are collected into a single @graph array to produce
 * one cohesive JSON-LD block per page.
 */
class Schema_Manager {

	/**
	 * Register hooks.
	 */
	public function register(): void {
		add_action( 'wp_head', [ $this, 'render_schema' ], 1 );
		add_action( 'admin_notices', [ $this, 'notice_seo_conflict' ] );
	}

	/**
	 * Check whether a known SEO plugin that outputs JSON-LD is active.
	 */
	public static function is_seo_plugin_active(): bool {
		return defined( 'RANK_MATH_VERSION' )
			|| defined( 'WPSEO_VERSION' )
			|| defined( 'AIOSEO_VERSION' );
	}

	/**
	 * Show a warning when a conflicting SEO plugin is detected.
	 */
	public function notice_seo_conflict(): void {
		if ( ! self::is_seo_plugin_active() ) {
			return;
		}

		$screen = get_current_screen();
		if ( ! $screen || ! in_array( $screen->id, [ 'toplevel_page_geo-optimizer', 'settings_page_geo-optimizer-settings' ], true ) ) {
			return;
		}

		printf(
			'<div class="notice notice-warning"><p><strong>%s</strong> %s</p></div>',
			esc_html__( 'GEORank:', 'georank' ),
			esc_html__( 'RankMath, Yoast or AIOSEO detected. Schema output has been automatically disabled to prevent conflicts. The llms.txt and GEO Score features continue to work normally.', 'georank' )
		);
	}

	/**
	 * Determine which schemas to output and render them.
	 */
	public function render_schema(): void {
		// Skip schema output when a SEO plugin already handles JSON-LD.
		if ( self::is_seo_plugin_active() ) {
			return;
		}

		// Never output schema on 404 pages.
		if ( is_404() ) {
			return;
		}

		$graph = [];

		// Organization schema on every page.
		$graph[] = $this->get_organization_schema();

		// WebPage schema for every valid page.
		$graph[] = $this->get_webpage_schema();

		if ( is_singular( 'post' ) ) {
			$graph[] = $this->get_article_schema();
			$graph[] = $this->get_person_schema();
			$graph[] = $this->get_faq_schema();
		}

		if ( is_singular( 'page' ) ) {
			$graph[] = $this->get_faq_schema();
		}

		// Remove null / empty entries before output.
		$graph = array_values( array_filter( $graph ) );

		/**
		 * Filter the @graph schemas before output.
		 *
		 * @param array[] $graph Array of schema arrays.
		 */
		$graph = apply_filters( 'geo_optimizer_schemas', $graph );

		if ( empty( $graph ) ) {
			return;
		}

		$this->print_json_ld( [
			'@context' => 'https://schema.org',
			'@graph'   => $graph,
		] );
	}

	// -------------------------------------------------------------------------
	// Schema builders
	// -------------------------------------------------------------------------

	/**
	 * Build the WebPage schema for the current request.
	 *
	 * @return array<string, mixed>|null
	 */
	private function get_webpage_schema(): ?array {
		$schema = [
			'@type' => 'WebPage',
			'url'   => $this->get_current_url(),
		];

		// Only fetch post data on singular pages.
		if ( is_singular() ) {
			$post = get_queried_object();

			if ( $post instanceof \WP_Post ) {
				$schema['name']          = get_the_title( $post );
				$schema['description']   = $this->get_meta_description( $post );
				$schema['datePublished'] = get_the_date( 'c', $post );
				$schema['dateModified']  = get_the_modified_date( 'c', $post );
				$schema['isPartOf']      = [
					'@type' => 'WebSite',
					'name'  => get_bloginfo( 'name' ),
					'url'   => home_url( '/' ),
				];
			}
		} elseif ( is_front_page() ) {
			$schema['name']        = get_bloginfo( 'name' );
			$schema['description'] = get_bloginfo( 'description' );
		}

		// Add SearchAction on the homepage.
		if ( is_front_page() ) {
			$schema['potentialAction'] = [
				'@type'       => 'SearchAction',
				'target'      => [
					'@type'        => 'EntryPoint',
					'urlTemplate'  => home_url( '/?s={search_term_string}' ),
				],
				'query-input' => 'required name=search_term_string',
			];
		}

		return $schema;
	}

	/**
	 * Build the Article schema for the current post.
	 *
	 * @return array<string, mixed>|null
	 */
	private function get_article_schema(): ?array {
		$post = get_queried_object();

		if ( ! $post instanceof \WP_Post ) {
			return null;
		}

		return [
			'@type'         => 'Article',
			'headline'      => get_the_title( $post ),
			'description'   => $this->get_meta_description( $post ),
			'datePublished' => get_the_date( 'c', $post ),
			'dateModified'  => get_the_modified_date( 'c', $post ),
			'url'           => get_permalink( $post ),
			'author'        => [
				'@type' => 'Person',
				'name'  => get_the_author_meta( 'display_name', $post->post_author ),
			],
			'publisher'     => [
				'@type' => 'Organization',
				'name'  => get_bloginfo( 'name' ),
			],
		];
	}

	/**
	 * Build the Organization schema.
	 *
	 * @return array<string, mixed>
	 */
	private function get_organization_schema(): array {
		$schema = [
			'@type' => 'Organization',
			'name'  => get_bloginfo( 'name' ),
			'url'   => home_url( '/' ),
		];

		$custom_logo_id = get_theme_mod( 'custom_logo' );
		if ( $custom_logo_id ) {
			$logo_url = wp_get_attachment_image_url( (int) $custom_logo_id, 'full' );
			if ( $logo_url ) {
				$schema['logo'] = $logo_url;
			}
		}

		return $schema;
	}

	/**
	 * Build the Person schema for the current post author.
	 *
	 * @return array<string, mixed>|null
	 */
	private function get_person_schema(): ?array {
		$post = get_queried_object();

		if ( ! $post instanceof \WP_Post ) {
			return null;
		}

		$author_id = (int) $post->post_author;

		return [
			'@type'       => 'Person',
			'name'        => get_the_author_meta( 'display_name', $author_id ),
			'description' => get_the_author_meta( 'description', $author_id ),
			'url'         => get_author_posts_url( $author_id ),
		];
	}

	/**
	 * Build FAQPage schema from headings that end with a question mark.
	 *
	 * @return array<string, mixed>|null
	 */
	private function get_faq_schema(): ?array {
		$post = get_queried_object();

		if ( ! $post instanceof \WP_Post ) {
			return null;
		}

		$content = $post->post_content;
		$pairs   = [];

		if ( preg_match_all(
			'/<h[2-3][^>]*>(.+?\?)<\/h[2-3]>\s*(<p[^>]*>.+?<\/p>)/si',
			wp_kses_post( $content ),
			$matches,
			PREG_SET_ORDER
		) ) {
			foreach ( $matches as $match ) {
				$pairs[] = [
					'@type'          => 'Question',
					'name'           => wp_strip_all_tags( $match[1] ),
					'acceptedAnswer' => [
						'@type' => 'Answer',
						'text'  => wp_strip_all_tags( $match[2] ),
					],
				];
			}
		}

		if ( empty( $pairs ) ) {
			return null;
		}

		return [
			'@type'      => 'FAQPage',
			'mainEntity' => $pairs,
		];
	}

	// -------------------------------------------------------------------------
	// Helpers
	// -------------------------------------------------------------------------

	/**
	 * Get a meta description for the post (excerpt fallback).
	 */
	private function get_meta_description( \WP_Post $post ): string {
		$desc = get_post_meta( $post->ID, '_geo_optimizer_meta_description', true );
		if ( $desc ) {
			return (string) $desc;
		}

		if ( $post->post_excerpt ) {
			return wp_strip_all_tags( $post->post_excerpt );
		}

		return wp_trim_words( wp_strip_all_tags( $post->post_content ), 30, '…' );
	}

	/**
	 * Get the URL for the current request.
	 */
	private function get_current_url(): string {
		if ( is_singular() ) {
			$obj = get_queried_object();
			if ( $obj instanceof \WP_Post ) {
				return (string) get_permalink( $obj );
			}
		}

		return home_url( add_query_arg( [] ) );
	}

	/**
	 * Print a single JSON-LD script block.
	 *
	 * @param array<string, mixed> $schema Schema data.
	 */
	private function print_json_ld( array $schema ): void {
		echo '<script type="application/ld+json">';
		echo wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
		echo '</script>' . "\n";
	}
}
