<?php
/**
 * Plugin Name: Dealerlux Utility Functions
 * Description: Reusable debugging, request, array, hook, enqueue, and plugin utility functions for Dealerlux.
 * Version: 0.0.1
 * Author: Jasper Benedicto Jardin
 * Requires PHP: 7.4
 */

namespace DealerluxUtils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Bootstraps the Dealerlux Utility MU plugin.
 */
final class Bootstrap {

	/**
	 * Namespace handled by this autoloader.
	 */
	private const NAMESPACE_PREFIX = __NAMESPACE__ . '\\';

	/**
	 * Singleton instance.
	 *
	 * @var self|null
	 */
	private static $instance = null;

	/**
	 * Plugin root directory.
	 *
	 * @var string
	 */
	private $plugin_directory;

	/**
	 * Returns the bootstrap instance.
	 *
	 * @return self
	 */
	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Initializes the MU plugin.
	 */
	private function __construct() {
		$this->plugin_directory = trailingslashit( __DIR__ );

		$this->register_autoloader();
		$this->load_utility_functions();
		$this->initialize_classes();
	}

	/**
	 * Prevent cloning.
	 *
	 * @return void
	 */
	private function __clone() {}

	/**
	 * Registers the class autoloader.
	 *
	 * @return void
	 */
	private function register_autoloader(): void {
		spl_autoload_register(
			array( $this, 'autoload' )
		);
	}

	/**
	 * Autoloads Dealerlux Utility classes and traits.
	 *
	 * Examples:
	 *
	 * DealerluxUtils\Initializer
	 * => src/class-initializer.php
	 *
	 * DealerluxUtils\Shortcodes\Forms_CTA
	 * => src/shortcodes/class-forms-cta.php
	 *
	 * DealerluxUtils\Traits\Singleton
	 * => src/traits/trait-singleton.php
	 *
	 * @param string $class_name Fully qualified class or trait name.
	 * @return void
	 */
	public function autoload( string $class_name ): void {
		if (
			0 !== strpos(
				$class_name,
				self::NAMESPACE_PREFIX
			)
		) {
			return;
		}

		$relative_class = substr(
			$class_name,
			strlen( self::NAMESPACE_PREFIX )
		);

		if ( '' === $relative_class ) {
			return;
		}

		$namespace_parts = explode(
			'\\',
			$relative_class
		);

		$class_name = array_pop( $namespace_parts );

		$is_trait = isset( $namespace_parts[0] )
			&& 'Traits' === $namespace_parts[0];

		$directories = array_map(
			'strtolower',
			$namespace_parts
		);

		$file_name = strtolower(
			str_replace( '_', '-', $class_name )
		);

		$file_prefix = $is_trait
			? 'trait-'
			: 'class-';

		$file_path = $this->plugin_directory . 'src/';

		if ( ! empty( $directories ) ) {
			$file_path .= implode( '/', $directories ) . '/';
		}

		$file_path .= $file_prefix . $file_name . '.php';

		$file_path = wp_normalize_path( $file_path );
		

		if ( is_file( $file_path ) ) {
			require_once $file_path;

			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				if ( ! class_exists( $class_name, false ) && ! trait_exists( $class_name, false ) ) {
					error_log(
						sprintf(
							'Dealerlux autoload: File loaded, but "%s" was not declared.',
							$class_name
						)
					);
				}
			}

			return;
		}

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log(
				sprintf(
					'Dealerlux Utility autoload failure: Class "%1$s" expected at "%2$s".',
					$relative_class,
					$file_path
				)
			);
		}
	}

	/**
	 * Loads procedural utility functions.
	 *
	 * @return void
	 */
	private function load_utility_functions(): void {
		$utility_functions_file = $this->plugin_directory
			. 'utils/functions.php';

		if ( ! is_file( $utility_functions_file ) ) {
			error_log(
				'Dealerlux Utility: Missing utility functions file: '
				. $utility_functions_file
			);

			return;
		}

		require_once $utility_functions_file;
	}

	/**
	 * Initializes Dealerlux Utility classes.
	 *
	 * @return void
	 */
	public function initialize_classes(): void {
		Initializer::register();
	}
}

Bootstrap::instance();