<?php
/**
 * LLMs.txt — serves a Markdown file at /llms.txt for AI crawlers.
 *
 * @package GEO_Optimizer
 */

declare(strict_types=1);

namespace GEO_Optimizer;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers a rewrite rule for /llms.txt and renders the output.
 */
class Llms_Txt {

	/**
	 * Register hooks.
	 */
	public function register(): void {
		add_action( 'init', [ $this, 'add_rewrite_rules' ] );
		add_filter( 'query_vars', [ $this, 'add_query_vars' ] );
		add_action( 'template_redirect', [ $this, 'render' ] );
	}

	/**
	 * Add the /llms.txt rewrite rule.
	 */
	public function add_rewrite_rules(): void {
		add_rewrite_rule( '^llms\.txt$', 'index.php?geo_llms_txt=1', 'top' );
	}

	/**
	 * Register the custom query var.
	 *
	 * @param string[] $vars Existing query vars.
	 * @return string[]
	 */
	public function add_query_vars( array $vars ): array {
		$vars[] = 'geo_llms_txt';
		return $vars;
	}

	/**
	 * Intercept the request and serve the Markdown output.
	 */
	public function render(): void {
		if ( ! get_query_var( 'geo_llms_txt' ) ) {
			return;
		}

		header( 'Content-Type: text/plain; charset=utf-8' );
		header( 'X-Robots-Tag: noindex' );

		echo esc_html( $this->build_markdown() );
		exit;
	}

	/**
	 * Build the full Markdown document.
	 */
	private function build_markdown(): string {
		$site_name = get_bloginfo( 'name' );
		$site_desc = get_bloginfo( 'description' );

		$lines   = [];
		$lines[] = "# {$site_name}";
		$lines[] = '';
		$lines[] = "> {$site_desc}";
		$lines[] = '';

		// Pages section.
		$lines[] = '## Pages';
		$lines[] = '';
		$this->append_posts( $lines, 'page' );

		// Posts section.
		$lines[] = '## Posts';
		$lines[] = '';
		$this->append_posts( $lines, 'post' );

		/**
		 * Filter the llms.txt Markdown lines before joining.
		 *
		 * @param string[] $lines Markdown lines.
		 */
		$lines = apply_filters( 'geo_optimizer_llms_txt_lines', $lines );

		return implode( "\n", $lines ) . "\n";
	}

	/**
	 * Append posts of a given type to the Markdown lines.
	 *
	 * @param string[] $lines     Lines array (passed by reference).
	 * @param string   $post_type Post type slug.
	 */
	private function append_posts( array &$lines, string $post_type ): void {
		$query = new \WP_Query( [
			'post_type'      => $post_type,
			'post_status'    => 'publish',
			'posts_per_page' => 500,
			'orderby'        => 'title',
			'order'          => 'ASC',
			'no_found_rows'  => true,
		] );

		foreach ( $query->posts as $post ) {
			$title = get_the_title( $post );
			$url   = get_permalink( $post );
			$desc  = $this->get_description( $post );

			$lines[] = "- [{$title}]({$url})";
			if ( $desc ) {
				$lines[] = "  {$desc}";
			}
		}

		$lines[] = '';
		wp_reset_postdata();
	}

	/**
	 * Get a short description for the post.
	 */
	private function get_description( \WP_Post $post ): string {
		// Prefer custom meta description.
		$meta = get_post_meta( $post->ID, '_geo_optimizer_meta_description', true );
		if ( $meta ) {
			return (string) $meta;
		}

		if ( $post->post_excerpt ) {
			return wp_strip_all_tags( $post->post_excerpt );
		}

		return wp_trim_words( wp_strip_all_tags( $post->post_content ), 25, '…' );
	}
}
