<?php
/**
 * Class Client_Forms_Provider
 *
 * Provides normalized form definitions from the selected SSS client plugin.
 */

namespace DealerluxUtils\Services\Forms;

use DealerluxUtils\Registries\Options_Registry;
use DealerluxUtils\Traits\Singleton as Singleton_Trait;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Resolve, load, validate, and normalize client form definitions.
 */
class Client_Forms_Provider {

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
	 * Forms loaded during the current request.
	 *
	 * Null means the forms have not been loaded yet.
	 *
	 * @var array|null
	 */
	private $forms = null;

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
	 * Get valid forms from the selected SSS client plugin.
	 *
	 * The forms are loaded only once during the current request.
	 *
	 * @return array
	 */
	public function get_forms() {
		if ( null !== $this->forms ) {
			return $this->forms;
		}

		$this->forms = array();

		$forms_file = $this->resolve_forms_file();

		if (
			'' === $forms_file ||
			! is_file( $forms_file ) ||
			! is_readable( $forms_file )
		) {
			return $this->forms;
		}

		$forms = require $forms_file;

		if ( ! is_array( $forms ) ) {
			return $this->forms;
		}

		foreach ( $forms as $form_key => $form ) {
			if ( ! $this->is_valid_form( $form_key, $form ) ) {
				continue;
			}

			$normalized_form_key = trim(
				$form_key
			);

			$this->forms[ $normalized_form_key ] = $form;
		}

		return $this->forms;
	}

	/**
	 * Get valid form keys mapped to readable titles.
	 *
	 * Example:
	 *
	 * array(
	 *     'contactForm'   => 'Contact Form',
	 *     'serviceRequest' => 'Service Request',
	 * )
	 *
	 * @return array<string, string>
	 */
	public function get_form_options() {
		$options = array();

		foreach ( array_keys( $this->get_forms() ) as $form_key ) {
			$form_title = $this->format_form_title(
				$form_key
			);

			if ( '' === $form_title ) {
				continue;
			}

			$options[ $form_key ] = $form_title;
		}

		return $options;
	}

	/**
	 * Determine whether a form definition is valid.
	 *
	 * @param mixed $form_key Form key.
	 * @param mixed $form     Form configuration.
	 * @return bool
	 */
	public function is_valid_form( $form_key, $form ) {
		return (
			is_string( $form_key ) &&
			'' !== trim( $form_key ) &&
			is_array( $form )
		);
	}

	/**
	 * Determine whether a form exists.
	 *
	 * @param mixed $form_key Form key.
	 * @return bool
	 */
	public function has_form( $form_key ) {
		if (
			! is_string( $form_key ) ||
			'' === trim( $form_key )
		) {
			return false;
		}

		$form_key = trim(
			$form_key
		);

		return array_key_exists(
			$form_key,
			$this->get_forms()
		);
	}

	/**
	 * Get one form definition.
	 *
	 * @param mixed $form_key Form key.
	 * @param mixed $default  Default return value.
	 * @return mixed
	 */
	public function get_form( $form_key, $default = array() ) {
		if (
			! is_string( $form_key ) ||
			'' === trim( $form_key )
		) {
			return $default;
		}

		$form_key = trim(
			$form_key
		);

		$forms = $this->get_forms();

		return array_key_exists( $form_key, $forms )
			? $forms[ $form_key ]
			: $default;
	}

	/**
	 * Convert a form key into a readable title.
	 *
	 * Examples:
	 *
	 * contact          becomes Contact
	 * contactForm      becomes Contact Form
	 * service_request  becomes Service Request
	 * request-estimate becomes Request Estimate
	 *
	 * @param mixed $form_key Form key.
	 * @return string
	 */
	public function format_form_title( $form_key ) {
		if (
			! is_string( $form_key ) ||
			'' === trim( $form_key )
		) {
			return '';
		}

		$title = preg_replace(
			'/(?<=[a-z0-9])(?=[A-Z])/',
			' ',
			trim( $form_key )
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

	/**
	 * Clear the in-memory forms cache.
	 *
	 * This is useful when the selected plugin changes during the same request.
	 *
	 * @return void
	 */
	public function clear_cache() {
		$this->forms = null;
	}

	/**
	 * Resolve forms.php from the selected plugin path or directory slug.
	 *
	 * Supports plugin_directory values containing either:
	 *
	 * 1. A complete filesystem path.
	 * 2. A WordPress plugin directory slug.
	 *
	 * @return string
	 */
	private function resolve_forms_file() {
		$plugin_directory = $this->get_selected_plugin_directory();

		if ( '' === $plugin_directory ) {
			return '';
		}

		/*
		 * The registry supplied a complete filesystem path.
		 *
		 * Example:
		 *
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
		 *
		 * sss-client-plugin
		 */
		if ( ! \function_exists( 'dl_get_plugin_file_path' ) ) {
			return '';
		}

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

		if (
			! is_string( $forms_file ) ||
			'' === trim( $forms_file )
		) {
			return '';
		}

		return wp_normalize_path(
			$forms_file
		);
	}

	/**
	 * Get the selected SSS client plugin directory.
	 *
	 * The registry may contain either:
	 *
	 * 1. A complete filesystem path.
	 * 2. A plugin directory slug.
	 *
	 * @return string
	 */
	private function get_selected_plugin_directory() {
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
			return '';
		}

		$plugin_directory = wp_normalize_path(
			trim( $client_plugin_data['plugin_directory'] )
		);

		return '' === $plugin_directory
			? ''
			: $plugin_directory;
	}
}