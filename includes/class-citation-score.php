<?php
/**
 * Citation Score — AI-powered citability assessment.
 *
 * @package GEO_Optimizer
 */

declare(strict_types=1);

namespace GEO_Optimizer;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Calculates a citation score (0-100) indicating how likely AI search engines
 * are to cite the given content.
 */
class Citation_Score {

	/**
	 * Calculate the citation score for a post.
	 *
	 * @param \WP_Post $post The post to evaluate.
	 * @return array{score: int, improvements: string[]}|\WP_Error
	 */
	public static function calculate( \WP_Post $post ): array|\WP_Error {
		$content = wp_strip_all_tags( $post->post_content );

		$prompt = sprintf(
			'Beoordeel hoe goed de volgende content geciteerd zou worden door AI-zoekmachines. '
			. 'Geef een score van 0 tot 100 en precies 3 concrete verbeterpunten. '
			. 'Antwoord uitsluitend in JSON formaat: {"score": 75, "improvements": ["...", "...", "..."]}. '
			. 'Geen extra tekst buiten het JSON object.'
			. "\n\nTitel: %s\n\nContent:\n%s",
			$post->post_title,
			mb_substr( $content, 0, 4000 )
		);

		$result = API_Manager::analyze_content( $post, $prompt );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		// When analyze_content could not parse JSON it wraps in {raw: ...}.
		if ( isset( $result['raw'] ) ) {
			$raw = $result['raw'];
			if ( preg_match( '/\{[\s\S]*\}/', $raw, $matches ) ) {
				$parsed = json_decode( $matches[0], true );
				if ( is_array( $parsed ) ) {
					$result = $parsed;
				}
			}

			if ( isset( $result['raw'] ) ) {
				return new \WP_Error(
					'geo_optimizer_citation_parse',
					__( 'Could not calculate citation score from the AI response.', 'georank' )
				);
			}
		}

		$score        = isset( $result['score'] ) ? absint( $result['score'] ) : 0;
		$score        = min( $score, 100 );
		$improvements = [];

		if ( ! empty( $result['improvements'] ) && is_array( $result['improvements'] ) ) {
			foreach ( $result['improvements'] as $item ) {
				if ( is_string( $item ) ) {
					$improvements[] = wp_kses_post( $item );
				}
			}
		}

		return [
			'score'        => $score,
			'improvements' => array_slice( $improvements, 0, 3 ),
		];
	}
}
