<?php
/**
 * Settings Page — self-contained admin settings for GEO Optimizer.
 *
 * Registers the menu, Settings API fields, renders tabs, and enqueues assets.
 *
 * @package GEO_Optimizer
 */

declare(strict_types=1);

namespace GEO_Optimizer;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles the Instellingen > GEO Optimizer settings page.
 */
class Settings_Page {

	private const OPTION_GROUP = 'geo_optimizer_settings';
	private const OPTION_NAME  = 'geo_optimizer_settings';
	private const PAGE_SLUG    = 'geo-optimizer-settings';
	private const NONCE_ACTION = 'geo_optimizer_settings_save';

	/**
	 * Cached option values, loaded once per request.
	 *
	 * @var array<string, string>
	 */
	private array $options = [];

	// -------------------------------------------------------------------------
	// Bootstrap
	// -------------------------------------------------------------------------

	/**
	 * Register all hooks.
	 */
	public function register(): void {
		add_action( 'admin_menu', [ $this, 'add_menu' ] );
		add_action( 'admin_init', [ $this, 'register_settings' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
	}

	/**
	 * Read a single saved value (static helper for use anywhere).
	 */
	public static function get( string $key, mixed $default = '' ): mixed {
		$options = (array) get_option( self::OPTION_NAME, [] );
		return $options[ $key ] ?? $default;
	}

	// -------------------------------------------------------------------------
	// Menu
	// -------------------------------------------------------------------------

	/**
	 * Register the page under Instellingen.
	 */
	public function add_menu(): void {
		add_options_page(
			__( 'GEO Optimizer — Instellingen', 'geo-optimizer' ),
			__( 'GEO Optimizer', 'geo-optimizer' ),
			'manage_options',
			self::PAGE_SLUG,
			[ $this, 'render_page' ]
		);
	}

	// -------------------------------------------------------------------------
	// Settings API registration
	// -------------------------------------------------------------------------

	/**
	 * Register the setting plus all sections and fields.
	 */
	public function register_settings(): void {
		$this->options = (array) get_option( self::OPTION_NAME, [] );

		register_setting(
			self::OPTION_GROUP,
			self::OPTION_NAME,
			[
				'type'              => 'array',
				'sanitize_callback' => [ $this, 'sanitize' ],
				'default'           => [],
			]
		);

		$this->register_organization_fields();
		$this->register_address_fields();
	}

	/**
	 * Section + fields for the "Organisatie" tab.
	 */
	private function register_organization_fields(): void {
		$section = 'geo_optimizer_section_org';
		$page    = self::PAGE_SLUG . '-organization';

		add_settings_section(
			$section,
			__( 'Organisatie-instellingen', 'geo-optimizer' ),
			static fn () => printf(
				'<p>%s</p>',
				esc_html__( 'Configureer je organisatiegegevens voor structured data.', 'geo-optimizer' )
			),
			$page
		);

		// org_name — text.
		add_settings_field( 'org_name', __( 'Organisatienaam', 'geo-optimizer' ), [ $this, 'render_text' ], $page, $section, [
			'id' => 'org_name',
		] );

		// org_type — select.
		add_settings_field( 'org_type', __( 'Organisatietype', 'geo-optimizer' ), [ $this, 'render_select' ], $page, $section, [
			'id'      => 'org_type',
			'choices' => [
				'Organization'  => 'Organization',
				'LocalBusiness' => 'LocalBusiness',
				'Person'        => 'Person',
			],
		] );

		// org_logo — text + media uploader.
		add_settings_field( 'org_logo', __( 'Logo URL', 'geo-optimizer' ), [ $this, 'render_media' ], $page, $section, [
			'id' => 'org_logo',
		] );

		// org_description — textarea.
		add_settings_field( 'org_description', __( 'Beschrijving', 'geo-optimizer' ), [ $this, 'render_textarea' ], $page, $section, [
			'id' => 'org_description',
		] );

		// Social URLs.
		add_settings_field( 'social_linkedin', __( 'LinkedIn URL', 'geo-optimizer' ), [ $this, 'render_text' ], $page, $section, [
			'id'   => 'social_linkedin',
			'type' => 'url',
		] );

		add_settings_field( 'social_twitter', __( 'Twitter/X URL', 'geo-optimizer' ), [ $this, 'render_text' ], $page, $section, [
			'id'   => 'social_twitter',
			'type' => 'url',
		] );

		add_settings_field( 'social_facebook', __( 'Facebook URL', 'geo-optimizer' ), [ $this, 'render_text' ], $page, $section, [
			'id'   => 'social_facebook',
			'type' => 'url',
		] );
	}

	/**
	 * Section + fields for the "Adres" tab.
	 */
	private function register_address_fields(): void {
		$section = 'geo_optimizer_section_addr';
		$page    = self::PAGE_SLUG . '-address';

		add_settings_section(
			$section,
			__( 'Adresinstellingen', 'geo-optimizer' ),
			static fn () => printf(
				'<p>%s</p>',
				esc_html__( 'Voeg adresgegevens toe voor LocalBusiness schema.', 'geo-optimizer' )
			),
			$page
		);

		add_settings_field( 'address_street', __( 'Straat + huisnummer', 'geo-optimizer' ), [ $this, 'render_text' ], $page, $section, [
			'id' => 'address_street',
		] );

		add_settings_field( 'address_city', __( 'Stad', 'geo-optimizer' ), [ $this, 'render_text' ], $page, $section, [
			'id' => 'address_city',
		] );

		add_settings_field( 'address_zip', __( 'Postcode', 'geo-optimizer' ), [ $this, 'render_text' ], $page, $section, [
			'id' => 'address_zip',
		] );

		add_settings_field( 'address_country', __( 'Land', 'geo-optimizer' ), [ $this, 'render_select' ], $page, $section, [
			'id'      => 'address_country',
			'default' => 'NL',
			'choices' => [
				'NL' => __( 'Nederland', 'geo-optimizer' ),
				'BE' => __( 'België', 'geo-optimizer' ),
				'DE' => __( 'Duitsland', 'geo-optimizer' ),
				'GB' => __( 'Verenigd Koninkrijk', 'geo-optimizer' ),
				'US' => __( 'Verenigde Staten', 'geo-optimizer' ),
			],
		] );
	}

	// -------------------------------------------------------------------------
	// Sanitization
	// -------------------------------------------------------------------------

	/**
	 * Sanitize all incoming values before they are saved.
	 *
	 * @param mixed $input Raw form data.
	 * @return array<string, string>
	 */
	public function sanitize( mixed $input ): array {
		if ( ! is_array( $input ) ) {
			return [];
		}

		$clean = [];

		// --- Text fields (sanitize_text_field) --------------------------------
		$text_keys = [ 'org_name', 'address_street', 'address_city', 'address_zip' ];
		foreach ( $text_keys as $key ) {
			$clean[ $key ] = isset( $input[ $key ] )
				? sanitize_text_field( $input[ $key ] )
				: '';
		}

		// --- Textarea field (sanitize_textarea_field) -------------------------
		$clean['org_description'] = isset( $input['org_description'] )
			? sanitize_textarea_field( $input['org_description'] )
			: '';

		// --- URL fields (esc_url_raw) -----------------------------------------
		$url_keys = [ 'org_logo', 'social_linkedin', 'social_twitter', 'social_facebook' ];
		foreach ( $url_keys as $key ) {
			$clean[ $key ] = isset( $input[ $key ] )
				? esc_url_raw( $input[ $key ] )
				: '';
		}

		// --- Select: org_type -------------------------------------------------
		$valid_types       = [ 'Organization', 'LocalBusiness', 'Person' ];
		$clean['org_type'] = in_array( $input['org_type'] ?? '', $valid_types, true )
			? $input['org_type']
			: 'Organization';

		// --- Select: address_country ------------------------------------------
		$valid_countries          = [ 'NL', 'BE', 'DE', 'GB', 'US' ];
		$clean['address_country'] = in_array( $input['address_country'] ?? '', $valid_countries, true )
			? $input['address_country']
			: 'NL';

		return $clean;
	}

	// -------------------------------------------------------------------------
	// Field renderers
	// -------------------------------------------------------------------------

	/**
	 * Text / URL input.
	 *
	 * @param array{id: string, type?: string} $args Field arguments.
	 */
	public function render_text( array $args ): void {
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
	 * Select dropdown.
	 *
	 * @param array{id: string, choices: array<string,string>, default?: string} $args Field arguments.
	 */
	public function render_select( array $args ): void {
		$id      = $args['id'];
		$choices = $args['choices'];
		$default = $args['default'] ?? '';
		$value   = $this->options[ $id ] ?? $default;

		printf(
			'<select id="%s" name="%s[%s]">',
			esc_attr( $id ),
			esc_attr( self::OPTION_NAME ),
			esc_attr( $id )
		);

		foreach ( $choices as $opt_value => $opt_label ) {
			printf(
				'<option value="%s"%s>%s</option>',
				esc_attr( (string) $opt_value ),
				selected( $value, (string) $opt_value, false ),
				esc_html( $opt_label )
			);
		}

		echo '</select>';
	}

	/**
	 * Textarea.
	 *
	 * @param array{id: string} $args Field arguments.
	 */
	public function render_textarea( array $args ): void {
		$id    = $args['id'];
		$value = $this->options[ $id ] ?? '';

		printf(
			'<textarea id="%s" name="%s[%s]" rows="5" class="large-text">%s</textarea>',
			esc_attr( $id ),
			esc_attr( self::OPTION_NAME ),
			esc_attr( $id ),
			esc_textarea( (string) $value )
		);
	}

	/**
	 * URL input with "Selecteer afbeelding" media-library button.
	 *
	 * @param array{id: string} $args Field arguments.
	 */
	public function render_media( array $args ): void {
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
	// Assets
	// -------------------------------------------------------------------------

	/**
	 * Enqueue the media uploader on the settings page.
	 *
	 * @param string $hook_suffix Current admin page.
	 */
	public function enqueue_assets( string $hook_suffix ): void {
		if ( 'settings_page_' . self::PAGE_SLUG !== $hook_suffix ) {
			return;
		}

		wp_enqueue_media();

		wp_enqueue_script(
			'geo-optimizer-admin-settings',
			GEO_OPTIMIZER_URL . 'assets/js/admin-settings.js',
			[ 'jquery' ],
			GEO_OPTIMIZER_VERSION,
			true
		);
	}

	// -------------------------------------------------------------------------
	// Page renderer
	// -------------------------------------------------------------------------

	/**
	 * Output the full settings page with tabs.
	 */
	public function render_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only tab switch.
		$active_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'organization';

		if ( ! in_array( $active_tab, [ 'organization', 'address' ], true ) ) {
			$active_tab = 'organization';
		}

		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'GEO Optimizer — Instellingen', 'geo-optimizer' ); ?></h1>

			<nav class="nav-tab-wrapper">
				<a href="<?php echo esc_url( admin_url( 'options-general.php?page=' . self::PAGE_SLUG . '&tab=organization' ) ); ?>"
				   class="nav-tab <?php echo 'organization' === $active_tab ? 'nav-tab-active' : ''; ?>">
					<?php esc_html_e( 'Organisatie', 'geo-optimizer' ); ?>
				</a>
				<a href="<?php echo esc_url( admin_url( 'options-general.php?page=' . self::PAGE_SLUG . '&tab=address' ) ); ?>"
				   class="nav-tab <?php echo 'address' === $active_tab ? 'nav-tab-active' : ''; ?>">
					<?php esc_html_e( 'Adres', 'geo-optimizer' ); ?>
				</a>
			</nav>

			<form method="post" action="options.php">
				<?php
				settings_fields( self::OPTION_GROUP );
				wp_nonce_field( self::NONCE_ACTION, '_geo_optimizer_nonce' );

				if ( 'organization' === $active_tab ) {
					do_settings_sections( self::PAGE_SLUG . '-organization' );
				} else {
					do_settings_sections( self::PAGE_SLUG . '-address' );
				}

				submit_button( __( 'Instellingen opslaan', 'geo-optimizer' ) );
				?>
			</form>
		</div>
		<?php
	}
}
