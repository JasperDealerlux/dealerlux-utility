<?php
/**
 * Class Initializer
 *
 * Handles initialization of the plugin classes.
 */

namespace DealerluxUtils;

use DealerluxUtils\Traits\Singleton as Singleton_Trait;

use DealerluxUtils\Modules\Client_Switcher\Client_Switcher;
use DealerluxUtils\Registries\Options_Registry;
use DealerluxUtils\Registries\Posts_Registry;
use DealerluxUtils\Shortcodes\Dump_Client_Forms_Shortcode;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Initialize the plugin classes.
 */
class Initializer {

	/**
	 * Use the singleton loader.
	 *
	 * This prevents the class from being instantiated more than once
	 * during a single WordPress request.
	 */
	use Singleton_Trait;

	/**
	 * Constructor.
	 */
	private function __construct() {}

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
	 * @return void
	 */
	public function register_hooks() {
		add_action(
			'muplugins_loaded',
			array( $this, 'initialize_classes' )
		);
	}

	/**
	 * Initialize Dealerlux Utility classes by classification.
	 *
	 * @return void
	 */
	public function initialize_classes() {
		$this->initialize_registries();
		$this->initialize_modules();
		$this->initialize_pages();
		$this->initialize_shortcodes();
	}

	/**
	 * Initialize registry classes.
	 *
	 * Options Registry must be initialized before modules that read or write
	 * registered options.
	 *
	 * @return void
	 */
	private function initialize_registries() {
		Options_Registry::register();
		Posts_Registry::register();
	}

	/**
	 * Initialize feature modules.
	 *
	 * Client Switcher runs during muplugins_loaded so the managed normal
	 * plugins can be synchronized before WordPress loads active plugins.
	 *
	 * @return void
	 */
	private function initialize_modules() {
		Client_Switcher::register();
	}

	/**
	 * Initialize classes related to pages.
	 *
	 * @return void
	 */
	private function initialize_pages() {
		// Page-related classes are registered here.
	}

	/**
	 * Initialize classes related to shortcodes.
	 *
	 * @return void
	 */
	private function initialize_shortcodes() {
		Dump_Client_Forms_Shortcode::register();
	}
}