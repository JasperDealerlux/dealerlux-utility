<?php
/**
 * Class Client_Form_Selector_Shortcode
 *
 * Generates a Select2 form selector using forms available in the currently
 * selected SSS client plugin.
 */

namespace DealerluxUtils\Shortcodes\Forms;

use DealerluxUtils\Services\Forms\Client_Forms_Provider;
use DealerluxUtils\Traits\Plugin_Assets as Plugin_Assets_Trait;
use DealerluxUtils\Traits\Singleton as Singleton_Trait;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Render a Select2 dropdown that redirects visitors to a selected form.
 *
 * Supported usage:
 *
 * [dl_form_selector]
 * [dl_form_selector url="/contact-us/"]
 * [dl_form_selector placeholder="Choose a form"]
 * [dl_form_selector allow_clear="true"]
 */
class Client_Form_Selector_Shortcode {

	/**
	 * Shortcode tag.
	 *
	 * @var string
	 */
	private $shortcode_tag = 'dl_form_selector';

	/**
	 * URL query parameter used for the selected form.
	 *
	 * @var string
	 */
	private $form_query_parameter = 'set_form';

	/**
	 * Select2 stylesheet handle.
	 *
	 * @var string
	 */
	private $select2_style_handle = 'dealerlux-utility-select2';

	/**
	 * Select2 script handle.
	 *
	 * @var string
	 */
	private $select2_script_handle = 'dealerlux-utility-select2';

	/**
	 * Selector script handle.
	 *
	 * @var string
	 */
	private $selector_script_handle = 'dealerlux-utility-form-selector';

	/**
	 * Selector stylesheet handle.
	 *
	 * @var string
	 */
	private $selector_style_handle = 'dealerlux-utility-form-selector';

	/**
	 * Select2 version.
	 *
	 * @var string
	 */
	private $select2_version = '4.0.13';

	/**
	 * Whether frontend assets have already been registered.
	 *
	 * @var bool
	 */
	private $assets_registered = false;

	/**
	 * Use the singleton loader.
	 */
	use Singleton_Trait;

	/**
	 * Use shared Dealerlux Utility asset helpers.
	 */
	use Plugin_Assets_Trait;

	/**
	 * Constructor.
	 */
	private function __construct() {}

	/**
	 * Determine whether this class should be registered.
	 *
	 * @return bool
	 */
	protected static function can_register() {
		return true;
	}

	/**
	 * Register WordPress hooks.
	 *
	 * @return void
	 */
	public function register_hooks() {
		add_shortcode(
			$this->shortcode_tag,
			array( $this, 'render_shortcode' )
		);
	}

	/**
	 * Render the form selector.
	 *
	 * Supported attributes:
	 *
	 * url
	 *     Optional custom base URL. Defaults to the current URL.
	 *
	 * placeholder
	 *     Empty Select2 option label.
	 *
	 * allow_clear
	 *     Whether Select2 should display its clear-selection control.
	 *
	 * class
	 *     Optional additional wrapper classes.
	 *
	 * @param array|string $attributes Shortcode attributes.
	 * @return string
	 */
	public function render_shortcode( $attributes = array() ) {
		$attributes = shortcode_atts(
			array(
				'url'         => '',
				'placeholder' => 'Select a form',
				'allow_clear' => 'true',
				'class'       => '',
			),
			is_array( $attributes )
				? $attributes
				: array(),
			$this->shortcode_tag
		);

		$options = Client_Forms_Provider::instance()
			->get_form_options();

		if ( empty( $options ) ) {
			return '';
		}

		$base_url = $this->resolve_base_url(
			$attributes['url']
		);

		if ( '' === $base_url ) {
			return '';
		}

		$this->enqueue_assets();

		$instance_id = wp_unique_id(
			'dealerlux-form-selector-'
		);

		$placeholder = sanitize_text_field(
			$attributes['placeholder']
		);

		if ( '' === $placeholder ) {
			$placeholder = 'Select a form';
		}

		$allow_clear = $this->normalize_boolean(
			$attributes['allow_clear'],
			true
		);

		$wrapper_classes = array(
			'dealerlux-form-selector',
		);

		$custom_classes = $this->normalize_css_classes(
			$attributes['class']
		);

		if ( ! empty( $custom_classes ) ) {
			$wrapper_classes = array_merge(
				$wrapper_classes,
				$custom_classes
			);
		}

		$current_form = $this->get_requested_form_key();

		ob_start();
		?>
		<div
			class="<?php echo esc_attr( implode( ' ', $wrapper_classes ) ); ?>"
			data-dealerlux-form-selector
		>
			<label
				class="dealerlux-form-selector__label screen-reader-text"
				for="<?php echo esc_attr( $instance_id ); ?>"
			>
				<?php echo esc_html( $placeholder ); ?>
			</label>

			<select
				id="<?php echo esc_attr( $instance_id ); ?>"
				class="dealerlux-form-selector__select"
				data-placeholder="<?php echo esc_attr( $placeholder ); ?>"
				data-allow-clear="<?php echo esc_attr( $allow_clear ? 'true' : 'false' ); ?>"
				aria-label="<?php echo esc_attr( $placeholder ); ?>"
			>
				<option value=""></option>

				<?php foreach ( $options as $form_key => $form_title ) : ?>
					<?php
					$form_url = add_query_arg(
						$this->form_query_parameter,
						$form_key,
						$base_url
					);
					?>

					<option
						value="<?php echo esc_url( $form_url ); ?>"
						<?php selected( $current_form, $form_key ); ?>
					>
						<?php
						echo esc_html(
							sprintf(
								'(%1$s) - %2$s',
								$form_key,
								$form_title
							)
						);
						?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>
		<?php

		return trim(
			ob_get_clean()
		);
	}

	/**
	 * Register and enqueue Select2 and selector assets.
	 *
	 * Assets are only enqueued when the shortcode actually renders.
	 *
	 * @return void
	 */
	private function enqueue_assets() {
		$this->register_assets();

		if ( wp_style_is( $this->select2_style_handle, 'registered' ) ) {
			wp_enqueue_style(
				$this->select2_style_handle
			);
		}

		if ( wp_style_is( $this->selector_style_handle, 'registered' ) ) {
			wp_enqueue_style(
				$this->selector_style_handle
			);
		}

		if ( wp_script_is( $this->select2_script_handle, 'registered' ) ) {
			wp_enqueue_script(
				$this->select2_script_handle
			);
		}

		if ( wp_script_is( $this->selector_script_handle, 'registered' ) ) {
			wp_enqueue_script(
				$this->selector_script_handle
			);
		}
	}

	/**
	 * Register frontend assets.
	 *
	 * @return void
	 */
	private function register_assets() {
		if ( $this->assets_registered ) {
			return;
		}

		$selector_style_path = 'assets/shortcodes/forms/css/form-selector.css';
		$selector_script_path = 'assets/shortcodes/forms/js/form-selector.js';

		if ( ! wp_style_is( $this->select2_style_handle, 'registered' ) ) {
			wp_register_style(
				$this->select2_style_handle,
				sprintf(
					'https://cdn.jsdelivr.net/npm/select2@%s/dist/css/select2.min.css',
					rawurlencode( $this->select2_version )
				),
				array(),
				$this->select2_version
			);
		}

		if (
			$this->plugin_asset_exists( $selector_style_path ) &&
			! wp_style_is( $this->selector_style_handle, 'registered' )
		) {
			wp_register_style(
				$this->selector_style_handle,
				$this->get_asset_url(
					$selector_style_path
				),
				array(
					$this->select2_style_handle,
				),
				$this->get_asset_version(
					$selector_style_path
				)
			);
		}

		if ( ! wp_script_is( $this->select2_script_handle, 'registered' ) ) {
			wp_register_script(
				$this->select2_script_handle,
				sprintf(
					'https://cdn.jsdelivr.net/npm/select2@%s/dist/js/select2.min.js',
					rawurlencode( $this->select2_version )
				),
				array(
					'jquery',
				),
				$this->select2_version,
				true
			);
		}

		if (
			$this->plugin_asset_exists( $selector_script_path ) &&
			! wp_script_is( $this->selector_script_handle, 'registered' )
		) {
			wp_register_script(
				$this->selector_script_handle,
				$this->get_asset_url(
					$selector_script_path
				),
				array(
					'jquery',
					$this->select2_script_handle,
				),
				$this->get_asset_version(
					$selector_script_path
				),
				true
			);
		}

		$this->assets_registered = true;
	}

	/**
	 * Resolve the selector base URL.
	 *
	 * A shortcode URL can be:
	 *
	 * /contact-us/
	 * https://example.com/contact-us/
	 *
	 * When omitted, the current request URL is used.
	 *
	 * The existing set_form parameter is always removed before option URLs
	 * are generated.
	 *
	 * @param mixed $custom_url Custom shortcode URL.
	 * @return string
	 */
	private function resolve_base_url( $custom_url ) {
		$custom_url = is_string( $custom_url )
			? trim( $custom_url )
			: '';

		if ( '' !== $custom_url ) {
			$base_url = $this->normalize_custom_url(
				$custom_url
			);
		} else {
			$base_url = $this->get_current_url();
		}

		if ( '' === $base_url ) {
			return '';
		}

		return remove_query_arg(
			$this->form_query_parameter,
			$base_url
		);
	}

	/**
	 * Normalize a custom internal or absolute URL.
	 *
	 * Relative URLs are resolved through home_url().
	 *
	 * External absolute URLs are allowed because the shortcode explicitly
	 * supports custom URLs, but they must pass WordPress URL validation.
	 *
	 * @param string $custom_url Custom URL.
	 * @return string
	 */
	private function normalize_custom_url( $custom_url ) {
		if ( 0 === strpos( $custom_url, '/' ) ) {
			return esc_url_raw(
				home_url( $custom_url )
			);
		}

		$validated_url = wp_http_validate_url(
			$custom_url
		);

		return false === $validated_url
			? ''
			: esc_url_raw( $validated_url );
	}

	/**
	 * Get the current frontend URL.
	 *
	 * @return string
	 */
	private function get_current_url() {
		$request_uri = isset( $_SERVER['REQUEST_URI'] )
			? wp_unslash( $_SERVER['REQUEST_URI'] )
			: '';

		if (
			! is_string( $request_uri ) ||
			'' === trim( $request_uri )
		) {
			return '';
		}

		return esc_url_raw(
			home_url( $request_uri )
		);
	}

	/**
	 * Get the requested set_form value.
	 *
	 * @return string
	 */
	private function get_requested_form_key() {
		if ( ! isset( $_GET[ $this->form_query_parameter ] ) ) {
			return '';
		}

		$form_key = sanitize_key(
			wp_unslash(
				$_GET[ $this->form_query_parameter ]
			)
		);

		return Client_Forms_Provider::instance()->has_form( $form_key )
			? $form_key
			: '';
	}

	/**
	 * Normalize a shortcode boolean.
	 *
	 * @param mixed $value   Supplied value.
	 * @param bool  $default Default value.
	 * @return bool
	 */
	private function normalize_boolean( $value, $default = false ) {
		if ( is_bool( $value ) ) {
			return $value;
		}

		if ( ! is_scalar( $value ) ) {
			return $default;
		}

		$value = strtolower(
			trim( (string) $value )
		);

		if (
			in_array(
				$value,
				array( '1', 'true', 'yes', 'on' ),
				true
			)
		) {
			return true;
		}

		if (
			in_array(
				$value,
				array( '0', 'false', 'no', 'off' ),
				true
			)
		) {
			return false;
		}

		return $default;
	}

	/**
	 * Normalize optional wrapper CSS classes.
	 *
	 * @param mixed $classes CSS classes.
	 * @return array
	 */
	private function normalize_css_classes( $classes ) {
		if ( ! is_string( $classes ) ) {
			return array();
		}

		$classes = preg_split(
			'/\s+/',
			trim( $classes )
		);

		if ( ! is_array( $classes ) ) {
			return array();
		}

		$classes = array_map(
			'sanitize_html_class',
			$classes
		);

		return array_values(
			array_filter( $classes )
		);
	}
}