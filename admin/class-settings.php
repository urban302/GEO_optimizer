<?php
/**
 * Settings — WordPress Settings API integration with tabs.
 *
 * @package GEO_Optimizer
 */

declare(strict_types=1);

namespace GEO_Optimizer;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers settings, sections, and fields for the GEO Optimizer settings page.
 */
class Settings {

	private const OPTION_GROUP = 'geo_optimizer_settings';
	private const OPTION_NAME  = 'geo_optimizer_settings';
	private const NONCE_ACTION = 'geo_optimizer_settings_save';

	/**
	 * Cached settings.
	 *
	 * @var array<string, mixed>
	 */
	private array $options = [];

	/**
	 * Register hooks.
	 */
	public function register(): void {
		add_action( 'admin_init', [ $this, 'register_settings' ] );
	}

	/**
	 * Get a single option value with a default fallback.
	 */
	public static function get( string $key, mixed $default = '' ): mixed {
		$options = get_option( self::OPTION_NAME, [] );
		return $options[ $key ] ?? $default;
	}

	/**
	 * Register the setting and all sections/fields.
	 */
	public function register_settings(): void {
		$this->options = get_option( self::OPTION_NAME, [] );

		register_setting(
			self::OPTION_GROUP,
			self::OPTION_NAME,
			[
				'type'              => 'array',
				'sanitize_callback' => [ $this, 'sanitize' ],
				'default'           => [],
			]
		);

		$this->register_organization_section();
		$this->register_address_section();
	}

	/**
	 * Register the "Organisation" tab fields.
	 */
	private function register_organization_section(): void {
		$section = 'geo_optimizer_organization';

		add_settings_section(
			$section,
			__( 'Organisatie-instellingen', 'geo-optimizer' ),
			static fn () => printf(
				'<p>%s</p>',
				esc_html__( 'Configureer je organisatiegegevens voor structured data.', 'geo-optimizer' )
			),
			'geo-optimizer-organization'
		);

		$this->add_text_field( $section, 'org_name', __( 'Organisatienaam', 'geo-optimizer' ), 'geo-optimizer-organization' );

		add_settings_field(
			'org_type',
			__( 'Organisatietype', 'geo-optimizer' ),
			[ $this, 'render_select_field' ],
			'geo-optimizer-organization',
			$section,
			[
				'id'      => 'org_type',
				'options' => [
					'Organization'  => 'Organization',
					'LocalBusiness' => 'LocalBusiness',
					'Person'        => 'Person',
				],
			]
		);

		add_settings_field(
			'org_logo',
			__( 'Logo URL', 'geo-optimizer' ),
			[ $this, 'render_media_field' ],
			'geo-optimizer-organization',
			$section,
			[ 'id' => 'org_logo' ]
		);

		add_settings_field(
			'org_description',
			__( 'Beschrijving', 'geo-optimizer' ),
			[ $this, 'render_textarea_field' ],
			'geo-optimizer-organization',
			$section,
			[ 'id' => 'org_description' ]
		);

		$this->add_text_field( $section, 'social_linkedin', __( 'LinkedIn URL', 'geo-optimizer' ), 'geo-optimizer-organization', 'url' );
		$this->add_text_field( $section, 'social_twitter', __( 'Twitter/X URL', 'geo-optimizer' ), 'geo-optimizer-organization', 'url' );
		$this->add_text_field( $section, 'social_facebook', __( 'Facebook URL', 'geo-optimizer' ), 'geo-optimizer-organization', 'url' );
	}

	/**
	 * Register the "Adres" tab fields.
	 */
	private function register_address_section(): void {
		$section = 'geo_optimizer_address';

		add_settings_section(
			$section,
			__( 'Adresinstellingen', 'geo-optimizer' ),
			static fn () => printf(
				'<p>%s</p>',
				esc_html__( 'Voeg adresgegevens toe voor LocalBusiness schema.', 'geo-optimizer' )
			),
			'geo-optimizer-address'
		);

		$this->add_text_field( $section, 'address_street', __( 'Straat', 'geo-optimizer' ), 'geo-optimizer-address' );
		$this->add_text_field( $section, 'address_city', __( 'Stad', 'geo-optimizer' ), 'geo-optimizer-address' );
		$this->add_text_field( $section, 'address_postcode', __( 'Postcode', 'geo-optimizer' ), 'geo-optimizer-address' );

		add_settings_field(
			'address_country',
			__( 'Land', 'geo-optimizer' ),
			[ $this, 'render_select_field' ],
			'geo-optimizer-address',
			$section,
			[
				'id'      => 'address_country',
				'default' => 'NL',
				'options' => self::get_countries(),
			]
		);
	}

	/**
	 * Helper to add a simple text field.
	 */
	private function add_text_field( string $section, string $id, string $label, string $page, string $type = 'text' ): void {
		add_settings_field(
			$id,
			$label,
			[ $this, 'render_text_field' ],
			$page,
			$section,
			[
				'id'   => $id,
				'type' => $type,
			]
		);
	}

	// -------------------------------------------------------------------------
	// Field renderers
	// -------------------------------------------------------------------------

	/**
	 * Render a text input field.
	 *
	 * @param array{id: string, type?: string} $args Field arguments.
	 */
	public function render_text_field( array $args ): void {
		$id    = $args['id'];
		$type  = $args['type'] ?? 'text';
		$value = $this->options[ $id ] ?? '';

		printf(
			'<input type="%s" id="%s" name="%s[%s]" value="%s" class="regular-text" />',
			esc_attr( $type ),
			esc_attr( $id ),
			esc_attr( self::OPTION_NAME ),
			esc_attr( $id ),
			esc_attr( (string) $value )
		);
	}

	/**
	 * Render a select field.
	 *
	 * @param array{id: string, options: array<string, string>, default?: string} $args Field arguments.
	 */
	public function render_select_field( array $args ): void {
		$id      = $args['id'];
		$options = $args['options'];
		$default = $args['default'] ?? '';
		$value   = $this->options[ $id ] ?? $default;

		printf(
			'<select id="%s" name="%s[%s]">',
			esc_attr( $id ),
			esc_attr( self::OPTION_NAME ),
			esc_attr( $id )
		);

		foreach ( $options as $opt_value => $opt_label ) {
			printf(
				'<option value="%s"%s>%s</option>',
				esc_attr( $opt_value ),
				selected( $value, $opt_value, false ),
				esc_html( $opt_label )
			);
		}

		echo '</select>';
	}

	/**
	 * Render a textarea field.
	 *
	 * @param array{id: string} $args Field arguments.
	 */
	public function render_textarea_field( array $args ): void {
		$id    = $args['id'];
		$value = $this->options[ $id ] ?? '';

		printf(
			'<textarea id="%s" name="%s[%s]" rows="4" class="large-text">%s</textarea>',
			esc_attr( $id ),
			esc_attr( self::OPTION_NAME ),
			esc_attr( $id ),
			esc_textarea( (string) $value )
		);
	}

	/**
	 * Render a text input with a media library upload button.
	 *
	 * @param array{id: string} $args Field arguments.
	 */
	public function render_media_field( array $args ): void {
		$id    = $args['id'];
		$value = $this->options[ $id ] ?? '';

		printf(
			'<input type="url" id="%s" name="%s[%s]" value="%s" class="regular-text geo-media-url" />',
			esc_attr( $id ),
			esc_attr( self::OPTION_NAME ),
			esc_attr( $id ),
			esc_url( (string) $value )
		);

		printf(
			' <button type="button" class="button geo-media-upload" data-target="%s">%s</button>',
			esc_attr( $id ),
			esc_html__( 'Selecteer afbeelding', 'geo-optimizer' )
		);

		if ( $value ) {
			printf(
				'<div class="geo-media-preview" style="margin-top:8px"><img src="%s" alt="" style="max-width:200px;height:auto" /></div>',
				esc_url( (string) $value )
			);
		}
	}

	// -------------------------------------------------------------------------
	// Sanitization
	// -------------------------------------------------------------------------

	/**
	 * Sanitize all settings before saving.
	 *
	 * @param mixed $input Raw input.
	 * @return array<string, string>
	 */
	public function sanitize( mixed $input ): array {
		if ( ! is_array( $input ) ) {
			return [];
		}

		$clean = [];

		$text_fields = [
			'org_name',
			'org_description',
			'address_street',
			'address_city',
			'address_postcode',
		];

		foreach ( $text_fields as $field ) {
			$clean[ $field ] = isset( $input[ $field ] ) ? sanitize_text_field( $input[ $field ] ) : '';
		}

		// Select fields.
		$valid_types = [ 'Organization', 'LocalBusiness', 'Person' ];
		$clean['org_type'] = in_array( $input['org_type'] ?? '', $valid_types, true )
			? $input['org_type']
			: 'Organization';

		$countries             = self::get_countries();
		$clean['address_country'] = isset( $input['address_country'] ) && array_key_exists( $input['address_country'], $countries )
			? $input['address_country']
			: 'NL';

		// URL fields.
		$url_fields = [ 'org_logo', 'social_linkedin', 'social_twitter', 'social_facebook' ];
		foreach ( $url_fields as $field ) {
			$clean[ $field ] = isset( $input[ $field ] ) ? esc_url_raw( $input[ $field ] ) : '';
		}

		return $clean;
	}

	// -------------------------------------------------------------------------
	// Rendering helpers
	// -------------------------------------------------------------------------

	/**
	 * Get the option group constant for use in settings_fields().
	 */
	public static function option_group(): string {
		return self::OPTION_GROUP;
	}

	/**
	 * Return a list of common countries (ISO 3166-1 alpha-2).
	 *
	 * @return array<string, string>
	 */
	private static function get_countries(): array {
		return [
			'NL' => __( 'Nederland', 'geo-optimizer' ),
			'BE' => __( 'België', 'geo-optimizer' ),
			'DE' => __( 'Duitsland', 'geo-optimizer' ),
			'FR' => __( 'Frankrijk', 'geo-optimizer' ),
			'GB' => __( 'Verenigd Koninkrijk', 'geo-optimizer' ),
			'US' => __( 'Verenigde Staten', 'geo-optimizer' ),
			'CA' => __( 'Canada', 'geo-optimizer' ),
			'AU' => __( 'Australië', 'geo-optimizer' ),
			'AT' => __( 'Oostenrijk', 'geo-optimizer' ),
			'CH' => __( 'Zwitserland', 'geo-optimizer' ),
			'ES' => __( 'Spanje', 'geo-optimizer' ),
			'IT' => __( 'Italië', 'geo-optimizer' ),
			'PT' => __( 'Portugal', 'geo-optimizer' ),
			'SE' => __( 'Zweden', 'geo-optimizer' ),
			'NO' => __( 'Noorwegen', 'geo-optimizer' ),
			'DK' => __( 'Denemarken', 'geo-optimizer' ),
			'PL' => __( 'Polen', 'geo-optimizer' ),
			'IE' => __( 'Ierland', 'geo-optimizer' ),
			'LU' => __( 'Luxemburg', 'geo-optimizer' ),
		];
	}
}
