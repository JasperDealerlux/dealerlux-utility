<?php
/**
 * Class Client_Form_Selector_Shortcode
 *
 * Generates a Select2 form selector using forms available in the currently
 * selected SSS client plugin.
 */

namespace DealerluxUtils\Shortcodes\Forms;

use DealerluxUtils\Registries\Options_Registry;
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
	 * Options Registry selector.
	 *
	 * Resolves:
	 *
	 * plugins.collection.dealerlux-utility.options
	 *     .client_switcher_selected_plugin
	 *
	 * @var array
	 */
	private $option_selector = array(
		'type'   => 'plugin',
		'source' => 'dealerlux-utility',
		'name'   => 'client_switcher_selected_plugin',
	);

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

		$forms = $this->load_forms();

		if ( empty( $forms ) ) {
			return '';
		}


		$options = $this->normalize_forms( $forms );

		if ( empty( $options ) ) {
			return '';
		}

		$base_url = $this->resolve_base_url( $attributes['url'] );

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
						'set_form',
						$form_key,
						$base_url
					);
					?>
					<option
						value="<?php echo esc_url( $form_url ); ?>"
						<?php selected( $current_form, $form_key ); ?>
					>
						<?php echo esc_html( $form_title ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>
		<?php

		return trim( ob_get_clean() );
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

		wp_enqueue_style(
			$this->select2_style_handle
		);

		wp_enqueue_style(
			$this->selector_style_handle
		);

		wp_enqueue_script(
			$this->select2_script_handle
		);

		wp_enqueue_script(
			$this->selector_script_handle
		);
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

		wp_register_style(
			$this->select2_style_handle,
			sprintf(
				'https://cdn.jsdelivr.net/npm/select2@%s/dist/css/select2.min.css',
				rawurlencode( $this->select2_version )
			),
			array(),
			$this->select2_version
		);

		wp_register_style(
			$this->selector_style_handle,
			$this->get_asset_url(
				'assets/shortcodes/forms/css/form-selector.css'
			),
			array(
				$this->select2_style_handle,
			),
			$this->get_asset_version(
				'assets/shortcodes/forms/css/form-selector.css'
			)
		);

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

		wp_register_script(
			$this->selector_script_handle,
			$this->get_asset_url(
				'assets/shortcodes/forms/js/form-selector.js'
			),
			array(
				'jquery',
				$this->select2_script_handle,
			),
			$this->get_asset_version(
				'assets/shortcodes/forms/js/form-selector.js'
			),
			true
		);

		$this->assets_registered = true;
	}

    /**
     * Load forms.php from the selected SSS client plugin.
     *
     * Supports plugin_directory values containing either:
     *
     * 1. A complete filesystem path.
     * 2. A WordPress plugin directory slug.
     *
     * @return array
     */
    private function load_forms() {
        $client_plugin_data = Options_Registry::instance()
            ->get_value(
                $this->option_selector,
                array()
            );

        if (
            ! is_array( $client_plugin_data ) ||
            empty( $client_plugin_data['plugin_directory'] ) ||
            ! is_string( $client_plugin_data['plugin_directory'] )
        ) {
            return array();
        }

        $plugin_directory = wp_normalize_path(
            trim( $client_plugin_data['plugin_directory'] )
        );

        if ( '' === $plugin_directory ) {
            return array();
        }

        $forms_file = $this->resolve_forms_file(
            $plugin_directory
        );

        if (
            '' === $forms_file ||
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
     * Resolve the forms.php path from a plugin path or directory slug.
     *
     * @param string $plugin_directory Plugin directory path or slug.
     * @return string
     */
    private function resolve_forms_file( $plugin_directory ) {
        $plugin_directory = wp_normalize_path(
            trim( $plugin_directory )
        );

        if ( '' === $plugin_directory ) {
            return '';
        }

        /*
        * The registry already supplied a complete filesystem path.
        *
        * Example:
        * /var/www/html/wp-content/plugins/sss-client-plugin
        */
        if ( is_dir( $plugin_directory ) ) {
            return wp_normalize_path(
                trailingslashit( $plugin_directory )
                . 'forms/forms.php'
            );
        }

        /*
        * The registry supplied only a plugin directory slug.
        *
        * Example:
        * sss-client-plugin
        */
        if ( \function_exists( 'dl_get_plugin_file_path' ) ) {
            $plugin_slug = sanitize_file_name(
                basename( $plugin_directory )
            );

            if ( '' === $plugin_slug ) {
                return '';
            }

            $forms_file = \dl_get_plugin_file_path(
                $plugin_slug,
                'forms/forms.php'
            );

            return is_string( $forms_file )
                ? wp_normalize_path( $forms_file )
                : '';
        }

        return '';
    }

	/**
	 * Normalize forms.php entries into selector options.
	 *
	 * @param array $forms Forms configuration.
	 * @return array<string, string>
	 */
	private function normalize_forms( array $forms ) {
		$options = array();

		foreach ( $forms as $form_key => $form ) {
			if ( ! $this->is_valid_form( $form_key, $form ) ) {
				continue;
			}

            $normalized_key = $form_key;

			if ( '' === $normalized_key ) {
				continue;
			}

			$options[ $normalized_key ] = $this->format_form_title(
				$form_key
			);
		}

		return $options;
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
			'set_form',
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

		if ( ! is_string( $request_uri ) || '' === trim( $request_uri ) ) {
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
		if ( ! isset( $_GET['set_form'] ) ) {
			return '';
		}

		return sanitize_key(
			wp_unslash( $_GET['set_form'] )
		);
	}

	/**
	 * Convert form keys into readable labels.
	 *
	 * Examples:
	 *
	 * contact          becomes Contact
	 * contactForm      becomes Contact Form
	 * service_request  becomes Service Request
	 * request-estimate becomes Request Estimate
	 *
	 * @param string $form_key Form key.
	 * @return string
	 */
	private function format_form_title( $form_key ) {
		$title = preg_replace(
			'/(?<=[a-z0-9])(?=[A-Z])/',
			' ',
			$form_key
		);

		$title = preg_replace(
			'/[_\-]+/',
			' ',
			$title
		);

		$title = preg_replace(
			'/\s+/',
			' ',
			$title
		);

		return ucwords(
			trim( $title )
		);
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

		if ( in_array( $value, array( '1', 'true', 'yes', 'on' ), true ) ) {
			return true;
		}

		if ( in_array( $value, array( '0', 'false', 'no', 'off' ), true ) ) {
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

    /**
     * Get the DealerLux Utility plugin root path.
     *
     * The current class resides in:
     *
     * src/shortcodes/forms/
     *
     * @return string
     */
    private function get_plugin_root_path() {
        return dirname(
            __DIR__,
            3
        );
    }

    /**
     * Get the main plugin file path.
     *
     * @return string
     */
    private function get_plugin_file_path() {
        return $this->get_plugin_root_path()
            . '/dealerlux-utility.php';
    }

    /**
     * Get a plugin asset URL.
     *
     * @param string $relative_path Relative asset path.
     * @return string
     */
    private function get_asset_url( $relative_path ) {
        $relative_path = ltrim(
            $relative_path,
            '/'
        );

        return plugins_url(
            $relative_path,
            $this->get_plugin_file_path()
        );
    }

	/**
	 * Get a cache-safe asset version.
	 *
	 * @param string $relative_path Relative asset path.
	 * @return string
	 */
	private function get_asset_version( $relative_path ) {
        $relative_path = ltrim(
            $relative_path,
            '/'
        );

        $absolute_path = $this->get_plugin_root_path()
            . '/'
            . $relative_path;

        if ( is_file( $absolute_path ) ) {
            return (string) filemtime(
                $absolute_path
            );
        }

        return '1.0.0';
	}
}