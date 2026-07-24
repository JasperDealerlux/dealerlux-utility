<?php
/**
 * Class Dump_Client_Forms_Shortcode
 *
 * Generates CTA or accordion Gutenberg blocks from the selected
 * SSS client plugin forms.php.
 */

namespace DealerluxUtils\Shortcodes;

use DealerluxUtils\Registries\Options_Registry;
use DealerluxUtils\Traits\Singleton as DealerluxUtils_Singleton;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Generate Gutenberg blocks for all forms available in the currently
 * selected SSS client plugin.
 *
 * Supported shortcode styles:
 *
 * [dl_dump_forms]
 * [dl_dump_forms style="cta"]
 * [dl_dump_forms style="accordion"]
 */
class Dump_Client_Forms_Shortcode {

	/**
	 * The shortcode tag handled by this class.
	 *
	 * @var string
	 */
	private $shortcode_tag = 'dl_dump_forms';

	/**
	 * Default shortcode display style.
	 *
	 * @var string
	 */
	private $default_style = 'cta';

	/**
	 * Supported shortcode display styles.
	 *
	 * @var array
	 */
	private $supported_styles = array(
		'cta',
		'accordion',
	);

	/**
	 * Use the singleton loader.
	 */
	use DealerluxUtils_Singleton;

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
	 * Render the forms shortcode.
	 *
	 * Usage:
	 *
	 * [dl_dump_forms]
	 * [dl_dump_forms style="cta"]
	 * [dl_dump_forms style="accordion"]
	 *
	 * @param array|string $attributes Shortcode attributes.
	 * @return string
	 */
	public function render_shortcode( $attributes = array() ) {
		$attributes = shortcode_atts(
			array(
				'style' => $this->default_style,
			),
			is_array( $attributes )
				? $attributes
				: array(),
			$this->shortcode_tag
		);

		$style = $this->normalize_style(
			$attributes['style']
		);

		$forms = $this->load_forms();

		if ( empty( $forms ) ) {
			return '';
		}

		$block_markup = $this->build_blocks(
			$forms,
			$style
		);

		if ( '' === $block_markup ) {
			return '';
		}

		return do_blocks( $block_markup );
	}

	/**
	 * Normalize the requested shortcode style.
	 *
	 * Falls back to CTA when the supplied style is unsupported.
	 *
	 * @param mixed $style Requested style.
	 * @return string
	 */
	private function normalize_style( $style ) {
		if ( ! is_string( $style ) ) {
			return $this->default_style;
		}

		$style = strtolower(
			trim( $style )
		);

		if (
			'' === $style ||
			! in_array( $style, $this->supported_styles, true )
		) {
			return $this->default_style;
		}

		return $style;
	}

	/**
	 * Load forms.php from the selected SSS client plugin.
	 *
	 * @return array
	 */
	private function load_forms() {
		if ( ! \function_exists( 'dl_get_plugin_file_path' ) ) {
			return array();
		}

		$sss_client_plugin_data = Options_Registry::instance()
			->get_value(
				array(
					'type'   => 'plugin',
					'source' => 'spa-software-solutions',
					'name'   => 'selected_client_plugin',
				),
				array()
			);

		if (
			! is_array( $sss_client_plugin_data ) ||
			empty( $sss_client_plugin_data['plugin_directory'] ) ||
			! is_string(
				$sss_client_plugin_data['plugin_directory']
			)
		) {
			return array();
		}

		$plugin_directory = trim(
			$sss_client_plugin_data['plugin_directory']
		);

		if ( '' === $plugin_directory ) {
			return array();
		}

		$forms_file = \dl_get_plugin_file_path(
			$plugin_directory,
			'forms/forms.php'
		);

		if (
			! is_string( $forms_file ) ||
			'' === trim( $forms_file ) ||
			! is_file( $forms_file ) ||
			! is_readable( $forms_file )
		) {
			return array();
		}

		$forms = require $forms_file;

		return is_array( $forms )
			? $forms
			: array();
	}

	/**
	 * Build Gutenberg blocks using the requested display style.
	 *
	 * @param array  $forms Forms configuration.
	 * @param string $style Display style.
	 * @return string
	 */
	private function build_blocks( array $forms, $style ) {
		switch ( $style ) {
			case 'accordion':
				return $this->build_accordion_blocks( $forms );

			case 'cta':
			default:
				return $this->build_cta_blocks( $forms );
		}
	}

	/**
	 * Build one Gutenberg CTA container containing all available forms.
	 *
	 * @param array $forms Forms configuration.
	 * @return string
	 */
	private function build_cta_blocks( array $forms ) {
		$icon_blocks = array();

		foreach ( $forms as $form_key => $form ) {
			if ( ! $this->is_valid_form( $form_key, $form ) ) {
				continue;
			}

			$icon_block = $this->build_form_icon_block(
				$form_key
			);

			if ( '' !== $icon_block ) {
				$icon_blocks[] = $icon_block;
			}
		}

		if ( empty( $icon_blocks ) ) {
			return '';
		}

		$container_attributes = array(
			'metadata' => array(
				'name' => 'Form CTA : GENERIC',
			),
		);

		$container_json = $this->encode_block_attributes(
			$container_attributes
		);

		if ( '' === $container_json ) {
			return '';
		}

		return sprintf(
			"<!-- wp:spa-software-solutions/cta-container %1\$s -->\n"
			. "%2\$s\n"
			. '<!-- /wp:spa-software-solutions/cta-container -->',
			$container_json,
			implode( "\n\n", $icon_blocks )
		);
	}

	/**
	 * Build one Gutenberg accordion containing all available forms.
	 *
	 * Each form is rendered as:
	 *
	 * accordion
	 * └── accordion-item
	 *     ├── accordion-heading
	 *     └── accordion-panel
	 *         └── spa-software-solutions/lead-form
	 *
	 * @param array $forms Forms configuration.
	 * @return string
	 */
	private function build_accordion_blocks( array $forms ) {
		$accordion_items = array();

		foreach ( $forms as $form_key => $form ) {
			if ( ! $this->is_valid_form( $form_key, $form ) ) {
				continue;
			}

			$accordion_item = $this->build_form_accordion_item(
				$form_key
			);

			if ( '' !== $accordion_item ) {
				$accordion_items[] = $accordion_item;
			}
		}

		if ( empty( $accordion_items ) ) {
			return '';
		}

		return sprintf(
			"<!-- wp:accordion -->\n"
			. "<div role=\"group\" class=\"wp-block-accordion\">\n"
			. "%s\n"
			. "</div>\n"
			. '<!-- /wp:accordion -->',
			implode( "\n\n", $accordion_items )
		);
	}

	/**
	 * Build one Gutenberg CTA icon block.
	 *
	 * @param string $form_key Form array key.
	 * @return string
	 */
	private function build_form_icon_block( $form_key ) {
		$form_title = $this->format_form_title(
			$form_key
		);

		if ( '' === $form_title ) {
			return '';
		}

		$icon_attributes = array(
			'title'    => $form_title,
			'form'     => $form_key,
			'icon'     => 'fa-solid fa-file-lines',
			'metadata' => array(
				'name' => 'CTA : ' . $form_key,
			),
		);

		$icon_json = $this->encode_block_attributes(
			$icon_attributes
		);

		if ( '' === $icon_json ) {
			return '';
		}

		return sprintf(
			'<!-- wp:spa-software-solutions/cta-icon %s /-->',
			$icon_json
		);
	}

	/**
	 * Build one accordion item containing a lead form block.
	 *
	 * @param string $form_key Form array key.
	 * @return string
	 */
	private function build_form_accordion_item( $form_key ) {
		$form_title = $this->format_form_title(
			$form_key
		);

		if ( '' === $form_title ) {
			return '';
		}

		$lead_form_attributes = array(
			'form_type' => $form_key,
		);

		$lead_form_json = $this->encode_block_attributes(
			$lead_form_attributes
		);

		if ( '' === $lead_form_json ) {
			return '';
		}

		$escaped_title = esc_html(
			$form_title
		);

		return sprintf(
			"<!-- wp:accordion-item -->\n"
			. "<div class=\"wp-block-accordion-item\">\n"
			. "<!-- wp:accordion-heading -->\n"
			. "<h3 class=\"wp-block-accordion-heading\">"
			. "<button type=\"button\" class=\"wp-block-accordion-heading__toggle\">"
			. "<span class=\"wp-block-accordion-heading__toggle-title\">%1\$s</span>"
			. "<span class=\"wp-block-accordion-heading__toggle-icon\" aria-hidden=\"true\">+</span>"
			. "</button>"
			. "</h3>\n"
			. "<!-- /wp:accordion-heading -->\n\n"
			. "<!-- wp:accordion-panel -->\n"
			. "<div role=\"region\" class=\"wp-block-accordion-panel\">\n"
			. "<!-- wp:spa-software-solutions/lead-form %2\$s /-->\n"
			. "</div>\n"
			. "<!-- /wp:accordion-panel -->\n"
			. "</div>\n"
			. '<!-- /wp:accordion-item -->',
			$escaped_title,
			$lead_form_json
		);
	}

	/**
	 * Determine whether a forms.php entry is valid.
	 *
	 * @param mixed $form_key Form array key.
	 * @param mixed $form     Form configuration.
	 * @return bool
	 */
	private function is_valid_form( $form_key, $form ) {
		return (
			is_string( $form_key ) &&
			'' !== trim( $form_key ) &&
			is_array( $form )
		);
	}

	/**
	 * Encode Gutenberg block attributes.
	 *
	 * @param array $attributes Block attributes.
	 * @return string
	 */
	private function encode_block_attributes( array $attributes ) {
		$json = wp_json_encode(
			$attributes,
			JSON_UNESCAPED_SLASHES |
			JSON_UNESCAPED_UNICODE
		);

		return false === $json
			? ''
			: $json;
	}

	/**
	 * Convert camelCase, PascalCase, snake_case, or kebab-case
	 * into capitalized words.
	 *
	 * Examples:
	 *
	 * siteInspection   becomes Site Inspection
	 * requestPhoneCall becomes Request Phone Call
	 * pool_opening     becomes Pool Opening
	 *
	 * @param string $form_key Form array key.
	 * @return string
	 */
	private function format_form_title( $form_key ) {
		if (
			! is_string( $form_key ) ||
			'' === trim( $form_key )
		) {
			return '';
		}

		$title = preg_replace(
			'/(?<=[a-z0-9])(?=[A-Z])/',
			' ',
			$form_key
		);

		if ( ! is_string( $title ) ) {
			return '';
		}

		$title = str_replace(
			array( '_', '-' ),
			' ',
			$title
		);

		$title = preg_replace(
			'/\s+/',
			' ',
			$title
		);

		if ( ! is_string( $title ) ) {
			return '';
		}

		return ucwords(
			strtolower(
				trim( $title )
			)
		);
	}
}