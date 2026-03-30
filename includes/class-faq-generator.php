<?php
/**
 * FAQ Generator — generates FAQ questions via AI analysis.
 *
 * @package GEO_Optimizer
 */

declare(strict_types=1);

namespace GEO_Optimizer;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Generates FAQ question/answer pairs for a given post using the AI engine.
 */
class FAQ_Generator {

	/**
	 * Generate FAQ items for a post.
	 *
	 * @param \WP_Post $post The post to generate FAQs for.
	 * @return array|\WP_Error Array of {question, answer} objects or WP_Error.
	 */
	public static function generate( \WP_Post $post ): array|\WP_Error {
		$content = wp_strip_all_tags( $post->post_content );

		$prompt = sprintf(
			'Analyseer de volgende content en genereer precies 5 relevante FAQ vragen met antwoorden op basis van de tekst. '
			. 'Antwoord uitsluitend in JSON formaat: [{"question": "...", "answer": "..."}]. '
			. 'Geen extra tekst buiten de JSON array.'
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
			// Attempt to extract JSON array from the raw text.
			$raw = $result['raw'];
			if ( preg_match( '/\[[\s\S]*\]/', $raw, $matches ) ) {
				$parsed = json_decode( $matches[0], true );
				if ( is_array( $parsed ) ) {
					$result = $parsed;
				}
			}

			if ( isset( $result['raw'] ) ) {
				return new \WP_Error(
					'geo_optimizer_faq_parse',
					__( 'Could not generate FAQ items from the AI response.', 'georank' )
				);
			}
		}

		// Ensure the array is a sequential list with the expected keys.
		$faqs = [];
		foreach ( $result as $item ) {
			if ( is_array( $item ) && ! empty( $item['question'] ) && ! empty( $item['answer'] ) ) {
				$faqs[] = [
					'question' => wp_kses_post( $item['question'] ),
					'answer'   => wp_kses_post( $item['answer'] ),
				];
			}
		}

		if ( [] === $faqs ) {
			return new \WP_Error(
				'geo_optimizer_faq_empty',
				__( 'No valid FAQ items received.', 'georank' )
			);
		}

		return $faqs;
	}
}
