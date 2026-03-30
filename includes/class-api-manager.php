<?php
/**
 * API Manager — handles communication with the AI analysis engine.
 *
 * @package GEO_Optimizer
 */

declare(strict_types=1);

namespace GEO_Optimizer;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Manages API requests, caching, and error handling for AI content analysis.
 */
class API_Manager {

	private const API_ENDPOINT    = 'https://api.anthropic.com/v1/messages';
	private const API_VERSION     = '2023-06-01';
	private const MODEL           = 'claude-opus-4-5';
	private const MAX_TOKENS      = 1000;
	private const TIMEOUT         = 30;
	private const CACHE_TTL       = DAY_IN_SECONDS;
	private const OPTION_API_KEY  = 'georank_api_key';

	/**
	 * Retrieve the stored API key.
	 */
	public static function get_api_key(): string {
		return (string) get_option( self::OPTION_API_KEY, '' );
	}

	/**
	 * Check whether a valid API key is configured.
	 */
	public static function has_api_key(): bool {
		return '' !== self::get_api_key();
	}

	/**
	 * Validate the stored API key by making a minimal request.
	 *
	 * @return true|\WP_Error True on success, WP_Error on failure.
	 */
	public static function validate_api_key(): true|\WP_Error {
		$api_key = self::get_api_key();

		if ( '' === $api_key ) {
			return new \WP_Error(
				'geo_optimizer_no_key',
				__( 'No API key configured.', 'georank' )
			);
		}

		$response = wp_remote_post(
			self::API_ENDPOINT,
			[
				'timeout' => 15,
				'headers' => self::build_headers( $api_key ),
				'body'    => wp_json_encode( [
					'model'      => self::MODEL,
					'max_tokens' => 1,
					'messages'   => [
						[
							'role'    => 'user',
							'content' => 'Hi',
						],
					],
				] ),
			]
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$code = wp_remote_retrieve_response_code( $response );

		if ( $code >= 400 ) {
			$body    = json_decode( wp_remote_retrieve_body( $response ), true );
			$message = $body['error']['message'] ?? __( 'Invalid API key or server error.', 'georank' );
			return new \WP_Error( 'geo_optimizer_api_error', sanitize_text_field( $message ) );
		}

		return true;
	}

	/**
	 * Send content to the AI engine for analysis.
	 *
	 * Results are cached for 24 hours, keyed by post ID and modification date.
	 *
	 * @param \WP_Post $post   The post to analyze.
	 * @param string   $prompt The system/user prompt to send.
	 * @return array|\WP_Error Parsed JSON response or WP_Error.
	 */
	public static function analyze_content( \WP_Post $post, string $prompt = '' ): array|\WP_Error {
		$cache_key = 'georank_analysis_' . $post->ID . '_' . md5( $post->post_modified );
		$cached    = get_transient( $cache_key );

		if ( false !== $cached && is_array( $cached ) ) {
			return $cached;
		}

		$api_key = self::get_api_key();

		if ( '' === $api_key ) {
			return new \WP_Error(
				'geo_optimizer_no_key',
				__( 'No API key configured.', 'georank' )
			);
		}

		$content = wp_strip_all_tags( $post->post_content );

		if ( '' === $prompt ) {
			$prompt = sprintf(
				'Analyseer de volgende content en geef verbeterpunten voor AI-vindbaarheid.\n\nTitel: %s\n\nContent:\n%s',
				$post->post_title,
				mb_substr( $content, 0, 4000 )
			);
		}

		$response = wp_remote_post(
			self::API_ENDPOINT,
			[
				'timeout' => self::TIMEOUT,
				'headers' => self::build_headers( $api_key ),
				'body'    => wp_json_encode( [
					'model'      => self::MODEL,
					'max_tokens' => self::MAX_TOKENS,
					'messages'   => [
						[
							'role'    => 'user',
							'content' => $prompt,
						],
					],
				] ),
			]
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$code = wp_remote_retrieve_response_code( $response );

		if ( $code >= 400 ) {
			$body    = json_decode( wp_remote_retrieve_body( $response ), true );
			$message = $body['error']['message'] ?? __( 'API request failed.', 'georank' );
			return new \WP_Error( 'geo_optimizer_api_error', sanitize_text_field( $message ) );
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( ! is_array( $body ) || empty( $body['content'][0]['text'] ) ) {
			return new \WP_Error(
				'geo_optimizer_parse_error',
				__( 'Invalid response from AI engine.', 'georank' )
			);
		}

		$text   = $body['content'][0]['text'];
		$parsed = json_decode( $text, true );

		if ( ! is_array( $parsed ) ) {
			$parsed = [ 'raw' => wp_kses_post( $text ) ];
		} else {
			array_walk_recursive( $parsed, static function ( &$value ) {
				if ( is_string( $value ) ) {
					$value = wp_kses_post( $value );
				}
			} );
		}

		set_transient( $cache_key, $parsed, self::CACHE_TTL );

		return $parsed;
	}

	/**
	 * Build the HTTP headers for an API request.
	 *
	 * @param string $api_key The API key to use.
	 * @return array<string, string>
	 */
	private static function build_headers( string $api_key ): array {
		return [
			'Content-Type'      => 'application/json',
			'x-api-key'         => $api_key,
			'anthropic-version' => self::API_VERSION,
		];
	}
}
