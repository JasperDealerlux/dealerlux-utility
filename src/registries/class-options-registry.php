<?php
/**
 * Class Options_Registry
 *
 * Provides centralized access to WordPress core, theme, and plugin option
 * definitions using structured option selectors.
 *
 * @package DealerluxUtils
 */

namespace DealerluxUtils\Registries;

use DealerluxUtils\Traits\Singleton as DealerluxUtils_Singleton;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Central registry for WordPress option definitions.
 *
 * Options are selected using an array containing:
 *
 * array(
 *     'type'   => 'wp|theme|plugin',
 *     'source' => 'owner-identifier',
 *     'name'   => 'registry-option-key',
 * )
 *
 * WordPress core options do not require a source.
 */
class Options_Registry {

	/**
	 * WordPress option type.
	 *
	 * @var string
	 */
	private const TYPE_WORDPRESS = 'wp';

	/**
	 * Theme option type.
	 *
	 * @var string
	 */
	private const TYPE_THEME = 'theme';

	/**
	 * Plugin option type.
	 *
	 * @var string
	 */
	private const TYPE_PLUGIN = 'plugin';

	/**
	 * Theme registry category.
	 *
	 * @var string
	 */
	private const CATEGORY_THEME = 'theme';

	/**
	 * Plugin registry category.
	 *
	 * The external configuration uses "plugins" as its category key.
	 *
	 * @var string
	 */
	private const CATEGORY_PLUGINS = 'plugins';

	/**
	 * WordPress registry category.
	 *
	 * @var string
	 */
	private const CATEGORY_WORDPRESS = 'wp';

	/**
	 * Collection key used by theme and plugin categories.
	 *
	 * @var string
	 */
	private const COLLECTION_KEY = 'collection';

	/**
	 * Options key used by registry categories and owners.
	 *
	 * @var string
	 */
	private const OPTIONS_KEY = 'options';

	/**
	 * Database option-name field.
	 *
	 * @var string
	 */
	private const DATABASE_NAME_KEY = 'name';

	/**
	 * Default-value field.
	 *
	 * @var string
	 */
	private const DEFAULT_KEY = 'default';

	/**
	 * Use the singleton loader.
	 *
	 * This prevents the class from being instantiated more than once
	 * during a single WordPress request.
	 */
	use DealerluxUtils_Singleton;

	/**
	 * Loaded registry configuration.
	 *
	 * @var array
	 */
	private $registry = array();

	/**
	 * Constructor.
	 */
	private function __construct() {
		$this->registry = $this->load_config();
	}

	/**
	 * Determine whether this class should be registered.
	 *
	 * @return bool True when this class should be initialized.
	 */
	protected static function can_register() {
		return true;
	}

	/**
	 * Register WordPress hooks.
	 *
	 * The registry does not currently require WordPress hooks.
	 *
	 * @return void
	 */
	public function register_hooks() {}

	/**
	 * Get the complete options registry.
	 *
	 * @return array
	 */
	public function all() {
		return $this->registry;
	}

	/**
	 * Determine whether the registry configuration was loaded.
	 *
	 * @return bool
	 */
	public function is_loaded() {
		return ! empty( $this->registry );
	}

	/**
	 * Get a top-level registry category.
	 *
	 * Valid categories:
	 *
	 * - wp
	 * - theme
	 * - plugins
	 *
	 * @param string $category Category identifier.
	 * @return array
	 */
	public function get_category( $category ) {
		if (
			! is_string( $category ) ||
			'' === trim( $category )
		) {
			return array();
		}

		$category = trim( $category );

		return isset( $this->registry[ $category ] )
			&& is_array( $this->registry[ $category ] )
				? $this->registry[ $category ]
				: array();
	}

	/**
	 * Get all WordPress core option definitions.
	 *
	 * @return array
	 */
	public function get_wordpress_options() {
		$category = $this->get_category(
			self::CATEGORY_WORDPRESS
		);

		return $this->extract_options( $category );
	}

	/**
	 * Get all registered theme configurations.
	 *
	 * @return array
	 */
	public function get_themes() {
		return $this->get_collection(
			self::CATEGORY_THEME
		);
	}

	/**
	 * Get one registered theme configuration.
	 *
	 * @param string $theme Theme identifier.
	 * @return array
	 */
	public function get_theme( $theme ) {
		return $this->get_collection_item(
			self::CATEGORY_THEME,
			$theme
		);
	}

	/**
	 * Get all options registered for a theme.
	 *
	 * @param string $theme Theme identifier.
	 * @return array
	 */
	public function get_theme_options( $theme ) {
		return $this->extract_options(
			$this->get_theme( $theme )
		);
	}

	/**
	 * Get all registered plugin configurations.
	 *
	 * @return array
	 */
	public function get_plugins() {
		return $this->get_collection(
			self::CATEGORY_PLUGINS
		);
	}

	/**
	 * Get one registered plugin configuration.
	 *
	 * @param string $plugin Plugin identifier.
	 * @return array
	 */
	public function get_plugin( $plugin ) {
		return $this->get_collection_item(
			self::CATEGORY_PLUGINS,
			$plugin
		);
	}

	/**
	 * Get all options registered for a plugin.
	 *
	 * @param string $plugin Plugin identifier.
	 * @return array
	 */
	public function get_plugin_options( $plugin ) {
		return $this->extract_options(
			$this->get_plugin( $plugin )
		);
	}

	/**
	 * Get an option definition using a structured selector.
	 *
	 * WordPress example:
	 *
	 * array(
	 *     'type' => 'wp',
	 *     'name' => 'blogname',
	 * )
	 *
	 * Theme example:
	 *
	 * array(
	 *     'type'   => 'theme',
	 *     'source' => 'dealerlux',
	 *     'name'   => 'settings',
	 * )
	 *
	 * Plugin example:
	 *
	 * array(
	 *     'type'   => 'plugin',
	 *     'source' => 'spa-software-solutions',
	 *     'name'   => 'selected_client_plugin',
	 * )
	 *
	 * @param array $selector Option selector.
	 * @return array
	 */
	public function get_option( array $selector ) {
		$selector = $this->normalize_selector( $selector );

		if ( empty( $selector ) ) {
			return array();
		}

		$type   = $selector['type'];
		$source = $selector['source'];
		$name   = $selector['name'];

		if ( self::TYPE_WORDPRESS === $type ) {
			$options = $this->get_wordpress_options();

			if (
				! isset( $options[ $name ] ) ||
				! is_array( $options[ $name ] )
			) {
				return array();
			}

			return $this->add_option_context(
				$options[ $name ],
				$type,
				'wp',
				$name
			);
		}

		if ( self::TYPE_THEME === $type ) {
			$options = $this->get_theme_options( $source );

			if (
				! isset( $options[ $name ] ) ||
				! is_array( $options[ $name ] )
			) {
				return array();
			}

			return $this->add_option_context(
				$options[ $name ],
				$type,
				$source,
				$name
			);
		}

		if ( self::TYPE_PLUGIN === $type ) {
			$options = $this->get_plugin_options( $source );

			if (
				! isset( $options[ $name ] ) ||
				! is_array( $options[ $name ] )
			) {
				return array();
			}

			return $this->add_option_context(
				$options[ $name ],
				$type,
				$source,
				$name
			);
		}

		return array();
	}

	/**
	 * Determine whether an option is registered.
	 *
	 * This checks the registry, not the WordPress options table.
	 *
	 * @param array $selector Option selector.
	 * @return bool
	 */
	public function has_option( array $selector ) {
		return ! empty(
			$this->get_option( $selector )
		);
	}

	/**
	 * Get the actual WordPress database option name.
	 *
	 * For theme and plugin options, the database name comes from the
	 * definition's "name" field.
	 *
	 * For WordPress core options, the registry array key is used when
	 * no explicit "name" field is configured.
	 *
	 * @param array  $selector Option selector.
	 * @param string $fallback Fallback option name.
	 * @return string
	 */
	public function get_option_name(
		array $selector,
		$fallback = ''
	) {
		$option = $this->get_option( $selector );

		if ( empty( $option ) ) {
			return $fallback;
		}

		if (
			isset( $option[ self::DATABASE_NAME_KEY ] ) &&
			is_string(
				$option[ self::DATABASE_NAME_KEY ]
			) &&
			'' !== trim(
				$option[ self::DATABASE_NAME_KEY ]
			)
		) {
			return trim(
				$option[ self::DATABASE_NAME_KEY ]
			);
		}

		if (
			isset( $option['registry_name'] ) &&
			is_string( $option['registry_name'] ) &&
			'' !== trim( $option['registry_name'] )
		) {
			return trim( $option['registry_name'] );
		}

		return $fallback;
	}

	/**
	 * Get an option's registered default value.
	 *
	 * @param array $selector Option selector.
	 * @param mixed $fallback Fallback value.
	 * @return mixed
	 */
	public function get_default(
		array $selector,
		$fallback = null
	) {
		$option = $this->get_option( $selector );

		return array_key_exists(
			self::DEFAULT_KEY,
			$option
		)
			? $option[ self::DEFAULT_KEY ]
			: $fallback;
	}

	/**
	 * Get an option's registered data type.
	 *
	 * @param array  $selector Option selector.
	 * @param string $fallback Fallback type.
	 * @return string
	 */
	public function get_type(
		array $selector,
		$fallback = ''
	) {
		$option = $this->get_option( $selector );

		return isset( $option['type'] )
			&& is_string( $option['type'] )
				? $option['type']
				: $fallback;
	}

	/**
	 * Get an option's registered label.
	 *
	 * @param array  $selector Option selector.
	 * @param string $fallback Fallback label.
	 * @return string
	 */
	public function get_label(
		array $selector,
		$fallback = ''
	) {
		$option = $this->get_option( $selector );

		return isset( $option['label'] )
			&& is_string( $option['label'] )
				? $option['label']
				: $fallback;
	}

	/**
	 * Get an option's registered description.
	 *
	 * @param array  $selector Option selector.
	 * @param string $fallback Fallback description.
	 * @return string
	 */
	public function get_description(
		array $selector,
		$fallback = ''
	) {
		$option = $this->get_option( $selector );

		return isset( $option['description'] )
			&& is_string( $option['description'] )
				? $option['description']
				: $fallback;
	}

	/**
	 * Read a registered option value from WordPress.
	 *
	 * @param array $selector Option selector.
	 * @param mixed $fallback Fallback for an invalid or missing option.
	 * @return mixed
	 */
	public function get_value(
		array $selector,
		$fallback = null
	) {
		$option = $this->get_option( $selector );

		if ( empty( $option ) ) {
			return $fallback;
		}

		$option_name = $this->get_option_name(
			$selector
		);

		if ( '' === $option_name ) {
			return $fallback;
		}

		$default = array_key_exists(
			self::DEFAULT_KEY,
			$option
		)
			? $option[ self::DEFAULT_KEY ]
			: $fallback;

		return \get_option(
			$option_name,
			$default
		);
	}

	/**
	 * Add a registered option to WordPress.
	 *
	 * When no value is supplied, the registered default is used.
	 *
	 * @param array       $selector Option selector.
	 * @param mixed|null  $value    Value to store.
	 * @param bool|string $autoload Whether the option should autoload.
	 * @return bool
	 */
	public function add_value(
		array $selector,
		$value = null,
		$autoload = true
	) {
		$option_name = $this->get_option_name(
			$selector
		);

		if ( '' === $option_name ) {
			return false;
		}

		if ( null === $value ) {
			$value = $this->get_default(
				$selector
			);
		}

		return \add_option(
			$option_name,
			$value,
			'',
			$autoload
		);
	}

	/**
	 * Update a registered WordPress option.
	 *
	 * @param array       $selector Option selector.
	 * @param mixed       $value    Value to store.
	 * @param bool|string $autoload Whether the option should autoload.
	 * @return bool
	 */
	public function update_value(
		array $selector,
		$value,
		$autoload = true
	) {
		$option_name = $this->get_option_name(
			$selector
		);

		if ( '' === $option_name ) {
			return false;
		}

		return \update_option(
			$option_name,
			$value,
			$autoload
		);
	}

	/**
	 * Delete a registered WordPress option.
	 *
	 * @param array $selector Option selector.
	 * @return bool
	 */
	public function delete_value( array $selector ) {
		$option_name = $this->get_option_name(
			$selector
		);

		if ( '' === $option_name ) {
			return false;
		}

		return \delete_option( $option_name );
	}

	/**
	 * Determine whether the option exists in the WordPress database.
	 *
	 * This differs from has_option(), which only checks whether the
	 * selector exists in the registry configuration.
	 *
	 * @param array $selector Option selector.
	 * @return bool
	 */
	public function value_exists( array $selector ) {
		$option_name = $this->get_option_name(
			$selector
		);

		if ( '' === $option_name ) {
			return false;
		}

		$missing_value = new \stdClass();

		return $missing_value !== \get_option(
			$option_name,
			$missing_value
		);
	}

	/**
	 * Get all registered options as a flat array.
	 *
	 * Flat-array keys use the following formats:
	 *
	 * wp.option
	 * theme.source.option
	 * plugin.source.option
	 *
	 * @return array
	 */
	public function get_flat_options() {
		$options = array();

		foreach (
			$this->get_wordpress_options()
			as $option_name => $option
		) {
			if (
				! is_string( $option_name ) ||
				! is_array( $option )
			) {
				continue;
			}

			$key = sprintf(
				'wp.%s',
				$option_name
			);

			$options[ $key ] = $this->add_option_context(
				$option,
				self::TYPE_WORDPRESS,
				'wp',
				$option_name
			);
		}

		foreach ( $this->get_themes() as $theme_name => $theme ) {
			if (
				! is_string( $theme_name ) ||
				! is_array( $theme )
			) {
				continue;
			}

			foreach (
				$this->extract_options( $theme )
				as $option_name => $option
			) {
				if (
					! is_string( $option_name ) ||
					! is_array( $option )
				) {
					continue;
				}

				$key = sprintf(
					'theme.%1$s.%2$s',
					$theme_name,
					$option_name
				);

				$options[ $key ] =
					$this->add_option_context(
						$option,
						self::TYPE_THEME,
						$theme_name,
						$option_name
					);
			}
		}

		foreach ( $this->get_plugins() as $plugin_name => $plugin ) {
			if (
				! is_string( $plugin_name ) ||
				! is_array( $plugin )
			) {
				continue;
			}

			foreach (
				$this->extract_options( $plugin )
				as $option_name => $option
			) {
				if (
					! is_string( $option_name ) ||
					! is_array( $option )
				) {
					continue;
				}

				$key = sprintf(
					'plugin.%1$s.%2$s',
					$plugin_name,
					$option_name
				);

				$options[ $key ] =
					$this->add_option_context(
						$option,
						self::TYPE_PLUGIN,
						$plugin_name,
						$option_name
					);
			}
		}

		return $options;
	}

	/**
	 * Load the external options configuration.
	 *
	 * Expected location:
	 *
	 * src/config/options.php
	 *
	 * @return array
	 */
	private function load_config() {
		$config_file = dirname( __DIR__, 2 ) . '/config/options.php';

		if (
			! is_file( $config_file ) ||
			! is_readable( $config_file )
		) {
			return array();
		}

		$config = require $config_file;

		return is_array( $config )
			? $config
			: array();
	}

	/**
	 * Normalize and validate an option selector.
	 *
	 * The selector's "name" property represents the registry array key,
	 * not necessarily the actual WordPress database option name.
	 *
	 * @param array $selector Raw option selector.
	 * @return array
	 */
	private function normalize_selector( array $selector ) {
		$type = isset( $selector['type'] )
			&& is_string( $selector['type'] )
				? strtolower( trim( $selector['type'] ) )
				: '';

		$source = isset( $selector['source'] )
			&& is_string( $selector['source'] )
				? trim( $selector['source'] )
				: '';

		$name = isset( $selector['name'] )
			&& is_string( $selector['name'] )
				? trim( $selector['name'] )
				: '';

		if ( '' === $name ) {
			return array();
		}

		if ( self::TYPE_WORDPRESS === $type ) {
			return array(
				'type'   => self::TYPE_WORDPRESS,
				'source' => 'wp',
				'name'   => $name,
			);
		}

		if (
			! in_array(
				$type,
				array(
					self::TYPE_THEME,
					self::TYPE_PLUGIN,
				),
				true
			) ||
			'' === $source
		) {
			return array();
		}

		return array(
			'type'   => $type,
			'source' => $source,
			'name'   => $name,
		);
	}

	/**
	 * Get a collection from a registry category.
	 *
	 * @param string $category Category identifier.
	 * @return array
	 */
	private function get_collection( $category ) {
		$category_config = $this->get_category(
			$category
		);

		return isset(
			$category_config[ self::COLLECTION_KEY ]
		)
		&& is_array(
			$category_config[ self::COLLECTION_KEY ]
		)
			? $category_config[ self::COLLECTION_KEY ]
			: array();
	}

	/**
	 * Get one item from a category collection.
	 *
	 * @param string $category Category identifier.
	 * @param string $item_key Collection item identifier.
	 * @return array
	 */
	private function get_collection_item(
		$category,
		$item_key
	) {
		if (
			! is_string( $item_key ) ||
			'' === trim( $item_key )
		) {
			return array();
		}

		$item_key   = trim( $item_key );
		$collection = $this->get_collection( $category );

		return isset( $collection[ $item_key ] )
			&& is_array( $collection[ $item_key ] )
				? $collection[ $item_key ]
				: array();
	}

	/**
	 * Extract option definitions from a registry configuration.
	 *
	 * @param array $config Registry configuration.
	 * @return array
	 */
	private function extract_options( array $config ) {
		return isset( $config[ self::OPTIONS_KEY ] )
			&& is_array( $config[ self::OPTIONS_KEY ] )
				? $config[ self::OPTIONS_KEY ]
				: array();
	}

	/**
	 * Add registry context to an option definition.
	 *
	 * @param array  $option Option definition.
	 * @param string $type   Option type.
	 * @param string $source Option owner.
	 * @param string $name   Registry option key.
	 * @return array
	 */
	private function add_option_context(
		array $option,
		$type,
		$source,
		$name
	) {
		$option['registry_type']   = $type;
		$option['registry_source'] = $source;
		$option['registry_name']   = $name;

		return $option;
	}
}