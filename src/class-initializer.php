<?php
/**
 * Class Initializer
 *
 * Handles initialization of the plugin classes.
 */

namespace DealerluxUtils;

use DealerluxUtils\Traits\Singleton as DealerluxUtils_Singleton;

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
	use DealerluxUtils_Singleton;

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
	 * Initialize the classes from method classification class initializer.
	 *
	 * @return void
	 */
	public function initialize_classes() {
		$this->initialize_registries();
		$this->initialize_pages();
		$this->initialize_shortcodes();
	}

    /**
     * Initialize the registry classes.
     *
     * @return void
     */
    private function initialize_registries() {
        Options_Registry::register();
        Posts_Registry::register();

		$pages = Posts_Registry::instance()->get_posts( 'page' );

		dl_dump_js($pages);
    }

    /**
     * Initialize the classes related to pages.
     *
     * @return void
     */
    private function initialize_pages() {
        // Page_Title_Handler::register();
    }

    /**
     * Initialize the classes related to shortcodes.
     *
     * @return void
     */
    private function initialize_shortcodes() {
        Dump_Client_Forms_Shortcode::register();
    }
}