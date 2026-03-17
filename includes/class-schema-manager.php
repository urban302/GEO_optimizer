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
			esc_html__( 'GEO Optimizer:', 'geo-optimizer' ),
			esc_html__( 'RankMath, Yoast of AIOSEO gedetecteerd. Schema-output is automatisch uitgeschakeld om conflicten te voorkomen. De llms.txt en GEO Score werken normaal.', 'geo-optimizer' )
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

		$schemas = [];

		// Organization schema on every page.
		$schemas[] = $this->get_organization_schema();

		if ( is_singular( 'post' ) ) {
			$schemas[] = $this->get_article_schema();
			$schemas[] = $this->get_person_schema();

			$faq = $this->get_faq_schema();
			if ( $faq ) {
				$schemas[] = $faq;
			}
		}

		if ( is_singular( 'page' ) ) {
			$faq = $this->get_faq_schema();
			if ( $faq ) {
				$schemas[] = $faq;
			}
		}

		/**
		 * Filter the JSON-LD schemas before output.
		 *
		 * @param array[] $schemas Array of schema arrays.
		 */
		$schemas = apply_filters( 'geo_optimizer_schemas', array_filter( $schemas ) );

		foreach ( $schemas as $schema ) {
			$this->print_json_ld( $schema );
		}
	}

	/**
	 * Build the Article schema for the current post.
	 *
	 * @return array<string, mixed>
	 */
	private function get_article_schema(): array {
		$post = get_post();

		return [
			'@context'      => 'https://schema.org',
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
			'@context' => 'https://schema.org',
			'@type'    => 'Organization',
			'name'     => get_bloginfo( 'name' ),
			'url'      => home_url( '/' ),
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
	 * @return array<string, mixed>
	 */
	private function get_person_schema(): array {
		$post      = get_post();
		$author_id = (int) $post->post_author;

		$schema = [
			'@context'    => 'https://schema.org',
			'@type'       => 'Person',
			'name'        => get_the_author_meta( 'display_name', $author_id ),
			'description' => get_the_author_meta( 'description', $author_id ),
			'url'         => get_author_posts_url( $author_id ),
		];

		return $schema;
	}

	/**
	 * Build FAQPage schema from FAQ blocks or shortcodes in the current post.
	 *
	 * Looks for:
	 *  - Gutenberg core/heading + core/paragraph pairs with question-style headings.
	 *  - Content between <h2>/<h3> tags that end with a question mark.
	 *
	 * @return array<string, mixed>|null
	 */
	private function get_faq_schema(): ?array {
		$post    = get_post();
		$content = $post->post_content;

		// Extract question/answer pairs from headings ending with "?".
		$pairs = [];

		if ( preg_match_all(
			'/<h[2-3][^>]*>(.+?\?)<\/h[2-3]>\s*(<p[^>]*>.+?<\/p>)/si',
			apply_filters( 'the_content', $content ),
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
			'@context'   => 'https://schema.org',
			'@type'      => 'FAQPage',
			'mainEntity' => $pairs,
		];
	}

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
