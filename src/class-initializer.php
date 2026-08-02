<?php
/**
 * Class Initializer
 *
 * Handles initialization of the plugin classes.
 */

namespace DealerluxUtils;

use DealerluxUtils\Traits\Singleton as Singleton_Trait;
use DealerluxUtils\Traits\Clients_Config_Loader as Clients_Config_Loader_Trait;

use DealerluxUtils\Modules\Client_Switcher\Client_Switcher;
use DealerluxUtils\Registries\Options_Registry;
use DealerluxUtils\Registries\Posts_Registry;
use DealerluxUtils\Assets\Posts_Asset_Loader;
use DealerluxUtils\Shortcodes\Forms\Client_Forms_Shortcode;
use DealerluxUtils\Shortcodes\Forms\Client_Form_Selector_Shortcode;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Initialize the plugin classes.
 */
class Initializer {

	/**
	 * Use shared Dealerlux Utility traits.
	 */
	use Singleton_Trait;
	use Clients_Config_Loader_Trait;

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
		
		$this->initialize_early_components();

		add_action(
			'muplugins_loaded',
			array( $this, 'initialize_components' )
		);
	}

	/**
	 * Initialize components required before ordinary plugins load.
	 *
	 * @return void
	 */
	private function initialize_early_components() {
		$this->load_clients_configuration();
	}

	/**
	 * Initialize Dealerlux Utility classes by classification.
	 *
	 * @return void
	 */
	public function initialize_components() {
		$this->initialize_assets();
		$this->initialize_registries();
		$this->initialize_modules();
		$this->initialize_shortcodes();
		$this->initialize_pages();
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
	 * Initialize asset-loading classes.
	 *
	 * This runs after the post registry so asset loaders can inspect the
	 * currently registered virtual post configuration.
	 *
	 * @return void
	 */
	private function initialize_assets() {
		Posts_Asset_Loader::register();
	}

	/**
	 * Initialize classes related to shortcodes.
	 *
	 * @return void
	 */
	private function initialize_shortcodes() {
		Client_Forms_Shortcode::register();
		Client_Form_Selector_Shortcode::register();
	}

	/**
	 * Initialize classes related to pages.
	 *
	 * @return void
	 */
	private function initialize_pages() {
		// Page-related classes are registered here.
	}
}