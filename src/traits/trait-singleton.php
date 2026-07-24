<?php
/**
 * Singleton trait.
 *
 * Provides reusable singleton behavior for plugin classes that should only be
 * initialized once during a single WordPress request.
 *
 * Classes using this trait must implement the can_register() method to
 * determine whether their WordPress hooks should be registered.
 */

namespace DealerluxUtils\Traits;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Singleton trait for plugin classes.
 */
trait Singleton {

	/**
	 * Class instance.
	 *
	 * Each class using this trait receives its own singleton instance.
	 *
	 * @var static|null
	 */
	private static $instance = null;

	/**
	 * Determine whether the class should be registered.
	 *
	 * Classes using this trait must implement this method.
	 *
	 * @return bool True when the class should be initialized and registered.
	 */
	abstract protected static function can_register();

	/**
	 * Get the singleton instance.
	 *
	 * @return static The class instance.
	 */
	public static function instance() {
		if ( null === static::$instance ) {
			static::$instance = new static();
		}

		return static::$instance;
	}

	/**
	 * Register and initialize the class.
	 *
	 * The class is instantiated only when can_register() returns true.
	 * If the class contains a register_hooks() method, that method is called
	 * after the singleton instance has been created.
	 *
	 * @return static|null The class instance, or null when registration is
	 *                     disabled.
	 */
	public static function register() {
		if ( ! static::can_register() ) {
			return null;
		}

		$instance = static::instance();

		if ( method_exists( $instance, 'register_hooks' ) ) {
			$instance->register_hooks();
		}

		return $instance;
	}

	/**
	 * Prevent cloning the singleton instance.
	 *
	 * @return void
	 */
	private function __clone() {}

	/**
	 * Prevent unserializing the singleton instance.
	 *
	 * When WordPress debugging is enabled, an exception is thrown with the
	 * singleton class name and source file. In production, the same information
	 * is written to the PHP error log without interrupting the request.
	 *
	 * @throws RuntimeException When WP_DEBUG is enabled.
	 *
	 * @return void
	 */
	public function __wakeup() {
		$class_name = static::class;
		$file_path  = 'Unknown';

		try {
			$reflection = new \ReflectionClass( $class_name );
			$file_path  = $reflection->getFileName() ?: 'Unknown';
		} catch ( \ReflectionException $exception ) {
			// No action required; the fallback path is already set.
		}

		$message = sprintf(
			'Unserialization is not permitted for singleton class "%1$s". Source file: %2$s',
			$class_name,
			$file_path
		);

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			throw new \RuntimeException( $message );
		}

		error_log( $message );
	}
}